<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

?>

<?php
    $user = auth()->user();

    // Menu per peran. Ganti '#' dengan route() setelah halamannya dibuat.
    if ($user->isAdminPlatform()) {
        $peran = 'Admin Platform';
        $menu = [
            ['Dasbor', route('dashboard'), 'M3 11 12 3l9 8v10H3z'],
            ['Toko Mitra (Tenant)', '#', 'M3 9l2-5h14l2 5M4 9v11h16V9M9 20v-6h6v6'],
            ['Komisi', '#', 'M19 5 5 19M7 7m-2 0a2 2 0 1 0 4 0a2 2 0 1 0-4 0M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0-4 0'],
            ['Audit Transaksi', '#', 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01'],
            ['Pemecahan Dana', '#', 'M3 7h16a2 2 0 0 1 2 2v10H5a2 2 0 0 1-2-2zM3 7V5a2 2 0 0 1 2-2h12M16 14h2'],
            ['Pengguna', '#', 'M9 8m-3.5 0a3.5 3.5 0 1 0 7 0a3.5 3.5 0 1 0-7 0M2 20c0-3.5 3-6 7-6s7 2.5 7 6'],
        ];
    } elseif ($user->isSellerAdmin()) {
        $peran = 'Penjual';
        $menu = [
            ['Dasbor', route('dashboard'), 'M3 11 12 3l9 8v10H3z'],
            ['Buku & Stok', '#', 'M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5zM4 20.5A2.5 2.5 0 0 0 6.5 23H20v-5'],
            ['Pesanan', '#', 'M21 8 12 3 3 8v8l9 5 9-5zM3 8l9 5 9-5M12 13v8'],
            ['Pratinjau & E-book', '#', 'M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12zM12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0-6 0'],
            ['Laporan Penjualan', '#', 'M4 20V10M10 20V4M16 20v-7M22 20H2'],
            ['Pengaturan Toko', '#', 'M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0-6 0M12 2v3M12 19v3M2 12h3M19 12h3'],
        ];
    } else {
        $peran = 'Pembeli';
        $menu = [
            ['Beranda', route('dashboard'), 'M3 11 12 3l9 8v10H3z'],
            ['Katalog Buku', route('katalog'), 'M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5zM4 20.5A2.5 2.5 0 0 0 6.5 23H20v-5'],
            ['Keranjang', '#', 'M6 6h15l-1.5 9h-12zM6 6 5 3H2M9 20m-1 0a1 1 0 1 0 2 0a1 1 0 1 0-2 0M18 20m-1 0a1 1 0 1 0 2 0a1 1 0 1 0-2 0'],
            ['Pesanan Saya', '#', 'M21 8 12 3 3 8v8l9 5 9-5zM3 8l9 5 9-5M12 13v8'],
        ];
    }

    $namaToko = $user->tenant?->nama_toko;
?>

<aside x-data="{ open: false }" class="bg-hijau text-krem lg:w-60 lg:shrink-0 lg:min-h-screen lg:sticky lg:top-0 lg:h-screen flex flex-col">
    <div class="flex items-center justify-between px-5 py-5 lg:py-7">
        <a href="<?php echo e(route('dashboard')); ?>" wire:navigate class="flex items-center gap-2.5">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#F2B45A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5z"/><path d="M4 20.5A2.5 2.5 0 0 0 6.5 23H20v-5"/><path d="M9 8h7"/></svg>
            <span class="font-serif text-xl font-bold">Toko Buku</span>
        </a>
        <button type="button" class="lg:hidden p-2 rounded-md hover:bg-hijau-tua" x-on:click="open = ! open" aria-label="Buka menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <div :class="open ? 'flex' : 'hidden'" class="lg:flex flex-col flex-1 px-5 pb-5 gap-6">
        <div class="rounded-lg bg-hijau-tua px-3 py-3">
            <p class="text-xs text-[#D5E3DF]"><?php echo e($namaToko ? 'Toko aktif' : 'Masuk sebagai'); ?></p>
            <p class="text-sm font-semibold text-white"><?php echo e($namaToko ?? $peran); ?></p>
        </div>

        <nav class="flex flex-col gap-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $menu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $url, $ikon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php $aktif = $url !== '#' && url()->current() === $url; ?>
                <a href="<?php echo e($url); ?>" <?php if($url !== '#'): ?> wire:navigate <?php endif; ?>
                   class="flex items-center gap-3 rounded-lg px-3 h-11 text-sm font-medium <?php echo e($aktif ? 'bg-krem text-hijau font-semibold' : 'text-[#D5E3DF] hover:bg-hijau-tua'); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="<?php echo e($ikon); ?>"/></svg>
                    <?php echo e($label); ?>

                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </nav>

        <div class="mt-auto border-t border-white/15 pt-4">
            <p class="px-3 text-sm font-semibold text-white truncate"><?php echo e($user->name); ?></p>
            <p class="px-3 text-xs text-[#D5E3DF] truncate mb-2"><?php echo e($user->email); ?></p>
            <button wire:click="logout" type="button" class="flex w-full items-center gap-3 rounded-lg px-3 h-11 text-sm font-medium text-[#D5E3DF] hover:bg-hijau-tua">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                Keluar
            </button>
        </div>
    </div>
</aside><?php /**PATH D:\ServBay\www\servbay\projek\TOKO-BUKU\resources\views\livewire\layout\sidebar.blade.php ENDPATH**/ ?>