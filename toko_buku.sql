-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 10 Nov 2026 pada 12.32
-- Versi server: 8.4.11
-- Versi PHP: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `toko_buku`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `actor_id` bigint UNSIGNED DEFAULT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `entity` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `before` json DEFAULT NULL,
  `after` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `logged_at` datetime(6) NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `books`
--

CREATE TABLE `books` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `penulis` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `penerbit` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isbn` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_terbit` smallint UNSIGNED DEFAULT NULL,
  `format_buku` enum('fisik','ebook','keduanya') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fisik',
  `harga` bigint UNSIGNED NOT NULL,
  `stok` int UNSIGNED NOT NULL DEFAULT '0',
  `berat_gram` int UNSIGNED NOT NULL DEFAULT '0',
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `foto_sampul` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_preview_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ebook_full` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `books`
--

INSERT INTO `books` (`id`, `tenant_id`, `category_id`, `judul`, `penulis`, `penerbit`, `isbn`, `tahun_terbit`, `format_buku`, `harga`, `stok`, `berat_gram`, `deskripsi`, `foto_sampul`, `file_preview_pdf`, `file_ebook_full`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'Dasar Pemrograman Web', 'Penulis Contoh A', 'Toko Buku Harapan', '9786020000011', 2025, 'fisik', 85000, 40, 350, 'Buku demo untuk pengujian katalog.', NULL, NULL, NULL, 1, '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000'),
(2, 1, 3, 'Basis Data untuk Pemula', 'Penulis Contoh B', 'Toko Buku Harapan', '9786020000028', 2025, 'keduanya', 95000, 25, 350, 'Buku demo untuk pengujian katalog.', NULL, 'previews/9786020000028.pdf', 'ebooks/9786020000028.pdf', 1, '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000'),
(3, 1, 1, 'Kumpulan Cerita Senja', 'Penulis Contoh C', 'Toko Buku Harapan', '9786020000035', 2025, 'ebook', 45000, 0, 0, 'Buku demo untuk pengujian katalog.', NULL, 'previews/9786020000035.pdf', 'ebooks/9786020000035.pdf', 1, '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000'),
(4, 2, 4, 'Petualangan Kota Kecil', 'Penulis Contoh D', 'Penerbit Cahaya Ilmu', '9786020000042', 2025, 'fisik', 55000, 60, 350, 'Buku demo untuk pengujian katalog.', NULL, NULL, NULL, 1, '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000'),
(5, 2, 2, 'Jaringan Komputer Praktis', 'Penulis Contoh E', 'Penerbit Cahaya Ilmu', '9786020000059', 2025, 'fisik', 120000, 15, 350, 'Buku demo untuk pengujian katalog.', NULL, NULL, NULL, 1, '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000'),
(6, 2, 3, 'Matematika Dasar Mandiri', 'Penulis Contoh F', 'Penerbit Cahaya Ilmu', '9786020000066', 2025, 'ebook', 60000, 0, 0, 'Buku demo untuk pengujian katalog.', NULL, 'previews/9786020000066.pdf', 'ebooks/9786020000066.pdf', 1, '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `carts`
--

CREATE TABLE `carts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `book_id` bigint UNSIGNED NOT NULL,
  `jumlah` int UNSIGNED NOT NULL DEFAULT '1',
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_kategori` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `nama_kategori`, `created_at`, `updated_at`) VALUES
(1, 'Fiksi', '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000'),
(2, 'Teknologi', '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000'),
(3, 'Pendidikan', '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000'),
(4, 'Komik', '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000');

-- --------------------------------------------------------

--
-- Struktur dari tabel `commission_schemes`
--

CREATE TABLE `commission_schemes` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `persen_komisi` decimal(6,4) NOT NULL,
  `berlaku_mulai` datetime(6) NOT NULL,
  `berlaku_sampai` datetime(6) DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ;

--
-- Dumping data untuk tabel `commission_schemes`
--

INSERT INTO `commission_schemes` (`id`, `tenant_id`, `persen_komisi`, `berlaku_mulai`, `berlaku_sampai`, `created_at`, `updated_at`) VALUES
(1, NULL, 0.1000, '2026-01-01 00:00:00.000000', NULL, '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ebook_accesses`
--

CREATE TABLE `ebook_accesses` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `book_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `akses_diberikan_pada` datetime(6) NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ledger_entries`
--

CREATE TABLE `ledger_entries` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `seller_order_id` bigint UNSIGNED DEFAULT NULL,
  `payment_id` bigint UNSIGNED DEFAULT NULL,
  `withdrawal_id` bigint UNSIGNED DEFAULT NULL,
  `idempotency_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('sale_credit','commission_debit','hold','release','withdrawal_debit','reversal') COLLATE utf8mb4_unicode_ci NOT NULL,
  `delta_tersedia` bigint NOT NULL DEFAULT '0',
  `delta_tertahan` bigint NOT NULL DEFAULT '0',
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(12, '0001_01_01_000000_create_users_table', 1),
(13, '0001_01_01_000001_create_cache_table', 1),
(14, '0001_01_01_000002_create_jobs_table', 1),
(15, '2024_01_01_000000_create_passkeys_table', 1),
(16, '2025_08_14_170933_add_two_factor_columns_to_users_table', 1),
(17, '2026_08_16_000001_add_role_and_status_to_users_table', 1),
(18, '2026_08_16_000101_create_tenancy_tables', 1),
(19, '2026_08_16_000102_create_catalog_tables', 1),
(20, '2026_08_16_000103_create_order_tables', 1),
(21, '2026_08_16_000104_create_payment_tables', 1),
(22, '2026_08_16_000105_create_access_audit_tables', 1),
(23, '2026_11_10_072610_create_personal_access_tokens_table', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `nomor_nota` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checkout_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat_pengiriman` text COLLATE utf8mb4_unicode_ci,
  `kecamatan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_pos` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `biaya_layanan` bigint UNSIGNED NOT NULL DEFAULT '0',
  `total_tagihan` bigint UNSIGNED NOT NULL DEFAULT '0',
  `status_pembayaran` enum('menunggu','dibayar','batal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `batas_pembayaran` datetime(6) DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `passkeys`
--

CREATE TABLE `passkeys` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credential_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credential` json NOT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `confirmed_by` bigint UNSIGNED DEFAULT NULL,
  `payment_method` enum('qris','cash') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idempotency_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah_bayar` bigint UNSIGNED NOT NULL,
  `status` enum('pending','success','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `qris_payload` text COLLATE utf8mb4_unicode_ci,
  `expires_at` datetime(6) DEFAULT NULL,
  `payment_time` datetime(6) DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payment_events`
--

CREATE TABLE `payment_events` (
  `id` bigint UNSIGNED NOT NULL,
  `payment_id` bigint UNSIGNED NOT NULL,
  `provider_event_id` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` json NOT NULL,
  `result` enum('verified','rejected','duplicate') COLLATE utf8mb4_unicode_ci NOT NULL,
  `received_at` datetime(6) NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `seller_orders`
--

CREATE TABLE `seller_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `commission_scheme_id` bigint UNSIGNED DEFAULT NULL,
  `metode_pemenuhan` enum('pengiriman','pickup','ebook') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pengiriman',
  `kurir` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtotal_buku` bigint UNSIGNED NOT NULL,
  `ongkos_kirim` bigint UNSIGNED NOT NULL DEFAULT '0',
  `komisi_rate_snapshot` decimal(6,4) NOT NULL DEFAULT '0.0000',
  `komisi_platform` bigint UNSIGNED NOT NULL DEFAULT '0',
  `pendapatan_tenant` bigint UNSIGNED NOT NULL DEFAULT '0',
  `nomor_resi` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_pesanan` enum('pending','diterima','diproses','siap_diambil','dikirim','selesai','batal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `seller_order_items`
--

CREATE TABLE `seller_order_items` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `seller_order_id` bigint UNSIGNED NOT NULL,
  `book_id` bigint UNSIGNED NOT NULL,
  `judul_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` int UNSIGNED NOT NULL,
  `harga_satuan` bigint UNSIGNED NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tenants`
--

CREATE TABLE `tenants` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_toko` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain_slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat_gudang` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `kontak` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district_id` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `pickup_aktif` tinyint(1) NOT NULL DEFAULT '0',
  `bayar_di_tempat_aktif` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('active','suspended','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tenants`
--

INSERT INTO `tenants` (`id`, `nama_toko`, `domain_slug`, `alamat_gudang`, `kontak`, `district_id`, `latitude`, `longitude`, `pickup_aktif`, `bayar_di_tempat_aktif`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Toko Buku Harapan', 'toko-harapan', 'Jl. Gajah Mada No. 10, Banyuwangi, Jawa Timur', '081234560001', '3510', NULL, NULL, 1, 1, 'active', '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000'),
(2, 'Penerbit Cahaya Ilmu', 'penerbit-cahaya', 'Jl. Raya Genteng No. 25, Banyuwangi, Jawa Timur', '081234560002', '3511', NULL, NULL, 1, 0, 'active', '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tenant_balances`
--

CREATE TABLE `tenant_balances` (
  `tenant_id` bigint UNSIGNED NOT NULL,
  `saldo_tersedia` bigint UNSIGNED NOT NULL DEFAULT '0',
  `saldo_tertahan` bigint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ;

--
-- Dumping data untuk tabel `tenant_balances`
--

INSERT INTO `tenant_balances` (`tenant_id`, `saldo_tersedia`, `saldo_tertahan`, `created_at`, `updated_at`) VALUES
(1, 0, 0, '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000'),
(2, 0, 0, '2026-10-17 22:38:07.000000', '2026-10-17 22:38:07.000000');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tenant_bank_accounts`
--

CREATE TABLE `tenant_bank_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `verified_by` bigint UNSIGNED DEFAULT NULL,
  `bank_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_rekening_cipher` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rekening_last4` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_pemilik` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('unverified','verified','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unverified',
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin_platform','seller_admin','pembeli') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pembeli',
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `tenant_id`, `status`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin Platform', 'admin@tokobuku.test', 'admin_platform', NULL, 'active', '2026-10-17 22:38:07', '$2y$12$tOdOFr7qPDfcv4dtXqUX5.xXB.JSHZsEZ9SNeQFljD3stHXoIkVR.', NULL, NULL, NULL, NULL, '2026-10-17 22:38:08', '2026-10-17 22:38:08'),
(2, 'Admin Toko Harapan', 'seller.harapan@tokobuku.test', 'seller_admin', 1, 'active', '2026-10-17 22:38:08', '$2y$12$u3OMGwyLIoxZBJR8e4lBhe.igvwJQa/ZEAVohIERAVEB0tI0x0apW', NULL, NULL, NULL, NULL, '2026-10-17 22:38:08', '2026-10-17 22:38:08'),
(3, 'Admin Penerbit Cahaya', 'seller.cahaya@tokobuku.test', 'seller_admin', 2, 'active', '2026-10-17 22:38:08', '$2y$12$.zT9eVsliJhxbq5u79NmNOtmjIIustV.MldNghkX7bl03AJsCj/8a', NULL, NULL, NULL, NULL, '2026-10-17 22:38:08', '2026-10-17 22:38:08'),
(4, 'Pembeli Demo', 'pembeli@tokobuku.test', 'pembeli', NULL, 'active', '2026-10-17 22:38:08', '$2y$12$xWIzSzYDVKqNNMc0vyi6huDIKie/.ds3cEaD2YbtiXz1hRx2h95sG', NULL, NULL, NULL, NULL, '2026-10-17 22:38:08', '2026-10-17 22:38:08'),
(5, 'jai', 'cokhboskuy@gmail.com', 'pembeli', NULL, 'active', NULL, '$2y$12$.Jm6Yh...igGC0A0G83Fje3..cz21QXZ7xVOVQVWOaMob/OCbHKSK', NULL, NULL, NULL, NULL, '2026-10-27 19:28:59', '2026-10-27 19:28:59'),
(6, 'jai', 'ssgoblok000@gmail.com', 'pembeli', NULL, 'active', NULL, '$2y$12$UEojtKN6HqLiBYkIjjMDY.z1uScMYbIj.8LRPs9VtkZobeVMTja7W', NULL, NULL, NULL, 'cidzrfhemYqQ3gsgmtBfDQQ0dV1V4LDltx16CFwf1SDaOcSZjGnnIGPsWP8n', '2026-11-10 00:23:21', '2026-11-10 00:23:21'),
(7, 'Jainul', 'jainul@example.com', 'pembeli', NULL, 'active', NULL, '$2y$12$EZAebFrMWSNIxC9a97ZcteYK6elYLxdifcDDUOxYV1AP0fiNHpK8q', NULL, NULL, NULL, NULL, '2026-11-10 00:33:58', '2026-11-10 00:33:58'),
(8, 'daffa', 'daffa@gmail.com', 'pembeli', NULL, 'active', NULL, '$2y$12$lmqGC0KcQAzyHmeFFYdgXuMYAyiy1QXLDI5TDq20xFH1J0dYHxDKy', NULL, NULL, NULL, NULL, '2026-11-10 00:41:28', '2026-11-10 00:41:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `bank_account_id` bigint UNSIGNED NOT NULL,
  `diajukan_oleh` bigint UNSIGNED NOT NULL,
  `diverifikasi_oleh` bigint UNSIGNED DEFAULT NULL,
  `idempotency_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` bigint UNSIGNED NOT NULL,
  `status` enum('menunggu','disetujui','ditolak','dicairkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `diverifikasi_pada` datetime(6) DEFAULT NULL,
  `active_tenant_lock` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL
) ;

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_actor_id_foreign` (`actor_id`),
  ADD KEY `audit_logs_entity_entity_id_index` (`entity`,`entity_id`),
  ADD KEY `audit_logs_tenant_id_action_index` (`tenant_id`,`action`);

--
-- Indeks untuk tabel `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `books_tenant_id_id_unique` (`tenant_id`,`id`),
  ADD UNIQUE KEY `books_tenant_id_isbn_unique` (`tenant_id`,`isbn`),
  ADD KEY `books_category_id_foreign` (`category_id`),
  ADD KEY `books_isbn_index` (`isbn`),
  ADD KEY `books_judul_index` (`judul`),
  ADD KEY `books_tenant_id_category_id_is_active_index` (`tenant_id`,`category_id`,`is_active`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `carts_user_id_book_id_unique` (`user_id`,`book_id`),
  ADD KEY `carts_user_id_tenant_id_index` (`user_id`,`tenant_id`),
  ADD KEY `carts_tenant_id_book_id_foreign` (`tenant_id`,`book_id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_nama_kategori_unique` (`nama_kategori`);

--
-- Indeks untuk tabel `commission_schemes`
--
ALTER TABLE `commission_schemes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commission_schemes_tenant_id_berlaku_mulai_index` (`tenant_id`,`berlaku_mulai`);

--
-- Indeks untuk tabel `ebook_accesses`
--
ALTER TABLE `ebook_accesses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ebook_accesses_user_id_book_id_unique` (`user_id`,`book_id`),
  ADD KEY `ebook_accesses_book_id_foreign` (`book_id`),
  ADD KEY `ebook_accesses_order_id_foreign` (`order_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ledger_entries`
--
ALTER TABLE `ledger_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ledger_entries_idempotency_key_unique` (`idempotency_key`),
  ADD KEY `ledger_entries_payment_id_foreign` (`payment_id`),
  ADD KEY `ledger_entries_withdrawal_id_foreign` (`withdrawal_id`),
  ADD KEY `ledger_entries_tenant_id_type_index` (`tenant_id`,`type`),
  ADD KEY `ledger_entries_tenant_id_seller_order_id_foreign` (`tenant_id`,`seller_order_id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_nomor_nota_unique` (`nomor_nota`),
  ADD UNIQUE KEY `orders_checkout_key_unique` (`checkout_key`),
  ADD KEY `orders_user_id_status_pembayaran_index` (`user_id`,`status_pembayaran`);

--
-- Indeks untuk tabel `passkeys`
--
ALTER TABLE `passkeys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `passkeys_credential_id_unique` (`credential_id`),
  ADD KEY `passkeys_user_id_index` (`user_id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_idempotency_key_unique` (`idempotency_key`),
  ADD UNIQUE KEY `payments_payment_reference_unique` (`payment_reference`),
  ADD KEY `payments_confirmed_by_foreign` (`confirmed_by`),
  ADD KEY `payments_order_id_status_index` (`order_id`,`status`);

--
-- Indeks untuk tabel `payment_events`
--
ALTER TABLE `payment_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_events_provider_event_id_unique` (`provider_event_id`),
  ADD KEY `payment_events_payment_id_result_index` (`payment_id`,`result`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indeks untuk tabel `seller_orders`
--
ALTER TABLE `seller_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `seller_orders_order_id_tenant_id_unique` (`order_id`,`tenant_id`),
  ADD UNIQUE KEY `seller_orders_tenant_id_id_unique` (`tenant_id`,`id`),
  ADD KEY `seller_orders_commission_scheme_id_foreign` (`commission_scheme_id`),
  ADD KEY `seller_orders_tenant_id_status_pesanan_index` (`tenant_id`,`status_pesanan`),
  ADD KEY `seller_orders_nomor_resi_index` (`nomor_resi`);

--
-- Indeks untuk tabel `seller_order_items`
--
ALTER TABLE `seller_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_order_items_tenant_id_seller_order_id_index` (`tenant_id`,`seller_order_id`),
  ADD KEY `seller_order_items_tenant_id_book_id_foreign` (`tenant_id`,`book_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenants_domain_slug_unique` (`domain_slug`),
  ADD KEY `tenants_status_index` (`status`);

--
-- Indeks untuk tabel `tenant_balances`
--
ALTER TABLE `tenant_balances`
  ADD PRIMARY KEY (`tenant_id`);

--
-- Indeks untuk tabel `tenant_bank_accounts`
--
ALTER TABLE `tenant_bank_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenant_bank_accounts_tenant_id_id_unique` (`tenant_id`,`id`),
  ADD KEY `tenant_bank_accounts_verified_by_foreign` (`verified_by`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_tenant_id_foreign` (`tenant_id`);

--
-- Indeks untuk tabel `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `withdrawals_idempotency_key_unique` (`idempotency_key`),
  ADD UNIQUE KEY `withdrawals_active_tenant_lock_unique` (`active_tenant_lock`),
  ADD KEY `withdrawals_diajukan_oleh_foreign` (`diajukan_oleh`),
  ADD KEY `withdrawals_diverifikasi_oleh_foreign` (`diverifikasi_oleh`),
  ADD KEY `withdrawals_tenant_id_status_index` (`tenant_id`,`status`),
  ADD KEY `withdrawals_tenant_id_bank_account_id_foreign` (`tenant_id`,`bank_account_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `books`
--
ALTER TABLE `books`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `commission_schemes`
--
ALTER TABLE `commission_schemes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ebook_accesses`
--
ALTER TABLE `ebook_accesses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ledger_entries`
--
ALTER TABLE `ledger_entries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `passkeys`
--
ALTER TABLE `passkeys`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `payment_events`
--
ALTER TABLE `payment_events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `seller_orders`
--
ALTER TABLE `seller_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `seller_order_items`
--
ALTER TABLE `seller_order_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `tenant_bank_accounts`
--
ALTER TABLE `tenant_bank_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audit_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `books_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_tenant_id_book_id_foreign` FOREIGN KEY (`tenant_id`,`book_id`) REFERENCES `books` (`tenant_id`, `id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `commission_schemes`
--
ALTER TABLE `commission_schemes`
  ADD CONSTRAINT `commission_schemes_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ebook_accesses`
--
ALTER TABLE `ebook_accesses`
  ADD CONSTRAINT `ebook_accesses_book_id_foreign` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `ebook_accesses_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ebook_accesses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ledger_entries`
--
ALTER TABLE `ledger_entries`
  ADD CONSTRAINT `ledger_entries_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ledger_entries_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `ledger_entries_tenant_id_seller_order_id_foreign` FOREIGN KEY (`tenant_id`,`seller_order_id`) REFERENCES `seller_orders` (`tenant_id`, `id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `ledger_entries_withdrawal_id_foreign` FOREIGN KEY (`withdrawal_id`) REFERENCES `withdrawals` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `passkeys`
--
ALTER TABLE `passkeys`
  ADD CONSTRAINT `passkeys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_confirmed_by_foreign` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `payment_events`
--
ALTER TABLE `payment_events`
  ADD CONSTRAINT `payment_events_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `seller_orders`
--
ALTER TABLE `seller_orders`
  ADD CONSTRAINT `seller_orders_commission_scheme_id_foreign` FOREIGN KEY (`commission_scheme_id`) REFERENCES `commission_schemes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `seller_orders_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seller_orders_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `seller_order_items`
--
ALTER TABLE `seller_order_items`
  ADD CONSTRAINT `seller_order_items_tenant_id_book_id_foreign` FOREIGN KEY (`tenant_id`,`book_id`) REFERENCES `books` (`tenant_id`, `id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `seller_order_items_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `seller_order_items_tenant_id_seller_order_id_foreign` FOREIGN KEY (`tenant_id`,`seller_order_id`) REFERENCES `seller_orders` (`tenant_id`, `id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tenant_balances`
--
ALTER TABLE `tenant_balances`
  ADD CONSTRAINT `tenant_balances_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tenant_bank_accounts`
--
ALTER TABLE `tenant_bank_accounts`
  ADD CONSTRAINT `tenant_bank_accounts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenant_bank_accounts_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_diajukan_oleh_foreign` FOREIGN KEY (`diajukan_oleh`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `withdrawals_diverifikasi_oleh_foreign` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `withdrawals_tenant_id_bank_account_id_foreign` FOREIGN KEY (`tenant_id`,`bank_account_id`) REFERENCES `tenant_bank_accounts` (`tenant_id`, `id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `withdrawals_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
