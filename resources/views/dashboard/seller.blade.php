@php
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
@endphp

<x-dashboard-layout :title="'Selamat datang, '.$namaToko" subtitle="Ringkasan pesanan dan stok hari ini">
    <x-slot name="actions">
        <a href="#" class="hidden sm:inline-flex h-11 items-center gap-2 rounded-lg bg-hijau px-4 text-sm font-semibold text-white hover:bg-hijau-tua">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Tambah buku
        </a>
    </x-slot>

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
                        @foreach ($pesanan as [$no, $pembeli, $item, $subtotal, $status, $aksi])
                            <tr>
                                <td class="px-5 py-3.5">{{ $no }}</td>
                                <td class="px-3 py-3.5">{{ $pembeli }}</td>
                                <td class="px-3 py-3.5">{{ $item }}</td>
                                <td class="px-3 py-3.5">{{ $subtotal }}</td>
                                <td class="px-3 py-3.5"><span class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold {{ $warnaStatus[$status] }}">{{ $status }}</span></td>
                                <td class="px-5 py-3.5">
                                    <a href="#" class="inline-block rounded-md px-3 py-1.5 text-xs font-semibold {{ $aksi === 'Lihat' ? 'border border-[#A9B5B2] text-tinta' : 'bg-hijau text-white' }}">{{ $aksi }}</a>
                                </td>
                            </tr>
                        @endforeach
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
                @foreach ($stok as [$judul, $isbn, $sisa])
                    <li class="flex items-center gap-3 px-5 py-3">
                        <div class="h-12 w-9 rounded bg-[#D5E3DF]" aria-hidden="true"></div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">{{ $judul }}</p>
                            <p class="text-xs text-gray-600">ISBN {{ $isbn }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $sisa < 5 ? 'bg-amber-100 text-amber-900' : 'bg-stone-200 text-stone-800' }}">Sisa {{ $sisa }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-dashboard-layout>
