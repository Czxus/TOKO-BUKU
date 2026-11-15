<?php

namespace App\Modules\Ordering\Exceptions;

use RuntimeException;

/**
 * Kesalahan domain keranjang. Pesan aman untuk ditampilkan ke pembeli dan tidak
 * membocorkan detail internal (mis. keberadaan buku milik toko nonaktif).
 */
final class CartException extends RuntimeException
{
    /** Buku tidak ada / nonaktif / dihapus / tokonya tidak aktif. */
    public static function bookNotOrderable(): self
    {
        return new self('Buku ini sudah tidak tersedia.');
    }

    /** UC-03 alur 2a: stok buku fisik habis saat penambahan. */
    public static function soldOut(): self
    {
        return new self('Stok buku ini habis.');
    }

    public static function invalidQuantity(): self
    {
        return new self('Jumlah tidak valid.');
    }

    public static function quantityLimit(): self
    {
        return new self('Jumlah di keranjang sudah mencapai batas yang tersedia.');
    }

    public static function tooManyLines(): self
    {
        return new self('Keranjang penuh. Selesaikan pesanan ini terlebih dahulu.');
    }

    /** Lock mutasi tidak didapat dalam batas tunggu (mis. dua tab bersamaan). */
    public static function busy(): self
    {
        return new self('Keranjang sedang diperbarui dari tab lain. Silakan coba lagi.');
    }

    /** Redis/cache store tidak dapat dijangkau; aman dicoba ulang. */
    public static function unavailable(): self
    {
        return new self('Keranjang sementara tidak dapat diakses. Silakan coba lagi.');
    }
}
