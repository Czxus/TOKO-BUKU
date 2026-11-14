<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Tenant;
use App\Services\PublicCatalogQuery;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

?>

<?php
    $labelFormat = ['fisik' => 'Buku fisik', 'ebook' => 'E-book', 'keduanya' => 'Fisik + e-book'];
    $adaFilter = $cari !== '' || $kategori !== '' || $format !== '';
?>

<div class="space-y-6">
    <!-- Judul halaman -->
    <div class="space-y-1">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($toko): ?>
            <a href="<?php echo e(route('katalog')); ?>" wire:navigate class="text-sm font-medium text-hijau hover:underline">← Semua toko</a>
            <h1 class="font-serif text-2xl lg:text-3xl font-semibold"><?php echo e($toko->nama_toko); ?></h1>
            <p class="text-sm text-gray-600"><?php echo e($toko->alamat_gudang); ?></p>
        <?php else: ?>
            <h1 class="font-serif text-2xl lg:text-3xl font-semibold">Katalog Buku</h1>
            <p class="text-sm text-gray-600">Buku fisik dan e-book dari semua toko mitra.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarKategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e($k->id); ?>"><?php echo e($k->nama_kategori); ?></option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </select>
        <select wire:model.live="format" class="h-12 border-[#A9B5B2] bg-white focus:border-hijau focus:ring-hijau rounded-lg" aria-label="Format">
            <option value="">Semua format</option>
            <option value="fisik">Buku fisik</option>
            <option value="ebook">E-book</option>
        </select>
    </section>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pesan): ?>
        <div class="rounded-lg px-4 py-3 text-sm font-medium <?php echo e($pesanTipe === 'ok' ? 'bg-green-100 text-green-900' : 'bg-amber-100 text-amber-900'); ?>" role="status">
            <?php echo e($pesan); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Daftar buku -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($books->isEmpty()): ?>
        <div class="rounded-xl border border-[#E4DED0] bg-white px-6 py-12 text-center space-y-3">
            <p class="font-serif text-xl font-semibold">Buku tidak ditemukan</p>
            <p class="text-sm text-gray-600">Coba kata kunci lain atau ubah filter.</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($adaFilter): ?>
                <button type="button" wire:click="reset_filter" class="inline-flex h-11 items-center rounded-lg bg-hijau px-4 text-sm font-semibold text-white hover:bg-hijau-tua">Hapus filter</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php else: ?>
        <section class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $habis = $b->format_buku === 'fisik' && (int) $b->stok <= 0;
                ?>
                <article <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'buku-'.e($b->id).''; ?>wire:key="buku-<?php echo e($b->id); ?>" class="rounded-xl border border-[#E4DED0] bg-white overflow-hidden flex flex-col">
                    <!-- Sampul -->
                    <div class="relative aspect-[3/4] bg-[#E4EEE9] grid place-items-center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($b->foto_sampul): ?>
                            <img src="<?php echo e(asset('storage/'.$b->foto_sampul)); ?>" alt="Sampul <?php echo e($b->judul); ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                            <span class="font-serif text-5xl font-bold text-hijau/40" aria-hidden="true"><?php echo e(mb_strtoupper(mb_substr($b->judul, 0, 1))); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span class="absolute left-2 top-2 rounded-full bg-white/90 px-2.5 py-1 text-xs font-semibold text-hijau"><?php echo e($labelFormat[$b->format_buku] ?? $b->format_buku); ?></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($habis): ?>
                            <span class="absolute inset-x-0 bottom-0 bg-tinta/80 py-1.5 text-center text-xs font-semibold text-white">Stok Habis</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="flex flex-1 flex-col p-3 gap-1">
                        <h2 class="font-serif text-base font-semibold leading-snug line-clamp-2"><?php echo e($b->judul); ?></h2>
                        <p class="text-xs text-gray-600 truncate"><?php echo e($b->penulis); ?></p>
                        <p class="text-xs text-gray-600 truncate">ISBN <?php echo e($b->isbn); ?></p>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $toko): ?>
                            <a href="<?php echo e(route('toko.katalog', $b->tenant->domain_slug)); ?>" wire:navigate class="text-xs font-medium text-hijau hover:underline truncate">
                                <?php echo e($b->tenant->nama_toko); ?>

                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <p class="mt-auto pt-2 font-semibold">Rp <?php echo e(number_format($b->harga, 0, ',', '.')); ?></p>

                        <button type="button" wire:click="tambah(<?php echo e($b->id); ?>)" wire:loading.attr="disabled" <?php if($habis): echo 'disabled'; endif; ?>
                                class="mt-1 inline-flex h-11 w-full items-center justify-center rounded-lg text-sm font-semibold <?php echo e($habis ? 'bg-stone-200 text-stone-500 cursor-not-allowed' : 'bg-hijau text-white hover:bg-hijau-tua'); ?>">
                            <?php echo e($habis ? 'Stok habis' : 'Tambah ke keranjang'); ?>

                        </button>
                    </div>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <div><?php echo e($books->links()); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH D:\ServBay\www\servbay\projek\TOKO-BUKU\resources\views\livewire\pages\catalog\index.blade.php ENDPATH**/ ?>