<x-dashboard-layout :title="'Halo, '.auth()->user()->name" subtitle="Selamat datang di Toko Buku">
    <section class="rounded-xl border border-[#E4DED0] bg-white px-6 py-8 max-w-2xl space-y-3">
        <h2 class="font-serif text-xl font-semibold">Akun pembeli Anda sudah aktif</h2>
        <p class="text-sm text-gray-600 leading-relaxed">
            Katalog buku lintas toko, keranjang, dan riwayat pesanan akan tersedia di sini.
            Untuk sementara, halaman ini menandakan Anda berhasil masuk.
        </p>
    </section>
</x-dashboard-layout>
