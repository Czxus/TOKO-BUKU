<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Ordering\Exceptions\CartException;
use App\Modules\Ordering\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Modul 8 — keranjang multi-tenant: operasi add/update/remove/clear, revalidasi
 * harga/stok dari database, isolasi per pembeli, TTL, payload dimanipulasi, dan lock.
 * Test memakai cache store 'array' (CART_STORE di phpunit.xml) sehingga tidak butuh Redis;
 * jalankan dengan CART_STORE=redis untuk mengujinya pada Redis sungguhan.
 */
class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private function cart(): CartService
    {
        return app(CartService::class);
    }

    private function pembeli(): User
    {
        return User::factory()->create(['role' => 'pembeli']);
    }

    private function book(?Tenant $tenant = null, array $attrs = []): Book
    {
        $tenant ??= Tenant::factory()->create();

        return Book::factory()->create(array_merge([
            'tenant_id' => $tenant->id,
            'harga' => 50000,
            'stok' => 10,
        ], $attrs));
    }

    private function key(User $user): string
    {
        return 'cart:user:'.$user->id;
    }

    private function assertAddRejected(User $user, int $bookId, string $message, int $quantity = 1): void
    {
        try {
            $this->cart()->add($user, $bookId, $quantity);
            $this->fail('Penambahan seharusnya ditolak: '.$message);
        } catch (CartException $e) {
            $this->assertSame($message, $e->getMessage());
        }
    }

    public function test_add_stores_json_payload_and_merges_same_book(): void
    {
        $user = $this->pembeli();
        $book = $this->book();

        $this->cart()->add($user, $book->id, 2);
        $this->cart()->add($user, $book->id, 3);

        $raw = Cache::store(config('cart.store'))->get($this->key($user));
        $this->assertIsString($raw, 'payload harus JSON string, bukan objek/serialisasi class');
        $decoded = json_decode($raw, true);
        $this->assertSame(1, $decoded['v']);
        $this->assertCount(1, $decoded['items']);

        $view = $this->cart()->view($user);
        $this->assertCount(1, $view->lines);
        $this->assertSame(5, $view->lines[0]->quantity);
        $this->assertSame(250000, $view->subtotal);
        $this->assertSame(5, $this->cart()->itemCount($user));
    }

    public function test_add_rejects_books_that_cannot_be_ordered(): void
    {
        $user = $this->pembeli();

        $nonaktif = $this->book(null, ['is_active' => false]);
        $dihapus = $this->book();
        $dihapus->delete();
        $tokoSuspended = $this->book(Tenant::factory()->suspended()->create());

        $this->assertAddRejected($user, $nonaktif->id, 'Buku ini sudah tidak tersedia.');
        $this->assertAddRejected($user, $dihapus->id, 'Buku ini sudah tidak tersedia.');
        $this->assertAddRejected($user, $tokoSuspended->id, 'Buku ini sudah tidak tersedia.');
        $this->assertAddRejected($user, 999999, 'Buku ini sudah tidak tersedia.');

        $habis = $this->book(null, ['stok' => 0]);
        $this->assertAddRejected($user, $habis->id, 'Stok buku ini habis.');

        $this->assertSame(0, $this->cart()->itemCount($user), 'penolakan tidak boleh meninggalkan data');
    }

    public function test_quantity_is_limited_by_stock_format_and_cart_limits(): void
    {
        $user = $this->pembeli();

        // Stok 3: 2 + 2 melebihi stok, jumlah tetap 2.
        $fisik = $this->book(null, ['stok' => 3]);
        $this->cart()->add($user, $fisik->id, 2);
        $this->assertAddRejected($user, $fisik->id, 'Jumlah di keranjang sudah mencapai batas yang tersedia.', 2);
        $this->assertSame(2, $this->cart()->view($user)->lines[0]->quantity);

        // E-book tidak memakai stok tetapi maksimal 1 per baris.
        $ebook = $this->book(null, ['format_buku' => 'ebook', 'stok' => 0]);
        $this->cart()->add($user, $ebook->id);
        $this->assertAddRejected($user, $ebook->id, 'Jumlah di keranjang sudah mencapai batas yang tersedia.');

        // Batas jumlah per baris dan jumlah baris dari config.
        config(['cart.max_qty_per_line' => 3, 'cart.max_lines' => 2]);
        $banyak = $this->book(null, ['stok' => 50]);
        $this->assertAddRejected($user, $banyak->id, 'Keranjang penuh. Selesaikan pesanan ini terlebih dahulu.');
    }

    public function test_view_recomputes_price_from_database_and_user_can_accept_it(): void
    {
        $user = $this->pembeli();
        $book = $this->book(null, ['harga' => 40000]);
        $this->cart()->add($user, $book->id, 2);

        $book->update(['harga' => 55000]); // naik SETELAH masuk keranjang

        $view = $this->cart()->view($user);
        $line = $view->lines[0];
        $this->assertTrue($line->available);
        $this->assertTrue($line->priceChanged());
        $this->assertTrue($view->hasPriceChanges());
        $this->assertSame(55000, $line->unitPrice, 'harga otoritatif harus dari DB');
        $this->assertSame(40000, $line->priceAtAdd);
        $this->assertSame(110000, $view->subtotal);

        $this->cart()->acceptPriceChanges($user);

        $after = $this->cart()->view($user);
        $this->assertFalse($after->lines[0]->priceChanged());
        $this->assertFalse($after->hasPriceChanges());
        $this->assertSame(110000, $after->subtotal);
    }

    public function test_view_flags_insufficient_stock_and_quantity_can_always_be_reduced(): void
    {
        $user = $this->pembeli();
        $book = $this->book(null, ['stok' => 10, 'harga' => 20000]);
        $this->cart()->add($user, $book->id, 3);

        $book->update(['stok' => 1]);

        $view = $this->cart()->view($user);
        $this->assertFalse($view->lines[0]->available);
        $this->assertTrue($view->lines[0]->hasIssue('insufficient_stock'));
        $this->assertSame(1, $view->lines[0]->maxQuantity);
        $this->assertSame(0, $view->subtotal, 'baris bermasalah tidak boleh ditagih');
        $this->assertTrue($view->hasBlockingIssues);
        $this->assertFalse($view->isOrderable());

        // Menaikkan jumlah ditolak; menurunkan bertahap selalu boleh.
        try {
            $this->cart()->changeQuantity($user, $book->id, 1);
            $this->fail('menaikkan jumlah melebihi stok seharusnya ditolak');
        } catch (CartException) {
            // diharapkan
        }

        $this->cart()->changeQuantity($user, $book->id, -1); // 3 -> 2 (masih bermasalah)
        $this->assertFalse($this->cart()->view($user)->isOrderable());

        $this->cart()->changeQuantity($user, $book->id, -1); // 2 -> 1 (sesuai stok)
        $ok = $this->cart()->view($user);
        $this->assertTrue($ok->isOrderable());
        $this->assertSame(20000, $ok->subtotal);
    }

    public function test_view_flags_book_unavailable_when_tenant_suspended_book_hidden_or_deleted(): void
    {
        $skenario = [
            'toko disuspend' => fn (Book $b) => $b->tenant->update(['status' => 'suspended']),
            'buku disembunyikan' => fn (Book $b) => $b->update(['is_active' => false]),
            'buku dihapus (soft delete)' => fn (Book $b) => $b->delete(),
        ];

        foreach ($skenario as $label => $ubah) {
            $user = $this->pembeli();
            $book = $this->book();
            $this->cart()->add($user, $book->id);

            $ubah($book);

            $view = $this->cart()->view($user);
            $line = $view->lines[0];
            $this->assertFalse($line->available, $label);
            $this->assertTrue($line->hasIssue('book_unavailable'), $label);
            $this->assertSame($book->judul, $line->title, $label.': judul jatuh kembali ke snapshot');
            $this->assertSame(0, $view->subtotal, $label);
            $this->assertFalse($view->isOrderable(), $label);
        }
    }

    public function test_set_quantity_zero_removes_line_and_clear_empties_cart(): void
    {
        $user = $this->pembeli();
        $book = $this->book();
        $store = Cache::store(config('cart.store'));

        $this->cart()->add($user, $book->id, 2);
        $this->cart()->setQuantity($user, $book->id, 4);
        $this->assertSame(4, $this->cart()->view($user)->lines[0]->quantity);

        $this->cart()->setQuantity($user, $book->id, 0);
        $this->assertTrue($this->cart()->view($user)->isEmpty());
        $this->assertFalse($store->has($this->key($user)), 'keranjang kosong harus menghapus key');

        $this->cart()->add($user, $book->id);
        $this->cart()->remove($user, $book->id);
        $this->assertTrue($this->cart()->view($user)->isEmpty());

        $this->cart()->add($user, $book->id);
        $this->cart()->clear($user);
        $this->assertTrue($this->cart()->view($user)->isEmpty());
    }

    public function test_cart_is_isolated_per_user(): void
    {
        $book = $this->book();
        $a = $this->pembeli();
        $b = $this->pembeli();

        $this->cart()->add($a, $book->id, 2);

        $this->assertCount(1, $this->cart()->view($a)->lines);
        $this->assertTrue($this->cart()->view($b)->isEmpty(), 'keranjang pembeli lain tidak boleh bocor');
        $this->assertSame(0, $this->cart()->itemCount($b));
    }

    public function test_multi_tenant_groups_sum_to_platform_total_with_service_fee(): void
    {
        config(['cart.service_fee_rate' => 0.025]);

        $user = $this->pembeli();
        $alfa = Tenant::factory()->create(['nama_toko' => 'Toko Alfa']);
        $beta = Tenant::factory()->create(['nama_toko' => 'Toko Beta']);

        $a1 = $this->book($alfa, ['harga' => 10001]);
        $a2 = $this->book($alfa, ['harga' => 20000]);
        $b1 = $this->book($beta, ['harga' => 30000]);

        $this->cart()->add($user, $a1->id, 1);
        $this->cart()->add($user, $b1->id, 1);
        $this->cart()->add($user, $a2->id, 2);

        $view = $this->cart()->view($user);
        $groups = $view->tenantGroups();

        $this->assertCount(2, $groups);
        $this->assertSame('Toko Alfa', $groups[0]['tenant_name']);
        $this->assertSame(50001, $groups[0]['subtotal']);
        $this->assertSame(30000, $groups[1]['subtotal']);
        $this->assertSame($view->subtotal, array_sum(array_column($groups, 'subtotal')));
        $this->assertSame(80001, $view->subtotal);

        $this->assertSame(2000, $view->serviceFeeAmount); // round(80001 × 0.025) = 2000
        $this->assertSame(82001, $view->grandTotal());
    }

    public function test_tampered_payload_never_sets_price_or_tenant(): void
    {
        $user = $this->pembeli();
        $book = $this->book(null, ['harga' => 90000]);

        Cache::store(config('cart.store'))->put($this->key($user), json_encode([
            'v' => 1,
            'items' => [[
                'book_id' => $book->id,
                'tenant_id' => 999,
                'quantity' => 2,
                'price_at_add' => 1,
                'unit_price' => 1,
                'line_total' => 2,
                'title_at_add' => 'Judul Palsu',
                'tenant_name_at_add' => 'Toko Palsu',
            ]],
        ]), 3600);

        $line = $this->cart()->view($user)->lines[0];

        $this->assertSame(90000, $line->unitPrice);
        $this->assertSame(180000, $line->lineTotal);
        $this->assertSame($book->tenant_id, $line->tenantId);
        $this->assertSame($book->judul, $line->title);
        $this->assertTrue($line->priceChanged(), 'selisih dengan snapshot ditandai, tetapi tidak dipakai');
    }

    public function test_corrupt_payload_is_treated_as_empty_cart(): void
    {
        $user = $this->pembeli();
        $store = Cache::store(config('cart.store'));

        foreach ([
            'bukan-json',
            json_encode(['v' => 1, 'items' => 'x']),
            json_encode(['v' => 1, 'items' => ['x', ['book_id' => -1, 'quantity' => 2], ['book_id' => 5, 'quantity' => 0]]]),
            ['items' => []], // bukan string JSON
        ] as $payload) {
            $store->put($this->key($user), $payload, 3600);

            $this->assertTrue($this->cart()->view($user)->isEmpty());
            $this->assertSame(0, $this->cart()->itemCount($user));
        }
    }

    public function test_expired_cart_is_empty_not_an_exception(): void
    {
        $user = $this->pembeli();
        $book = $this->book();
        $this->cart()->add($user, $book->id, 2);
        $this->assertSame(2, $this->cart()->itemCount($user));

        $this->travel((int) config('cart.ttl_seconds') + 1)->seconds();

        $this->assertTrue($this->cart()->view($user)->isEmpty());
        $this->assertSame(0, $this->cart()->itemCount($user));
    }

    public function test_mutation_fails_in_controlled_way_when_lock_is_held_by_another_tab(): void
    {
        config(['cart.lock_wait' => 0]);

        $user = $this->pembeli();
        $book = $this->book();

        $lock = Cache::store(config('cart.store'))->lock('lock:'.$this->key($user), 10);
        $this->assertTrue($lock->get());

        try {
            $this->cart()->add($user, $book->id);
            $this->fail('mutasi seharusnya gagal terkontrol saat lock dipegang');
        } catch (CartException $e) {
            $this->assertSame('Keranjang sedang diperbarui dari tab lain. Silakan coba lagi.', $e->getMessage());
        } finally {
            $lock->release();
        }

        $this->cart()->add($user, $book->id); // setelah lock dilepas, berhasil
        $this->assertSame(1, $this->cart()->itemCount($user));
    }
}
