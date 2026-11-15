<?php

use App\Models\Category;
use App\Models\Tenant;
use App\Modules\Ordering\Exceptions\CartException;
use App\Modules\Ordering\Services\CartService;
use App\Services\PublicCatalogQuery;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.katalog')] class extends Component
{
    use WithPagination;

    public ?string $slug = null;

    #[Url(as: 'q')]
    public string $cari = '';

    #[Url(as: 'kategori')]
    public string $kategori = '';

    #[Url(as: 'format')]
    public string $format = '';

    public ?string $pesan = null;

    public string $pesanTipe = 'ok';

    public function mount(?string $slug = null): void
    {
        $this->slug = $slug;

        if ($slug !== null) {
            // Toko nonaktif / tidak ada: katalognya disembunyikan (UC-01 alur 3b).
            abort_unless(
                Tenant::where('domain_slug', $slug)->where('status', 'active')->exists(),
                404
            );
        }
    }

    public function updated(string $name): void
    {
        if (in_array($name, ['cari', 'kategori', 'format'], true)) {
            $this->resetPage();
        }
    }

    public function reset_filter(): void
    {
        $this->reset('cari', 'kategori', 'format');
        $this->resetPage();
    }

    public function tambah(int $bookId): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        if (! $user->isPembeli()) {
            $this->notif('Hanya akun pembeli yang dapat berbelanja.', 'err');

            return;
        }

        // Keranjang disimpan di Redis oleh CartService (Modul 8). Service memvalidasi buku
        // aktif, toko aktif, stok, dan batas jumlah dari database; katalog tidak menentukan harga.
        try {
            $book = app(CartService::class)->add($user, $bookId, 1);
        } catch (CartException $e) {
            $this->notif($e->getMessage(), 'err');

            return;
        }

        $this->dispatch('cart-updated');
        $this->notif('“'.$book->judul.'” ditambahkan ke keranjang.', 'ok');
    }

    private function notif(string $teks, string $tipe): void
    {
        $this->pesan = $teks;
        $this->pesanTipe = $tipe;
    }

    public function with(): array
    {
        return [
            'books' => app(PublicCatalogQuery::class)->getPublicBooks(
                12,
                $this->cari !== '' ? $this->cari : null,
                $this->kategori !== '' ? (int) $this->kategori : null,
                $this->format !== '' ? $this->format : null,
                $this->slug,
            ),
            'daftarKategori' => Category::orderBy('nama_kategori')->get(['id', 'nama_kategori']),
            'toko' => $this->slug ? Tenant::where('domain_slug', $this->slug)->first() : null,
        ];
    }
}; ?>

@php
    $labelFormat = ['fisik' => 'Buku fisik', 'ebook' => 'E-book', 'keduanya' => 'Fisik + e-book'];
    $adaFilter = $cari !== '' || $kategori !== '' || $format !== '';
@endphp

<div class="space-y-6">
    <!-- Judul halaman -->
    <div class="space-y-1">
        @if ($toko)
            <a href="{{ route('katalog') }}" wire:navigate class="text-sm font-medium text-hijau hover:underline">← Semua toko</a>
            <h1 class="font-serif text-2xl lg:text-3xl font-semibold">{{ $toko->nama_toko }}</h1>
            <p class="text-sm text-gray-600">{{ $toko->alamat_gudang }}</p>
        @else
            <h1 class="font-serif text-2xl lg:text-3xl font-semibold">Katalog Buku</h1>
            <p class="text-sm text-gray-600">Buku fisik dan e-book dari semua toko mitra.</p>
        @endif
    </div>

    <!-- Pencarian dan filter -->
    <section class="rounded-xl border border-[#E4DED0] bg-white p-4 grid gap-3 sm:grid-cols-[1fr_auto_auto]">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input wire:model.live.debounce.400ms="cari" type="search" placeholder="Cari judul, penulis, penerbit, atau ISBN"
                   class="h-12 w-full pl-10 border-[#A9B5B2] bg-white placeholder:text-gray-500 focus:border-hijau focus:ring-hijau rounded-lg">
        </div>
        <select wire:model.live="kategori" class="h-12 border-[#A9B5B2] bg-white focus:border-hijau focus:ring-hijau rounded-lg" aria-label="Kategori">
            <option value="">Semua kategori</option>
            @foreach ($daftarKategori as $k)
                <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
            @endforeach
        </select>
        <select wire:model.live="format" class="h-12 border-[#A9B5B2] bg-white focus:border-hijau focus:ring-hijau rounded-lg" aria-label="Format">
            <option value="">Semua format</option>
            <option value="fisik">Buku fisik</option>
            <option value="ebook">E-book</option>
        </select>
    </section>

    @if ($pesan)
        <div class="rounded-lg px-4 py-3 text-sm font-medium {{ $pesanTipe === 'ok' ? 'bg-green-100 text-green-900' : 'bg-amber-100 text-amber-900' }}" role="status">
            {{ $pesan }}
        </div>
    @endif

    <!-- Daftar buku -->
    @if ($books->isEmpty())
        <div class="rounded-xl border border-[#E4DED0] bg-white px-6 py-12 text-center space-y-3">
            <p class="font-serif text-xl font-semibold">Buku tidak ditemukan</p>
            <p class="text-sm text-gray-600">Coba kata kunci lain atau ubah filter.</p>
            @if ($adaFilter)
                <button type="button" wire:click="reset_filter" class="inline-flex h-11 items-center rounded-lg bg-hijau px-4 text-sm font-semibold text-white hover:bg-hijau-tua">Hapus filter</button>
            @endif
        </div>
    @else
        <section class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($books as $b)
                @php
                    $habis = $b->format_buku === 'fisik' && (int) $b->stok <= 0;
                @endphp
                <article wire:key="buku-{{ $b->id }}" class="rounded-xl border border-[#E4DED0] bg-white overflow-hidden flex flex-col">
                    <!-- Sampul -->
                    <div class="relative aspect-[3/4] bg-[#E4EEE9] grid place-items-center">
                        @if ($b->foto_sampul)
                            <img src="{{ asset('storage/'.$b->foto_sampul) }}" alt="Sampul {{ $b->judul }}" class="h-full w-full object-cover">
                        @else
                            <span class="font-serif text-5xl font-bold text-hijau/40" aria-hidden="true">{{ mb_strtoupper(mb_substr($b->judul, 0, 1)) }}</span>
                        @endif
                        <span class="absolute left-2 top-2 rounded-full bg-white/90 px-2.5 py-1 text-xs font-semibold text-hijau">{{ $labelFormat[$b->format_buku] ?? $b->format_buku }}</span>
                        @if ($habis)
                            <span class="absolute inset-x-0 bottom-0 bg-tinta/80 py-1.5 text-center text-xs font-semibold text-white">Stok Habis</span>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col p-3 gap-1">
                        <h2 class="font-serif text-base font-semibold leading-snug line-clamp-2">{{ $b->judul }}</h2>
                        <p class="text-xs text-gray-600 truncate">{{ $b->penulis }}</p>
                        <p class="text-xs text-gray-600 truncate">ISBN {{ $b->isbn }}</p>

                        @if (! $toko)
                            <a href="{{ route('toko.katalog', $b->tenant->domain_slug) }}" wire:navigate class="text-xs font-medium text-hijau hover:underline truncate">
                                {{ $b->tenant->nama_toko }}
                            </a>
                        @endif

                        <p class="mt-auto pt-2 font-semibold">Rp {{ number_format($b->harga, 0, ',', '.') }}</p>

                        <button type="button" wire:click="tambah({{ $b->id }})" wire:loading.attr="disabled" @disabled($habis)
                                class="mt-1 inline-flex h-11 w-full items-center justify-center rounded-lg text-sm font-semibold {{ $habis ? 'bg-stone-200 text-stone-500 cursor-not-allowed' : 'bg-hijau text-white hover:bg-hijau-tua' }}">
                            {{ $habis ? 'Stok habis' : 'Tambah ke keranjang' }}
                        </button>
                    </div>
                </article>
            @endforeach
        </section>

        <div>{{ $books->links() }}</div>
    @endif
</div>
