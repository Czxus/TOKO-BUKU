<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Toko Buku')); ?></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="font-sans text-tinta antialiased bg-krem">
        <div class="min-h-screen lg:flex">
            <!-- Panel brand -->
            <aside class="bg-hijau text-krem px-8 py-8 lg:w-[480px] lg:shrink-0 lg:p-14 flex flex-col justify-between gap-10">
                <a href="/" wire:navigate class="flex items-center gap-3">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#F2B45A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5z"/><path d="M4 20.5A2.5 2.5 0 0 0 6.5 23H20v-5"/><path d="M9 8h7"/></svg>
                    <span class="font-serif text-2xl font-bold">Toko Buku</span>
                </a>

                <div class="hidden lg:block space-y-5">
                    <h1 class="font-serif text-4xl font-semibold leading-tight">Satu rak, banyak penerbit.</h1>
                    <p class="text-[#D5E3DF] leading-relaxed">Platform penjualan buku fisik dan e-book dari banyak toko mitra, dengan pembayaran QRIS dan pratinjau halaman sebelum membeli.</p>
                </div>

                <ul class="hidden lg:block space-y-3 text-sm text-[#D5E3DF]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['Katalog lintas toko dengan pencarian ISBN', 'Pemecahan dana otomatis ke setiap penjual', 'Pesanan baru tampil real time di dasbor penjual']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fitur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li class="flex items-center gap-3">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F2B45A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            <?php echo e($fitur); ?>

                        </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ul>
            </aside>

            <!-- Area form -->
            <main class="flex-1 flex items-center justify-center px-6 py-12">
                <div class="w-full max-w-sm">
                    <?php echo e($slot); ?>

                </div>
            </main>
        </div>
    </body>
</html>
<?php /**PATH C:\ServBay\www\servbay\TOKO-BUKU\resources\views/layouts/guest.blade.php ENDPATH**/ ?>