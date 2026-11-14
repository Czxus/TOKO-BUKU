@php
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
@endphp

<x-dashboard-layout title="Ringkasan platform" subtitle="Pantau toko mitra, komisi, dan transaksi lintas penjual">
    <!-- Kartu ringkasan -->
    <section class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($ringkasan as [$label, $nilai, $ket])
            <div class="rounded-xl border border-[#E4DED0] bg-white px-5 py-4">
                <p class="text-sm font-medium text-gray-600">{{ $label }}</p>
                <p class="mt-2 font-serif text-2xl xl:text-3xl font-semibold">{{ $nilai }}</p>
                <p class="mt-1 text-xs text-gray-600">{{ $ket }}</p>
            </div>
        @endforeach
    </section>

    <div class="grid gap-4 xl:grid-cols-[1fr_400px]">
        <!-- Grafik -->
        <section class="rounded-xl border border-[#E4DED0] bg-white px-5 py-4">
            <h2 class="font-serif text-lg font-semibold">Nilai transaksi 7 hari terakhir</h2>
            <div class="mt-6 flex h-48 items-end justify-between gap-3">
                @foreach ($grafik as [$hari, $tinggi])
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <div class="w-full max-w-[44px] rounded {{ $hari === 'Jum' ? 'bg-hijau' : 'bg-[#7FA8A1]' }}" style="height: {{ $tinggi * 1.8 }}px"></div>
                        <span class="text-xs text-gray-600">{{ $hari }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Pendaftaran tenant -->
        <section class="rounded-xl border border-[#E4DED0] bg-white self-start">
            <h2 class="font-serif text-lg font-semibold px-5 py-4">Pendaftaran toko baru</h2>
            <ul>
                @foreach ($tenantBaru as [$nama, $slug])
                    <li class="flex items-center gap-3 px-5 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">{{ $nama }}</p>
                            <p class="truncate text-xs text-gray-600">{{ $slug }}.tokobuku.com</p>
                        </div>
                        <button type="button" class="rounded-md bg-hijau px-3 py-1.5 text-xs font-semibold text-white">Setujui</button>
                        <button type="button" class="rounded-md border border-[#A9B5B2] px-3 py-1.5 text-xs font-semibold">Tolak</button>
                    </li>
                @endforeach
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
                    @foreach ($transaksi as [$nota, $pembeli, $toko, $total, $metode, $status, $waktu])
                        <tr>
                            <td class="px-5 py-3.5">{{ $nota }}</td>
                            <td class="px-3 py-3.5">{{ $pembeli }}</td>
                            <td class="px-3 py-3.5">{{ $toko }}</td>
                            <td class="px-3 py-3.5">{{ $total }}</td>
                            <td class="px-3 py-3.5">{{ $metode }}</td>
                            <td class="px-3 py-3.5"><span class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold {{ $warnaStatus[$status] }}">{{ $status }}</span></td>
                            <td class="px-5 py-3.5">{{ $waktu }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-dashboard-layout>
