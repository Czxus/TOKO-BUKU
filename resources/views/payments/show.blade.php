<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Instruksi Pembayaran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                
                <!-- Status & Info Pesanan -->
                <div class="mb-6 border-b pb-4">
                    <h3 class="text-lg font-bold mb-2">Detail Transaksi #{{ $payment->order_id }}</h3>
                    <p class="text-sm text-gray-600">Silakan selesaikan pembayaran Anda sebelum batas waktu berikut:</p>
                    <p class="text-md font-semibold text-red-600 mt-1">
                        ⏰ {{ $payment->expires_at ? $payment->expires_at->format('d M Y H:i') : '-' }}
                    </p>
                </div>

                <!-- Total Tagihan -->
                <div class="mb-6 bg-gray-50 p-4 rounded-lg flex justify-between items-center border">
                    <div>
                        <span class="text-sm text-gray-500">Total yang harus dibayar:</span>
                        <h4 class="text-2xl font-extrabold text-blue-600">Rp {{ number_format($payment->jumlah_bayar, 0, ',', '.') }}</h4>
                    </div>
                    <div>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full uppercase">
                            {{ $payment->payment_method }}
                        </span>
                    </div>
                </div>

                <!-- Instruksi Berdasarkan Metode Pembayaran -->
                @if($payment->payment_method === 'qris')
                    <div class="border rounded-lg p-6 text-center bg-white shadow-sm">
                        <h4 class="font-bold text-lg mb-2">Scan QRIS untuk Membayar</h4>
                        <p class="text-sm text-gray-600 mb-4">Gunakan aplikasi e-wallet atau mobile banking apa saja (GoPay, OVO, Dana, BCA Mobile, dll)</p>
                        
                        <div class="inline-block p-4 bg-white border rounded-lg shadow-inner">
                            <!-- Generator QR Code Placeholder -->
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=ORDER-{{ $payment->order_id }}-{{ $payment->jumlah_bayar }}" alt="QRIS Code" class="mx-auto">
                        </div>
                        <p class="text-xs text-gray-500 mt-4">Simpan bukti transaksi jika diperlukan.</p>
                    </div>
                @else
                    <div class="border rounded-lg p-6 bg-white shadow-sm">
                        <h4 class="font-bold text-lg mb-4">Instruksi Transfer Bank</h4>
                        <div class="space-y-3 text-sm text-gray-700">
                            <p>Silakan lakukan transfer manual ke rekening resmi toko kami:</p>
                            <div class="p-3 bg-gray-50 border rounded flex justify-between items-center">
                                <div>
                                    <span class="block text-xs text-gray-500">Bank BCA</span>
                                    <strong class="text-base">1234-5678-9012</strong>
                                    <span class="block text-xs text-gray-600 mt-0.5">a.n. PT Toko Buku Indonesia</span>
                                </div>
                                <button onclick="navigator.clipboard.writeText('123456789012'); alert('Nomor rekening disalin!');" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-xs font-semibold rounded">
                                    Salin
                                </button>
                            </div>
                            <p class="text-xs text-red-500 mt-2">* Pastikan nominal transfer sesuai hingga 3 digit terakhir (jika ada kode unik) agar sistem dapat memverifikasi otomatis.</p>
                        </div>
                    </div>
                @endif

                <!-- Tombol Navigasi / Selesai -->
                <div class="mt-8 flex justify-between items-center">
                    <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:underline">
                        &larr; Kembali ke Dashboard
                    </a>
                    <button onclick="alert('Menunggu konfirmasi pembayaran otomatis...')" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow">
                        Cek Status Pembayaran
                    </button>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>