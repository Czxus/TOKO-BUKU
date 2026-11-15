<?php

namespace App\Modules\Ordering\Services;

use App\Models\Book;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use App\Modules\Ordering\Data\CartLine;
use App\Modules\Ordering\Data\CartView;
use App\Modules\Ordering\Exceptions\CartException;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Keranjang pembeli yang tersimpan di Redis (cache store dari config('cart.store')),
 * di-key per PEMBELI (cart:user:{id}). Prinsip keamanan inti:
 *
 * - Redis hanya menyimpan IDENTITAS + jumlah (book_id, quantity) dalam JSON biasa —
 *   tanpa model Eloquent/serialisasi class. Harga TIDAK PERNAH dipercaya dari keranjang;
 *   selalu dibaca ulang dari basis data di view().
 * - Buku wajib aktif, belum dihapus (soft delete), dan milik toko berstatus active.
 *   TenantScope dilepas karena pembeli lintas toko; diganti filter eksplisit.
 * - Setiap mutasi (read-modify-write) dilindungi Cache::lock()->block() agar dua tab
 *   tidak saling menimpa. Lock Redis TIDAK menggantikan row lock database yang tetap
 *   dibutuhkan saat stok dikurangi di checkout (Pertemuan 9).
 * - Hanya key milik pembeli yang disentuh (put/forget satu key). Tidak ada flush global.
 * - Payload rusak / TTL habis diperlakukan sebagai keranjang kosong, bukan exception fatal.
 *
 * @phpstan-type CartItem array{book_id: int, tenant_id: int, quantity: int, price_at_add: int, title_at_add: string, tenant_name_at_add: string}
 */
final class CartService
{
    private const KEY_PREFIX = 'cart:user:';

    private const PAYLOAD_VERSION = 1;

    /**
     * Menambah buku ke keranjang (digabung bila sudah ada). Mengembalikan buku yang
     * ditambahkan agar UI dapat menampilkan judulnya.
     *
     * @throws CartException
     */
    public function add(User $user, int $bookId, int $quantity = 1): Book
    {
        if ($quantity < 1) {
            throw CartException::invalidQuantity();
        }

        $book = $this->orderableBook($bookId);
        if ($book === null) {
            throw CartException::bookNotOrderable();
        }

        $max = $this->maxQuantity($book);
        if ($max < 1) {
            throw CartException::soldOut(); // UC-03 alur 2a
        }

        $this->mutate($user, function (array $items) use ($book, $quantity, $max): array {
            $existing = isset($items[$book->id]) ? $items[$book->id]['quantity'] : 0;

            if ($existing === 0 && count($items) >= (int) config('cart.max_lines')) {
                throw CartException::tooManyLines();
            }

            if ($existing + $quantity > $max) {
                throw CartException::quantityLimit();
            }

            $items[$book->id] = [
                'book_id' => $book->id,
                'tenant_id' => $book->tenant_id,
                'quantity' => $existing + $quantity,
                'price_at_add' => (int) $book->harga,
                'title_at_add' => (string) $book->judul,
                'tenant_name_at_add' => (string) ($book->tenant->nama_toko ?? ''),
            ];

            return $items;
        });

        return $book;
    }

    /**
     * Mengubah jumlah relatif (+1 / -1) secara atomik di dalam lock, sehingga dua tab
     * yang menekan tombol bersamaan tidak menghilangkan satu klik pun.
     */
    public function changeQuantity(User $user, int $bookId, int $delta): void
    {
        $this->mutate($user, function (array $items) use ($bookId, $delta): array {
            if (! isset($items[$bookId])) {
                return $items;
            }

            return $this->applyQuantity($items, $bookId, $items[$bookId]['quantity'] + $delta);
        });
    }

    /** Menyetel jumlah sebuah baris. 0 (atau kurang) menghapus baris. */
    public function setQuantity(User $user, int $bookId, int $quantity): void
    {
        $this->mutate($user, function (array $items) use ($bookId, $quantity): array {
            return isset($items[$bookId]) ? $this->applyQuantity($items, $bookId, $quantity) : $items;
        });
    }

    public function remove(User $user, int $bookId): void
    {
        $this->mutate($user, function (array $items) use ($bookId): array {
            unset($items[$bookId]);

            return $items;
        });
    }

    /** Mengosongkan keranjang (mis. setelah checkout sukses & order commit). */
    public function clear(User $user): void
    {
        $this->guard(fn () => $this->store()->forget($this->key($user)));
    }

    /**
     * Pembeli menyetujui harga terbaru: price_at_add diselaraskan dengan harga database
     * sehingga peringatan "harga diperbarui" hilang (alur UC revalidasi, langkah 5.3).
     */
    public function acceptPriceChanges(User $user): void
    {
        $this->mutate($user, function (array $items): array {
            if ($items === []) {
                return $items;
            }

            $books = $this->loadBooks(array_keys($items));

            foreach ($items as $id => $item) {
                $book = $books->get($id);

                if ($book !== null && $this->isOrderable($book)) {
                    $items[$id]['price_at_add'] = (int) $book->harga;
                    $items[$id]['title_at_add'] = (string) $book->judul;
                }
            }

            return $items;
        });
    }

    /**
     * Jumlah total eksemplar di keranjang untuk badge. Hanya membaca Redis (tanpa query
     * database) dan TIDAK memperpanjang TTL.
     */
    public function itemCount(User $user): int
    {
        return array_sum(array_column($this->guard(fn () => $this->load($user)), 'quantity'));
    }

    /**
     * Menyusun tampilan keranjang TERREVALIDASI: harga, stok, dan status buku/toko dibaca
     * ulang dari basis data dalam satu batch query; baris yang tak lagi dapat dipesan
     * ditandai dan dikeluarkan dari subtotal.
     */
    public function view(User $user): CartView
    {
        $items = $this->guard(fn () => $this->load($user));

        if ($items === []) {
            return new CartView([], 0, 0, false);
        }

        $books = $this->loadBooks(array_keys($items));

        $lines = [];
        $subtotal = 0;
        $totalQuantity = 0;
        $blocking = false;

        foreach ($items as $item) {
            $line = $this->revalidateLine($item, $books->get($item['book_id']));
            $lines[] = $line;
            $totalQuantity += $line->quantity;

            if ($line->available) {
                $subtotal += $line->lineTotal;
            } else {
                $blocking = true;
            }
        }

        return new CartView($lines, $subtotal, $totalQuantity, $blocking, $this->serviceFee($subtotal));
    }

    /**
     * @param  CartItem  $item  data dari Redis (setiap nilai sudah di-cast eksplisit oleh load())
     */
    private function revalidateLine(array $item, ?Book $book): CartLine
    {
        $orderable = $book !== null && $this->isOrderable($book);
        $issues = [];
        $available = true;

        if (! $orderable) {
            $issues[] = 'book_unavailable';
            $available = false;
        }

        $unitPrice = $book !== null ? (int) $book->harga : 0;
        $maxQuantity = $book !== null ? $this->maxQuantity($book) : 0;

        if ($available && $item['quantity'] > $maxQuantity) {
            $issues[] = 'insufficient_stock';
            $available = false;
        }

        if ($orderable && $item['price_at_add'] !== $unitPrice) {
            $issues[] = 'price_changed';
        }

        return new CartLine(
            bookId: $item['book_id'],
            tenantId: $book !== null ? (int) $book->tenant_id : $item['tenant_id'],
            tenantName: (string) ($book?->tenant?->nama_toko ?? $item['tenant_name_at_add']),
            title: $book !== null ? (string) $book->judul : $item['title_at_add'],
            author: $book?->penulis,
            format: $book !== null ? (string) $book->format_buku : 'fisik',
            coverPath: $book?->foto_sampul,
            quantity: $item['quantity'],
            unitPrice: $unitPrice,
            priceAtAdd: $item['price_at_add'],
            maxQuantity: $maxQuantity,
            lineTotal: $available ? $unitPrice * $item['quantity'] : 0,
            available: $available,
            issues: $issues,
        );
    }

    /**
     * Menerapkan jumlah baru pada satu baris. Mengurangi jumlah SELALU diperbolehkan
     * (agar baris dengan stok menipis dapat diturunkan bertahap); menaikkan jumlah
     * divalidasi ulang terhadap stok dan status buku saat ini.
     *
     * @param  array<int, CartItem>  $items
     * @return array<int, CartItem>
     */
    private function applyQuantity(array $items, int $bookId, int $quantity): array
    {
        if ($quantity < 1) {
            unset($items[$bookId]);

            return $items;
        }

        if ($quantity > $items[$bookId]['quantity']) {
            $book = $this->orderableBook($bookId);

            if ($book === null) {
                throw CartException::bookNotOrderable();
            }

            if ($quantity > $this->maxQuantity($book)) {
                throw CartException::quantityLimit();
            }
        }

        $items[$bookId]['quantity'] = $quantity;

        return $items;
    }

    /**
     * Batas jumlah yang boleh dibeli untuk satu buku, mengikuti aturan katalog:
     * fisik = stok; e-book = 1 (tanpa stok); fisik+e-book = minimal 1 (e-book tetap bisa).
     * Selalu dibatasi config('cart.max_qty_per_line').
     */
    private function maxQuantity(Book $book): int
    {
        $available = match ($book->format_buku) {
            'ebook' => 1,
            'keduanya' => max(1, (int) $book->stok),
            default => (int) $book->stok,
        };

        return min($available, (int) config('cart.max_qty_per_line'));
    }

    /** Buku sah untuk dipesan: aktif dan milik toko berstatus active. (Soft delete sudah tersaring.) */
    private function isOrderable(Book $book): bool
    {
        return $book->is_active && $book->tenant !== null && $book->tenant->isActive();
    }

    private function orderableBook(int $bookId): ?Book
    {
        return Book::query()
            ->withoutGlobalScope(TenantScope::class)
            ->with('tenant:id,nama_toko,status')
            ->whereKey($bookId)
            ->where('is_active', true)
            ->whereHas('tenant', fn ($tenant) => $tenant->where('status', 'active'))
            ->first();
    }

    /**
     * @param  list<int>  $ids
     * @return \Illuminate\Database\Eloquent\Collection<int, Book>
     */
    private function loadBooks(array $ids): \Illuminate\Database\Eloquent\Collection
    {
        return Book::query()
            ->withoutGlobalScope(TenantScope::class)
            ->with('tenant:id,nama_toko,status')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }

    /** Biaya layanan platform = round(subtotal × tarif); pembulatan hanya di sini. */
    private function serviceFee(int $subtotal): int
    {
        $rate = min(1.0, max(0.0, (float) config('cart.service_fee_rate')));

        return $rate > 0 ? (int) round($subtotal * $rate) : 0;
    }

    /**
     * Read-modify-write atomik: seluruh baca, perubahan, validasi, dan tulis ulang
     * dilakukan di dalam lock dengan lease dan batas tunggu pendek.
     *
     * @param  Closure(array<int, CartItem>): array<int, CartItem>  $callback
     */
    private function mutate(User $user, Closure $callback): void
    {
        $this->guard(function () use ($user, $callback): void {
            $this->store()
                ->lock('lock:'.$this->key($user), (int) config('cart.lock_seconds'))
                ->block((int) config('cart.lock_wait'), function () use ($user, $callback): void {
                    $this->persist($user, $callback($this->load($user)));
                });
        });
    }

    /**
     * Mengubah kegagalan infrastruktur menjadi CartException yang dapat dicoba ulang.
     * Tidak mencatat isi keranjang (hanya jenis galat) agar tidak membocorkan data.
     */
    private function guard(Closure $callback): mixed
    {
        try {
            return $callback();
        } catch (LockTimeoutException) {
            throw CartException::busy();
        } catch (\RedisException|\Predis\PredisException $e) {
            Log::warning('Cart store tidak dapat dijangkau.', ['exception' => $e::class]);

            throw CartException::unavailable();
        }
    }

    /**
     * Membaca keranjang dari store. Payload yang hilang (TTL habis), bukan JSON, atau
     * strukturnya tidak sesuai diperlakukan sebagai keranjang kosong; entri rusak dibuang.
     *
     * @return array<int, CartItem>
     */
    private function load(User $user): array
    {
        $raw = $this->store()->get($this->key($user));

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded) || ! isset($decoded['items']) || ! is_array($decoded['items'])) {
            return [];
        }

        $items = [];

        foreach ($decoded['items'] as $item) {
            if (! is_array($item) || ! isset($item['book_id'], $item['quantity'])) {
                continue;
            }

            $bookId = (int) $item['book_id'];
            $quantity = (int) $item['quantity'];

            if ($bookId < 1 || $quantity < 1) {
                continue;
            }

            $items[$bookId] = [
                'book_id' => $bookId,
                'tenant_id' => (int) ($item['tenant_id'] ?? 0),
                'quantity' => $quantity,
                'price_at_add' => (int) ($item['price_at_add'] ?? 0),
                'title_at_add' => (string) ($item['title_at_add'] ?? ''),
                'tenant_name_at_add' => (string) ($item['tenant_name_at_add'] ?? ''),
            ];
        }

        return $items;
    }

    /**
     * Menulis keranjang sebagai JSON dengan TTL baru. Keranjang kosong menghapus key.
     *
     * @param  array<int, CartItem>  $items
     */
    private function persist(User $user, array $items): void
    {
        if ($items === []) {
            $this->store()->forget($this->key($user));

            return;
        }

        $payload = json_encode(
            ['v' => self::PAYLOAD_VERSION, 'items' => array_values($items)],
            JSON_THROW_ON_ERROR,
        );

        $this->store()->put($this->key($user), $payload, now()->addSeconds((int) config('cart.ttl_seconds')));
    }

    private function store(): Repository
    {
        return Cache::store(config('cart.store'));
    }

    private function key(User $user): string
    {
        return self::KEY_PREFIX.$user->getKey();
    }
}
