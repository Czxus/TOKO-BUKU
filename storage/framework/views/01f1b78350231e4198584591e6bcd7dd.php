<?php
    // DATA CONTOH. Ganti dengan query asli (Tenant, Order, Payment) setelah tampilan disetujui.
    $ringkasan = [
        ['Toko mitra aktif', '24', '2 menunggu persetujuan'],
        ['Nilai transaksi bulan ini', 'Rp 126.400.000', 'Contoh data'],
        ['Komisi platform', 'Rp 9.480.000', 'Contoh data'],
        ['Pembayaran tertunda', '6', 'Menunggu callback gateway'],
    ];
    $grafik = [['Sen', 45], ['Sel', 60], ['Rab', 50], ['Kam', 75], ['Jum', 85], ['Sab', 65], ['Min', 40]];
    $tenantBaru = [['Toko Cahaya Pustaka', 'cahaya-pustaka'], ['Penerbit Nusa Aksara', 'nusa-aksara']];
    $transaksi = [
        ['INV-20261114-001', 'Rina A.', 'Toko Harapan', 'Rp 185.000', 'QRIS', 'Sukses', '14 Nov, 09:12'],
        ['INV-20261114-002', 'Budi S.', 'Cahaya Pustaka +1 toko', 'Rp 342.000', 'QRIS', 'Menunggu', '14 Nov, 10:03'],
        ['INV-20261113-018', 'Dewi K.', 'Nusa Aksara', 'Rp 247.000', 'Tunai', 'Sukses', '13 Nov, 16:41'],
        ['INV-20261113-011', 'Agus P.', 'Toko Harapan', 'Rp 65.000', 'QRIS', 'Gagal', '13 Nov, 13:20'],
        ['INV-20261112-007', 'Maya L.', 'Cahaya Pustaka', 'Rp 158.000', 'QRIS', 'Sukses', '12 Nov, 19:55'],
    ];
    $warnaStatus = [
        'Sukses' => 'bg-green-100 text-green-900',
        'Menunggu' => 'bg-amber-100 text-amber-900',
        'Gagal' => 'bg-red-100 text-red-900',
    ];
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['title' => 'Ringkasan platform','subtitle' => 'Pantau toko mitra, komisi, dan transaksi lintas penjual']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Ringkasan platform','subtitle' => 'Pantau toko mitra, komisi, dan transaksi lintas penjual']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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

    <div class="grid gap-4 xl:grid-cols-[1fr_400px]">
        <!-- Grafik -->
        <section class="rounded-xl border border-[#E4DED0] bg-white px-5 py-4">
            <h2 class="font-serif text-lg font-semibold">Nilai transaksi 7 hari terakhir</h2>
            <div class="mt-6 flex h-48 items-end justify-between gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $grafik; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$hari, $tinggi]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <div class="w-full max-w-[44px] rounded <?php echo e($hari === 'Jum' ? 'bg-hijau' : 'bg-[#7FA8A1]'); ?>" style="height: <?php echo e($tinggi * 1.8); ?>px"></div>
                        <span class="text-xs text-gray-600"><?php echo e($hari); ?></span>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        <!-- Pendaftaran tenant -->
        <section class="rounded-xl border border-[#E4DED0] bg-white self-start">
            <h2 class="font-serif text-lg font-semibold px-5 py-4">Pendaftaran toko baru</h2>
            <ul>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tenantBaru; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$nama, $slug]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="flex items-center gap-3 px-5 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold"><?php echo e($nama); ?></p>
                            <p class="truncate text-xs text-gray-600"><?php echo e($slug); ?>.tokobuku.com</p>
                        </div>
                        <button type="button" class="rounded-md bg-hijau px-3 py-1.5 text-xs font-semibold text-white">Setujui</button>
                        <button type="button" class="rounded-md border border-[#A9B5B2] px-3 py-1.5 text-xs font-semibold">Tolak</button>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
            <div class="flex items-center justify-between border-t border-[#E4DED0] px-5 py-3.5 text-sm">
                <span class="text-gray-600">Komisi platform default</span>
                <span class="font-semibold">7,5%</span>
            </div>
        </section>
    </div>

    <!-- Audit transaksi -->
    <section class="rounded-xl border border-[#E4DED0] bg-white">
        <div class="flex items-center justify-between px-5 py-4">
            <h2 class="font-serif text-lg font-semibold">Audit transaksi terbaru</h2>
            <a href="#" class="text-sm font-semibold text-hijau hover:underline">Lihat semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-[#F1EDE3] text-left text-xs font-semibold text-gray-600">
                    <tr>
                        <th class="px-5 py-2.5">No. nota</th>
                        <th class="px-3 py-2.5">Pembeli</th>
                        <th class="px-3 py-2.5">Toko</th>
                        <th class="px-3 py-2.5">Total</th>
                        <th class="px-3 py-2.5">Metode</th>
                        <th class="px-3 py-2.5">Status</th>
                        <th class="px-5 py-2.5">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4DED0]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $transaksi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$nota, $pembeli, $toko, $total, $metode, $status, $waktu]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td class="px-5 py-3.5"><?php echo e($nota); ?></td>
                            <td class="px-3 py-3.5"><?php echo e($pembeli); ?></td>
                            <td class="px-3 py-3.5"><?php echo e($toko); ?></td>
                            <td class="px-3 py-3.5"><?php echo e($total); ?></td>
                            <td class="px-3 py-3.5"><?php echo e($metode); ?></td>
                            <td class="px-3 py-3.5"><span class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($warnaStatus[$status]); ?>"><?php echo e($status); ?></span></td>
                            <td class="px-5 py-3.5"><?php echo e($waktu); ?></td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
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
<?php /**PATH C:\ServBay\www\servbay\TOKO-BUKU\resources\views\dashboard\admin.blade.php ENDPATH**/ ?>