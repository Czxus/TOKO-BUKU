<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\Tenant;
use App\Services\AuditLogger;
use App\Services\BookStockService;
use App\Services\CoverStore;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * UC-13 Kelola Buku & Stok + UC-14 Tandai Habis (portal penjual).
 * tenantId dikunci (#[Locked]) dan keanggotaan seller diverifikasi ulang di SETIAP request
 * lewat booted(), karena request Livewire tidak melewati middleware route.
 */
new #[Layout('components.dashboard-layout'), Title('Buku & Stok')] class extends Component
{
    use WithFileUploads, WithPagination;

    #[Locked]
    public int $tenantId = 0;

    public ?int $editingId = null;
    public string $judul = '';
    public string $penulis = '';
    public string $penerbit = '';
    public string $isbn = '';
    public int|string|null $tahunTerbit = null;
    public string $formatBuku = 'fisik';
    public int|string|null $harga = 0;
    public int|string|null $stok = 0;
    public int|string|null $beratGram = 300;
    public string $deskripsi = '';
    public int|string|null $categoryId = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $foto = null;

    public string $cari = '';
    public int|string|null $filterKategori = null;
    public ?string $pesan = null;
    public string $pesanTipe = 'ok';

    public function mount(Tenant $tenant): void
    {
        $this->tenantId = $tenant->id;
        $this->guard();
    }

    public function booted(): void
    {
        if ($this->tenantId !== 0) {
            $this->guard();
        }
    }

    private function guard(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless(
            $user !== null && $user->isSellerAdmin() && (int) $user->tenant_id === $this->tenantId,
            403
        );

        $tenant = Tenant::query()->findOrFail($this->tenantId);
        abort_unless($tenant->isActive(), 403);

        app(TenantContext::class)->set($tenant);
    }

    public function updatingCari(): void
    {
        $this->resetPage();
    }

    public function updatingFilterKategori(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'penulis' => ['required', 'string', 'max:150'],
            'penerbit' => ['required', 'string', 'max:150'],
            'isbn' => ['required', 'regex:/^[0-9][0-9\-]{9,18}$/',
                Rule::unique('books', 'isbn')->where('tenant_id', $this->tenantId)->ignore($this->editingId)],
            'tahunTerbit' => ['nullable', 'integer', 'between:1900,'.(now()->year + 1)],
            'formatBuku' => ['required', Rule::in(['fisik', 'ebook', 'keduanya'])],
            'harga' => ['required', 'integer', 'min:1'],
            'stok' => ['required', 'integer', 'min:0', 'max:1000000'],
            'beratGram' => ['required', 'integer', 'min:0', 'max:50000'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'judul' => 'judul', 'penulis' => 'penulis', 'penerbit' => 'penerbit', 'isbn' => 'ISBN',
            'tahunTerbit' => 'tahun terbit', 'formatBuku' => 'format', 'harga' => 'harga',
            'stok' => 'stok', 'beratGram' => 'berat', 'deskripsi' => 'deskripsi',
            'categoryId' => 'kategori', 'foto' => 'foto sampul',
        ];
    }

    public function createBook(): void
    {
        $data = $this->validate();

        $ebook = $data['formatBuku'] === 'ebook';
        $stok = $ebook ? 0 : (int) $data['stok'];
        $cover = $this->foto ? app(CoverStore::class)->store($this->foto, $this->tenantId) : null;

        try {
            DB::transaction(function () use ($data, $ebook, $stok, $cover): void {
                $book = new Book;
                $book->fill([
                    'category_id' => (int) $data['categoryId'],
                    'judul' => $data['judul'],
                    'penulis' => $data['penulis'],
                    'penerbit' => $data['penerbit'],
                    'isbn' => $data['isbn'],
                    'tahun_terbit' => $data['tahunTerbit'] !== null && $data['tahunTerbit'] !== '' ? (int) $data['tahunTerbit'] : null,
                    'format_buku' => $data['formatBuku'],
                    'harga' => (int) $data['harga'],
                    'stok' => 0,
                    'berat_gram' => $ebook ? 0 : (int) $data['beratGram'],
                    'deskripsi' => $data['deskripsi'] !== '' ? $data['deskripsi'] : null,
                    'is_active' => true,
                ]);
                $book->foto_sampul = $cover;
                $book->save(); // tenant_id otomatis dari TenantContext

                if ($stok > 0) {
                    app(BookStockService::class)->adjust($book, $stok, 'initial', (string) Str::uuid(), 'stok awal');
                }

                app(AuditLogger::class)->record('book', $book->id, 'created', null,
                    $book->only(['judul', 'isbn', 'harga', 'format_buku']), $this->tenantId);
            });
        } catch (\Throwable $e) {
            app(CoverStore::class)->delete($cover);
            throw $e;
        }

        $this->resetForm();
        $this->notif('Buku ditambahkan.');
    }

    public function editBook(int $bookId): void
    {
        $book = Book::query()->findOrFail($bookId); // ter-scope tenant aktif
        Gate::authorize('update', $book);

        $this->editingId = $book->id;
        $this->judul = $book->judul;
        $this->penulis = $book->penulis;
        $this->penerbit = $book->penerbit;
        $this->isbn = $book->isbn;
        $this->tahunTerbit = $book->tahun_terbit;
        $this->formatBuku = $book->format_buku;
        $this->harga = $book->harga;
        $this->stok = $book->stok;
        $this->beratGram = $book->berat_gram;
        $this->deskripsi = (string) $book->deskripsi;
        $this->categoryId = $book->category_id;
        $this->foto = null;
        $this->resetValidation();
    }

    public function updateBook(): void
    {
        $data = $this->validate();

        $book = Book::query()->findOrFail((int) $this->editingId);
        Gate::authorize('update', $book);

        $ebook = $data['formatBuku'] === 'ebook';
        $delta = ($ebook ? 0 : (int) $data['stok']) - $book->stok;
        $before = $book->only(['judul', 'isbn', 'harga', 'format_buku', 'category_id']);

        $store = app(CoverStore::class);
        $oldCover = $book->foto_sampul;
        $newCover = $this->foto ? $store->store($this->foto, $this->tenantId) : null;

        try {
            DB::transaction(function () use ($book, $data, $ebook, $delta, $newCover, $before): void {
                $book->fill([
                    'category_id' => (int) $data['categoryId'],
                    'judul' => $data['judul'],
                    'penulis' => $data['penulis'],
                    'penerbit' => $data['penerbit'],
                    'isbn' => $data['isbn'],
                    'tahun_terbit' => $data['tahunTerbit'] !== null && $data['tahunTerbit'] !== '' ? (int) $data['tahunTerbit'] : null,
                    'format_buku' => $data['formatBuku'],
                    'harga' => (int) $data['harga'],
                    'berat_gram' => $ebook ? 0 : (int) $data['beratGram'],
                    'deskripsi' => $data['deskripsi'] !== '' ? $data['deskripsi'] : null,
                ]);
                if ($newCover !== null) {
                    $book->foto_sampul = $newCover;
                }
                $book->save();

                // Perubahan stok lewat service agar tercatat sebagai movement.
                if ($delta !== 0) {
                    app(BookStockService::class)->adjust($book, $delta, 'adjustment', (string) Str::uuid(), 'koreksi dari form ubah');
                }

                app(AuditLogger::class)->record('book', $book->id, 'updated', $before,
                    $book->only(['judul', 'isbn', 'harga', 'format_buku', 'category_id']), $this->tenantId);
            });
        } catch (\Throwable $e) {
            $store->delete($newCover);
            throw $e;
        }

        // Berkas lama dihapus hanya setelah pengganti berhasil tersimpan.
        if ($newCover !== null) {
            $store->delete($oldCover);
        }

        $this->resetForm();
        $this->notif('Buku diperbarui.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    /** UC-13 alur 3a: soft delete — riwayat pesanan tetap utuh. */
    public function deleteBook(int $bookId): void
    {
        $book = Book::query()->findOrFail($bookId);
        Gate::authorize('delete', $book);

        $book->delete();
        app(AuditLogger::class)->record('book', $book->id, 'deleted', ['judul' => $book->judul], null, $this->tenantId);

        $this->notif("Buku “{$book->judul}” dihapus.");
    }

    /** UC-14: satu ketukan menandai habis (stok -> 0). */
    public function markOut(int $bookId): void
    {
        $book = Book::query()->findOrFail($bookId);
        Gate::authorize('update', $book);

        app(BookStockService::class)->markOutOfStock($book);
        $this->notif("“{$book->judul}” ditandai habis.");
    }

    public function restock(int $bookId, int $qty = 10): void
    {
        $book = Book::query()->findOrFail($bookId);
        Gate::authorize('update', $book);

        try {
            app(BookStockService::class)->adjust($book, max(1, $qty), 'restock', (string) Str::uuid(), 'restock manual');
            $this->notif("Stok “{$book->judul}” ditambah {$qty}.");
        } catch (DomainException $e) {
            $this->notif($e->getMessage(), 'err');
        }
    }

    /** Tampilkan / sembunyikan buku di katalog publik. */
    public function toggleActive(int $bookId): void
    {
        $book = Book::query()->findOrFail($bookId);
        Gate::authorize('update', $book);

        app(BookStockService::class)->toggleActive($book);
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'judul', 'penulis', 'penerbit', 'isbn', 'tahunTerbit', 'formatBuku',
            'harga', 'stok', 'beratGram', 'deskripsi', 'categoryId', 'foto',
        ]);
        $this->resetValidation();
    }

    private function notif(string $teks, string $tipe = 'ok'): void
    {
        $this->pesan = $teks;
        $this->pesanTipe = $tipe;
    }

    public function with(): array
    {
        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $this->cari).'%';

        return [
            'books' => Book::query()->with('category:id,nama_kategori')
                ->when($this->cari !== '', fn ($q) => $q->where(fn ($w) => $w
                    ->where('judul', 'like', $like)
                    ->orWhere('penulis', 'like', $like)
                    ->orWhere('isbn', 'like', $like)))
                ->when($this->filterKategori, fn ($q) => $q->where('category_id', (int) $this->filterKategori))
                ->orderBy('judul')
                ->paginate(15),
            'daftarKategori' => Category::orderBy('nama_kategori')->get(['id', 'nama_kategori']),
        ];
    }
}; ?>

<div class="space-y-6">
    @if ($pesan)
        <div role="status" class="rounded-lg px-4 py-3 text-sm font-medium {{ $pesanTipe === 'ok' ? 'bg-green-100 text-green-900' : 'bg-amber-100 text-amber-900' }}">{{ $pesan }}</div>
    @endif

    <!-- Form tambah / ubah -->
    <section class="rounded-xl border border-[#E4DED0] bg-white p-5">
        <h2 class="font-serif text-lg font-semibold">{{ $editingId ? 'Ubah buku' : 'Tambah buku' }}</h2>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="judul" class="text-sm font-medium">Judul</label>
                <input id="judul" type="text" wire:model="judul" class="mt-1 h-11 w-full rounded-lg border-[#A9B5B2]">
                @error('judul') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="penulis" class="text-sm font-medium">Penulis</label>
                <input id="penulis" type="text" wire:model="penulis" class="mt-1 h-11 w-full rounded-lg border-[#A9B5B2]">
                @error('penulis') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="penerbit" class="text-sm font-medium">Penerbit</label>
                <input id="penerbit" type="text" wire:model="penerbit" class="mt-1 h-11 w-full rounded-lg border-[#A9B5B2]">
                @error('penerbit') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="isbn" class="text-sm font-medium">ISBN</label>
                <input id="isbn" type="text" wire:model="isbn" inputmode="numeric" class="mt-1 h-11 w-full rounded-lg border-[#A9B5B2]">
                @error('isbn') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="categoryId" class="text-sm font-medium">Kategori</label>
                <select id="categoryId" wire:model="categoryId" class="mt-1 h-11 w-full rounded-lg border-[#A9B5B2]">
                    <option value="">Pilih kategori</option>
                    @foreach ($daftarKategori as $k)
                        <option value="{{ $k->id }}" wire:key="kat-{{ $k->id }}">{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
                @error('categoryId') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="formatBuku" class="text-sm font-medium">Format</label>
                <select id="formatBuku" wire:model="formatBuku" class="mt-1 h-11 w-full rounded-lg border-[#A9B5B2]">
                    <option value="fisik">Buku fisik</option>
                    <option value="ebook">E-book</option>
                    <option value="keduanya">Fisik + e-book</option>
                </select>
                @error('formatBuku') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="tahunTerbit" class="text-sm font-medium">Tahun terbit (opsional)</label>
                <input id="tahunTerbit" type="number" wire:model="tahunTerbit" class="mt-1 h-11 w-full rounded-lg border-[#A9B5B2]">
                @error('tahunTerbit') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="harga" class="text-sm font-medium">Harga (Rp)</label>
                <input id="harga" type="number" wire:model="harga" class="mt-1 h-11 w-full rounded-lg border-[#A9B5B2]">
                @error('harga') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="stok" class="text-sm font-medium">Stok (abaikan untuk e-book)</label>
                <input id="stok" type="number" wire:model="stok" class="mt-1 h-11 w-full rounded-lg border-[#A9B5B2]">
                @error('stok') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="beratGram" class="text-sm font-medium">Berat (gram)</label>
                <input id="beratGram" type="number" wire:model="beratGram" class="mt-1 h-11 w-full rounded-lg border-[#A9B5B2]">
                @error('beratGram') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="deskripsi" class="text-sm font-medium">Deskripsi (opsional)</label>
                <textarea id="deskripsi" rows="3" wire:model="deskripsi" class="mt-1 w-full rounded-lg border-[#A9B5B2]"></textarea>
                @error('deskripsi') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="foto" class="text-sm font-medium">Foto sampul (JPG/PNG/WebP, maks 2 MB; otomatis jadi WebP)</label>
                <input id="foto" type="file" wire:model="foto" accept="image/*" class="mt-1 block text-sm">
                @error('foto') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            @if ($editingId)
                <button type="button" wire:click="updateBook" wire:loading.attr="disabled" class="inline-flex h-11 items-center rounded-lg bg-hijau px-4 text-sm font-semibold text-white hover:bg-hijau-tua">Simpan perubahan</button>
                <button type="button" wire:click="cancelEdit" class="inline-flex h-11 items-center rounded-lg border border-[#A9B5B2] px-4 text-sm font-semibold">Batal</button>
            @else
                <button type="button" wire:click="createBook" wire:loading.attr="disabled" class="inline-flex h-11 items-center rounded-lg bg-hijau px-4 text-sm font-semibold text-white hover:bg-hijau-tua">Simpan buku</button>
            @endif
        </div>
    </section>

    <!-- Daftar buku -->
    <section class="rounded-xl border border-[#E4DED0] bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 p-5">
            <h2 class="font-serif text-lg font-semibold">Daftar buku</h2>
            <div class="flex flex-wrap gap-2">
                <input type="search" wire:model.live.debounce.300ms="cari" placeholder="Cari judul, penulis, ISBN…" class="h-11 rounded-lg border-[#A9B5B2]">
                <select wire:model.live="filterKategori" aria-label="Saring kategori" class="h-11 rounded-lg border-[#A9B5B2]">
                    <option value="">Kategori: semua</option>
                    @foreach ($daftarKategori as $k)
                        <option value="{{ $k->id }}" wire:key="fk-{{ $k->id }}">{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-sm">
                <thead class="bg-[#F1EDE3] text-left text-xs font-semibold text-gray-600">
                    <tr>
                        <th class="px-5 py-3">Sampul</th>
                        <th class="px-3 py-3">Buku</th>
                        <th class="px-3 py-3">Kategori</th>
                        <th class="px-3 py-3">Harga</th>
                        <th class="px-3 py-3">Stok</th>
                        <th class="px-3 py-3">Tampil</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4DED0]">
                    @forelse ($books as $b)
                        <tr wire:key="buku-{{ $b->id }}">
                            <td class="px-5 py-3">
                                @if ($b->foto_sampul)
                                    <img src="{{ asset('storage/'.$b->foto_sampul) }}" alt="" class="h-14 w-11 rounded object-cover">
                                @else
                                    <span class="grid h-14 w-11 place-items-center rounded bg-[#E4EEE9] font-serif text-lg font-bold text-hijau/50" aria-hidden="true">{{ mb_strtoupper(mb_substr($b->judul, 0, 1)) }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <p class="font-semibold">{{ $b->judul }}
                                    @if ($b->isOutOfStock())
                                        <span class="ms-1 rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700">HABIS</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-600">{{ $b->penulis }} · ISBN {{ $b->isbn }}</p>
                            </td>
                            <td class="px-3 py-3">{{ $b->category?->nama_kategori }}</td>
                            <td class="px-3 py-3">Rp {{ number_format($b->harga, 0, ',', '.') }}</td>
                            <td class="px-3 py-3 {{ $b->format_buku !== 'ebook' && $b->stok <= 5 ? 'font-semibold text-amber-700' : '' }}">
                                {{ $b->format_buku === 'ebook' ? '—' : $b->stok }}
                            </td>
                            <td class="px-3 py-3">
                                <button type="button" role="switch" aria-checked="{{ $b->is_active ? 'true' : 'false' }}"
                                        aria-label="Tampilkan {{ $b->judul }} di katalog" wire:click="toggleActive({{ $b->id }})"
                                        class="inline-flex min-h-11 items-center gap-2">
                                    <span class="relative inline-block h-6 w-11 rounded-full transition {{ $b->is_active ? 'bg-hijau' : 'bg-stone-400' }}">
                                        <span class="absolute top-0.5 size-5 rounded-full bg-white transition {{ $b->is_active ? 'left-5' : 'left-0.5' }}"></span>
                                    </span>
                                    <span class="text-xs">{{ $b->is_active ? 'Tampil' : 'Sembunyi' }}</span>
                                </button>
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <button type="button" wire:click="editBook({{ $b->id }})" class="me-2 min-h-11 text-sm font-semibold text-hijau underline">Edit</button>
                                @if ($b->format_buku !== 'ebook')
                                    <button type="button" wire:click="markOut({{ $b->id }})" class="me-2 min-h-11 text-sm underline">Tandai habis</button>
                                    <button type="button" wire:click="restock({{ $b->id }}, 10)" class="me-2 min-h-11 text-sm underline">+10 stok</button>
                                @endif
                                <button type="button" wire:click="deleteBook({{ $b->id }})"
                                        wire:confirm="Hapus buku {{ $b->judul }}? Riwayat pesanan tetap tersimpan."
                                        class="min-h-11 text-sm text-red-700 underline">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-gray-600">Belum ada buku. Tambahkan buku pertamamu di formulir atas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5">{{ $books->links() }}</div>
    </section>
</div>
