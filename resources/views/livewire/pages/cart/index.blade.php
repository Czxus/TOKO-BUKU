<?php

use App\Models\User;
use App\Modules\Ordering\Data\CartView;
use App\Modules\Ordering\Exceptions\CartException;
use App\Modules\Ordering\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

/**
 * Keranjang pembeli (UC-03). Pembeli diambil dari sesi login (auth), BUKAN dari properti
 * klien. Semua harga di tampilan berasal dari CartService::view() yang selalu merevalidasi
 * harga/stok dari basis data; tombol di halaman ini hanya mengirim book_id.
 */
new #[Layout('layouts.katalog')] class extends Component
{
    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user?->isPembeli(), 403);
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    /** Null bila keranjang tidak dapat dibaca (Redis bermasalah); pesan galat diisi. */
    #[Computed]
    public function cart(): ?CartView
    {
        try {
            return app(CartService::class)->view($this->currentUser());
        } catch (CartException $e) {
            $this->addError('cart', $e->getMessage());

            return null;
        }
    }

    public function increment(int $bookId): void
    {
        $this->jalankan(fn (CartService $cart) => $cart->changeQuantity($this->currentUser(), $bookId, 1));
    }

    public function decrement(int $bookId): void
    {
        $this->jalankan(fn (CartService $cart) => $cart->changeQuantity($this->currentUser(), $bookId, -1));
    }

    public function remove(int $bookId): void
    {
        $this->jalankan(fn (CartService $cart) => $cart->remove($this->currentUser(), $bookId));
    }

    public function clear(): void
    {
        $this->jalankan(fn (CartService $cart) => $cart->clear($this->currentUser()));
    }

    /** Pembeli menyetujui harga terbaru dari database. */
    public function acceptPrices(): void
    {
        $this->jalankan(fn (CartService $cart) => $cart->acceptPriceChanges($this->currentUser()));
    }

    private function jalankan(\Closure $aksi): void
    {
        $this->resetErrorBag('cart');

        try {
            $aksi(app(CartService::class));
        } catch (CartException $e) {
            $this->addError('cart', $e->getMessage());
        }

        unset($this->cart);
        $this->dispatch('cart-updated');
    }
}; ?>

@php
    $cart = $this->cart;
    $labelFormat = ['fisik' => 'Buku fisik', 'ebook' => 'E-book', 'keduanya' => 'Fisik + e-book'];
    $rupiah = fn (int $n): string => 'Rp '.number_format($n, 0, ',', '.');
    $ttlJam = round((int) config('cart.ttl_seconds') / 3600, 1);
@endphp

<div class="space-y-6">
    <div class="space-y-1">
        <a href="{{ route('katalog') }}" wire:navigate class="text-sm font-medium text-hijau hover:underline">← Lanjut belanja</a>
        <h1 class="font-serif text-2xl lg:text-3xl font-semibold">Keranjang</h1>
        <p class="text-sm text-gray-600">
            Item dari beberapa toko dikelompokkan per toko. Keranjang disimpan sementara dan kedaluwarsa
            bila tidak ada aktivitas selama {{ $ttlJam }} jam. Harga dan stok diperiksa ulang setiap kali ditampilkan.
        </p>
    </div>

    @error('cart')
        <div class="rounded-lg bg-amber-100 px-4 py-3 text-sm font-medium text-amber-900" role="alert">{{ $message }}</div>
    @enderror

    @if ($cart === null)
        <div class="rounded-xl border border-[#E4DED0] bg-white px-6 py-12 text-center space-y-3">
            <p class="font-serif text-xl font-semibold">Keranjang belum dapat dimuat</p>
            <p class="text-sm text-gray-600">Coba muat ulang halaman beberapa saat lagi.</p>
        </div>
    @elseif ($cart->isEmpty())
        <div class="rounded-xl border border-[#E4DED0] bg-white px-6 py-12 text-center space-y-3">
            <p class="font-serif text-xl font-semibold">Keranjang kosong</p>
            <p class="text-sm text-gray-600">Tambahkan buku dari katalog untuk mulai berbelanja.</p>
            <a href="{{ route('katalog') }}" wire:navigate class="inline-flex h-11 items-center rounded-lg bg-hijau px-4 text-sm font-semibold text-white hover:bg-hijau-tua">Jelajahi katalog</a>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-3 lg:items-start">
            <div class="space-y-4 lg:col-span-2">
                @foreach ($cart->tenantGroups() as $group)
                    <section wire:key="toko-{{ $group['tenant_id'] }}" data-test="tenant-group" class="rounded-xl border border-[#E4DED0] bg-white overflow-hidden">
                        <div class="flex items-baseline justify-between gap-3 bg-[#E4EEE9] px-4 py-3">
                            <h2 class="text-sm font-extrabold uppercase tracking-wide text-hijau">{{ $group['tenant_name'] }}</h2>
                            <span class="text-sm font-semibold">{{ $rupiah($group['subtotal']) }}</span>
                        </div>

                        <ul class="divide-y divide-[#E4DED0]">
                            @foreach ($group['lines'] as $line)
                                <li wire:key="buku-{{ $line->bookId }}" class="flex gap-3 p-4 {{ $line->available ? '' : 'bg-stone-50' }}">
                                    <div class="grid size-16 shrink-0 place-items-center overflow-hidden rounded bg-[#E4EEE9]">
                                        @if ($line->coverPath)
                                            <img src="{{ asset('storage/'.$line->coverPath) }}" alt="Sampul {{ $line->title }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="font-serif text-2xl font-bold text-hijau/40" aria-hidden="true">{{ mb_strtoupper(mb_substr($line->title, 0, 1)) }}</span>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="font-serif font-semibold leading-snug line-clamp-2">{{ $line->title }}</p>
                                                <p class="truncate text-xs text-gray-600">
                                                    {{ $line->author }}{{ $line->author ? ' · ' : '' }}{{ $labelFormat[$line->format] ?? $line->format }}
                                                </p>
                                                <p class="text-xs text-gray-600">{{ $rupiah($line->unitPrice) }} / item</p>
                                            </div>
                                            <p class="shrink-0 font-semibold">{{ $rupiah($line->lineTotal) }}</p>
                                        </div>

                                        @if ($line->hasIssue('book_unavailable'))
                                            <p class="mt-1 text-xs font-medium text-red-700">Buku ini tidak lagi tersedia. Hapus dari keranjang untuk melanjutkan.</p>
                                        @elseif ($line->hasIssue('insufficient_stock'))
                                            <p class="mt-1 text-xs font-medium text-red-700">Stok tidak mencukupi. Tersedia {{ $line->maxQuantity }}; kurangi jumlahnya.</p>
                                        @endif

                                        @if ($line->priceChanged())
                                            <p class="mt-1 text-xs font-medium text-amber-700">Harga diperbarui: dari {{ $rupiah($line->priceAtAdd) }} menjadi {{ $rupiah($line->unitPrice) }}.</p>
                                        @endif

                                        <div class="mt-2 flex items-center gap-2">
                                            <button type="button" wire:click="decrement({{ $line->bookId }})" wire:loading.attr="disabled"
                                                    class="min-h-9 min-w-9 rounded-lg border border-[#A9B5B2] text-lg leading-none hover:bg-[#E4EEE9]" aria-label="Kurangi jumlah">−</button>
                                            <span class="w-8 text-center text-sm font-medium">{{ $line->quantity }}</span>
                                            <button type="button" wire:click="increment({{ $line->bookId }})" wire:loading.attr="disabled" @disabled(! $line->available || $line->quantity >= $line->maxQuantity)
                                                    class="min-h-9 min-w-9 rounded-lg border border-[#A9B5B2] text-lg leading-none hover:bg-[#E4EEE9] disabled:cursor-not-allowed disabled:opacity-40" aria-label="Tambah jumlah">+</button>
                                            <button type="button" wire:click="remove({{ $line->bookId }})" class="ms-auto text-sm font-medium text-red-700 underline">Hapus</button>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>

            <aside class="space-y-3 rounded-xl border border-[#E4DED0] bg-white p-4 lg:sticky lg:top-4" aria-label="Ringkasan belanja">
                <h2 class="font-serif text-lg font-semibold">Ringkasan</h2>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-600">Subtotal ({{ $cart->totalQuantity }} item)</dt><dd class="font-medium">{{ $rupiah($cart->subtotal) }}</dd></div>
                    @if ($cart->serviceFeeAmount > 0)
                        <div class="flex justify-between"><dt class="text-gray-600">Biaya layanan</dt><dd class="font-medium">{{ $rupiah($cart->serviceFeeAmount) }}</dd></div>
                    @endif
                    <div class="flex justify-between border-t border-[#E4DED0] pt-2 text-base"><dt class="font-semibold">Total</dt><dd class="font-bold" data-test="grand-total">{{ $rupiah($cart->grandTotal()) }}</dd></div>
                </dl>
                <p class="text-xs text-gray-600">Ongkos kirim dihitung saat checkout.</p>

                @if ($cart->hasPriceChanges())
                    <button type="button" wire:click="acceptPrices" class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-amber-600 text-sm font-semibold text-amber-800 hover:bg-amber-50">
                        Setujui harga terbaru
                    </button>
                @endif

                @if ($cart->hasBlockingIssues)
                    <p class="text-xs font-medium text-red-700">Perbaiki item bermasalah sebelum melanjutkan.</p>
                @endif

                {{-- Rute checkout dibuat pada Pertemuan 9; sementara tombol hanya aktif bila keranjang layak pesan. --}}
                <button type="button" @disabled(! $cart->isOrderable())
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-hijau text-sm font-semibold text-white hover:bg-hijau-tua disabled:cursor-not-allowed disabled:bg-stone-300 disabled:text-stone-500">
                    Lanjut ke Pembayaran
                </button>

                <button type="button" wire:click="clear" wire:confirm="Kosongkan seluruh keranjang?" class="w-full text-center text-sm font-medium text-gray-600 underline">
                    Kosongkan keranjang
                </button>
            </aside>
        </div>
    @endif
</div>