<?php

use App\Modules\Ordering\Exceptions\CartException;
use App\Modules\Ordering\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

/**
 * Badge jumlah item keranjang untuk header. Hanya membaca Redis (tanpa query database)
 * dan tidak memperpanjang TTL. Bila Redis bermasalah, badge tampil kosong, bukan error.
 */
new class extends Component
{
    #[Computed]
    public function jumlah(): int
    {
        $user = auth()->user();

        if ($user === null || ! $user->isPembeli()) {
            return 0;
        }

        try {
            return app(CartService::class)->itemCount($user);
        } catch (CartException) {
            return 0;
        }
    }

    #[On('cart-updated')]
    public function segarkan(): void
    {
        unset($this->jumlah);
    }
}; ?>

<a href="{{ route('keranjang') }}" wire:navigate
   class="relative inline-flex h-10 items-center gap-2 rounded-lg px-3 text-sm font-medium hover:bg-hijau-tua"
   aria-label="Keranjang, {{ $this->jumlah }} item">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 6h15l-1.5 9h-12zM6 6 5 3H2M9 20m-1 0a1 1 0 1 0 2 0a1 1 0 1 0-2 0M18 20m-1 0a1 1 0 1 0 2 0a1 1 0 1 0-2 0"/></svg>
    <span>Keranjang</span>
    @if ($this->jumlah > 0)
        <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-amber-buku px-1.5 text-xs font-semibold text-tinta" data-test="cart-badge">{{ $this->jumlah }}</span>
    @endif
</a>
