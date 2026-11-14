<?php
    // DATA CONTOH. Ganti dengan query asli (SellerOrder, Book) setelah tampilan disetujui.
    $ringkasan = [
        ['Pesanan baru', '12', 'Menunggu diproses'],
        ['Perlu dikirim', '7', 'Input resi diperlukan'],
        ['Stok menipis', '5', 'Di bawah 10 eksemplar'],
        ['Penjualan bulan ini', 'Rp 8.450.000', 'Contoh data'],
    ];
    $pesanan = [
        ['INV-0912-001', 'Rina A.', 'Pemrograman Web Dasar +1', 'Rp 185.000', 'Pending', 'Proses'],
        ['INV-0912-002', 'Budi S.', 'Basis Data Praktis', 'Rp 92.000', 'Diproses', 'Input resi'],
        ['INV-0911-014', 'Dewi K.', 'Kumpulan Cerpen Senja +2', 'Rp 247.000', 'Diproses', 'Input resi'],
        ['INV-0911-009', 'Agus P.', 'Algoritma untuk Pemula (e-book)', 'Rp 65.000', 'Dikirim', 'Lihat'],
        ['INV-0910-021', 'Maya L.', 'Desain Antarmuka +1', 'Rp 158.000', 'Selesai', 'Lihat'],
    ];
    $stok = [
        ['Basis Data Praktis', '978-602-0000-01-1', 3],
        ['Jaringan Komputer', '978-602-0000-02-8', 4],
        ['Pemrograman Web Dasar', '978-602-0000-03-5', 6],
        ['Desain Antarmuka', '978-602-0000-04-2', 8],
    ];
    $warnaStatus = [
        'Pending' => 'bg-amber-100 text-amber-900',
        'Diproses' => 'bg-blue-100 text-blue-900',
        'Dikirim' => 'bg-green-100 text-green-900',
        'Selesai' => 'bg-stone-200 text-stone-800',
    ];
    $namaToko = auth()->user()->tenant?->nama_toko ?? 'Toko Anda';
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['title' => 'Selamat datang, '.$namaToko,'subtitle' => 'Ringkasan pesanan dan stok hari ini']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Selamat datang, '.$namaToko),'subtitle' => 'Ringkasan pesanan dan stok hari ini']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <a href="#" class="hidden sm:inline-flex h-11 items-center gap-2 rounded-lg bg-hijau px-4 text-sm font-semibold text-white hover:bg-hijau-tua">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Tambah buku
        </a>
     <?php $__env->endSlot(); ?>

    <!-- Kartu ringkasan -->
    <section class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $ringkasan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $nilai, $ket]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="rounded-xl border border-[#E4DED0] bg-white px-5 py-4">
                <p class="text-sm font-medium text-gray-600"><?php echo e($label); ?></p>
                <p class="mt-2 font-serif text-2xl xl:text-3xl font-semibold"><?php echo e($nilai); ?></p>
                <p class="mt-1 text-xs text-gray-600"><?php echo e($ket); ?></p>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </section>

    <div class="grid gap-4 xl:grid-cols-[1fr_340px]">
        <!-- Pesanan masuk -->
        <section class="rounded-xl border border-[#E4DED0] bg-white">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="font-serif text-lg font-semibold">Pesanan masuk</h2>
                <a href="#" class="text-sm font-semibold text-hijau hover:underline">Lihat semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead class="bg-[#F1EDE3] text-left text-xs font-semibold text-gray-600">
                        <tr>
                            <th class="px-5 py-2.5">No. pesanan</th>
                            <th class="px-3 py-2.5">Pembeli</th>
                            <th class="px-3 py-2.5">Item</th>
                            <th class="px-3 py-2.5">Subtotal</th>
                            <th class="px-3 py-2.5">Status</th>
                            <th class="px-5 py-2.5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E4DED0]">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pesanan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$no, $pembeli, $item, $subtotal, $status, $aksi]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td class="px-5 py-3.5"><?php echo e($no); ?></td>
                                <td class="px-3 py-3.5"><?php echo e($pembeli); ?></td>
                                <td class="px-3 py-3.5"><?php echo e($item); ?></td>
                                <td class="px-3 py-3.5"><?php echo e($subtotal); ?></td>
                                <td class="px-3 py-3.5"><span class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($warnaStatus[$status]); ?>"><?php echo e($status); ?></span></td>
                                <td class="px-5 py-3.5">
                                    <a href="#" class="inline-block rounded-md px-3 py-1.5 text-xs font-semibold <?php echo e($aksi === 'Lihat' ? 'border border-[#A9B5B2] text-tinta' : 'bg-hijau text-white'); ?>"><?php echo e($aksi); ?></a>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Stok menipis -->
        <section class="rounded-xl border border-[#E4DED0] bg-white self-start">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="font-serif text-lg font-semibold">Stok menipis</h2>
                <a href="#" class="text-sm font-semibold text-hijau hover:underline">Kelola</a>
            </div>
            <ul>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stok; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$judul, $isbn, $sisa]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="flex items-center gap-3 px-5 py-3">
                        <div class="h-12 w-9 rounded bg-[#D5E3DF]" aria-hidden="true"></div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold"><?php echo e($judul); ?></p>
                            <p class="text-xs text-gray-600">ISBN <?php echo e($isbn); ?></p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($sisa < 5 ? 'bg-amber-100 text-amber-900' : 'bg-stone-200 text-stone-800'); ?>">Sisa <?php echo e($sisa); ?></span>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </section>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal895f6ef515592ffd4805667c75b9d7a7)): ?>
<?php $attributes = $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7; ?>
<?php unset($__attributesOriginal895f6ef515592ffd4805667c75b9d7a7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal895f6ef515592ffd4805667c75b9d7a7)): ?>
<?php $component = $__componentOriginal895f6ef515592ffd4805667c75b9d7a7; ?>
<?php unset($__componentOriginal895f6ef515592ffd4805667c75b9d7a7); ?>
<?php endif; ?>
<?php /**PATH D:\ServBay\www\servbay\projek\TOKO-BUKU\resources\views\dashboard\seller.blade.php ENDPATH**/ ?>