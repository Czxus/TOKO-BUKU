<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Keranjang (Modul 8 — Keranjang Multi-Tenant di Redis)
|--------------------------------------------------------------------------
|
| Keranjang adalah state sementara (niat belanja), BUKAN transaksi. Isinya
| hanya book_id + jumlah; harga, stok, dan status toko selalu dibaca ulang
| dari database oleh CartService::view().
|
*/

return [

    /*
    | Cache store tempat keranjang disimpan. Produksi/development: 'redis'
    | (butuh Redis yang sehat). Test memakai 'array' (lihat phpunit.xml) agar
    | tidak membutuhkan Redis. Cache store 'redis' otomatis memakai prefix
    | aplikasi dari config/cache.php sehingga environment tidak bertabrakan.
    */
    'store' => env('CART_STORE', 'redis'),

    /*
    | Masa hidup keranjang (detik). Default 4 jam (>= 30 menit minimum UC-03).
    | Diperpanjang setiap MUTASI (tambah/ubah/hapus), tidak pada pembacaan,
    | supaya polling/bot tidak membuat keranjang abadi.
    */
    'ttl_seconds' => (int) env('CART_TTL_SECONDS', 14400),

    /*
    | Batas keranjang.
    */
    'max_qty_per_line' => 20,
    'max_lines' => 50,

    /*
    | Lock mutasi (read-modify-write) agar dua tab tidak saling menimpa.
    | lock_seconds = masa hidup lock (lease); lock_wait = batas tunggu sebelum
    | request gagal terkontrol ("keranjang sedang diperbarui").
    */
    'lock_seconds' => 5,
    'lock_wait' => 3,

    /*
    | Biaya layanan platform sebagai pecahan 0–1 dari subtotal (mis. 0.02 = 2%).
    | Ini selaras dengan kolom orders.biaya_layanan. Default 0 = tidak ada biaya
    | sampai SRS menetapkan tarifnya. Ongkos kirim BUKAN bagian keranjang: dihitung
    | saat checkout (seller_orders.ongkos_kirim).
    */
    'service_fee_rate' => (float) env('CART_SERVICE_FEE_RATE', 0),

];
