# Data Dictionary — Toko Buku Multi-Tenant (Modul 3)

Diturunkan dari **SRS Toko Buku v1.1 (Bab 1.5 ERD, Bab 2.2 kebutuhan fungsional)**.

Scope: **P** = platform-scoped (tanpa `tenant_id`) · **T** = tenant-owned (wajib `tenant_id`) · **M** = campuran.
Uang = `unsignedBigInteger` (Rupiah, integer). Rate komisi = `decimal(6,4)`. Waktu = `dateTime(6)`.

## Pemetaan ERD SRS → tabel → model

| # | Tabel | Scope | Model | Sumber SRS | Kunci / invariant utama |
|--:|---|:--:|---|---|---|
| 1 | tenants | P | `Tenant` | ERD 1 | UQ `domain_slug`; status active/suspended/inactive |
| 2 | users | P | `User` | ERD 2 | UQ `email`; `role` enum (admin_platform, seller_admin, pembeli); `tenant_id` nullable FK |
| 3 | categories | P | `Category` | ERD 3 | UQ `nama_kategori` |
| 4 | books | T | `Book` | ERD 4 | UQ (`tenant_id`,`id`); UQ (`tenant_id`,`isbn`); `harga`/`stok` unsigned |
| 5 | carts | T | `Cart` | ERD 5 | UQ (`user_id`,`book_id`); **FK komposit** (`tenant_id`,`book_id`) → books |
| 6 | orders | P | `Order` | ERD 6 | **TANPA `tenant_id`**; UQ `nomor_nota`, `checkout_key` |
| 7 | payments | P | `Payment` | ERD 7 | UQ `idempotency_key`, `payment_reference`; `payment_method` qris/cash |
| 8 | seller_orders | T | `SellerOrder` | ERD 8 | UQ (`order_id`,`tenant_id`); UQ (`tenant_id`,`id`); snapshot komisi |
| 9 | seller_order_items | T | `SellerOrderItem` | ERD 9 | **FK komposit** ke seller_orders & books; snapshot harga/judul |
| 10 | tenant_balances | T | `TenantBalance` | FR-PAY-02, FR-TEN-04 | PK = `tenant_id`; saldo tersedia/tertahan ≥ 0 |
| 11 | tenant_bank_accounts | T | `TenantBankAccount` | FR-TEN-04, FR-ADM-04 | nomor rekening terenkripsi + last4; UQ (`tenant_id`,`id`) |
| 12 | commission_schemes | M | `CommissionScheme` | FR-ADM-02 | effective-dated; `tenant_id` NULL = default platform; rate 0–1 |
| 13 | payment_events | P | — | FR-PAY-02 | UQ `provider_event_id` (guard webhook duplikat); **append-only** |
| 14 | withdrawals | T | `Withdrawal` | FR-TEN-04, FR-ADM-04 | UQ `idempotency_key`; UQ `active_tenant_lock` (1 aktif/tenant); FK komposit rekening |
| 15 | ledger_entries | T | — | FR-PAY-02 | UQ `idempotency_key`; **append-only**; delta tersedia/tertahan |
| 16 | ebook_accesses | P | — | FR-CUS-05 | UQ (`user_id`,`book_id`); akses hanya setelah bayar |
| 17 | audit_logs | M | — | FR-PAY-02, FR-ADM-02/04 | **append-only**; before/after/metadata |

Tabel tanpa model (payment_events, ledger_entries, ebook_accesses, audit_logs) bersifat append-only dan
dibuat melalui alur domain pada modul berikutnya; model menyusul saat fiturnya dibangun.

## Invariant yang ditegakkan database (bukan hanya aplikasi)

- **Anti lintas-tenant**: FK komposit `(tenant_id, x_id) → parent(tenant_id, id)` pada `carts`,
  `seller_order_items`, `withdrawals`, dan `ledger_entries` menolak relasi ke resource tenant lain
  (FR-TEN-01: *zero cross-tenant data leak*).
- **`orders` tanpa `tenant_id`**: satu checkout memuat banyak `seller_orders` (multi-vendor, FR-CUS-02/03).
- **Idempotensi**: UQ pada `orders.checkout_key`, `payments.idempotency_key`,
  `payment_events.provider_event_id`, `withdrawals.idempotency_key`, `ledger_entries.idempotency_key`.
- **Snapshot transaksi**: `seller_orders.komisi_rate_snapshot` dan `seller_order_items.harga_satuan`/`judul_snapshot`
  tidak berubah walau komisi atau harga buku diubah kemudian (FR-ADM-02).
- **Uang integer**: tidak ada FLOAT untuk nominal.
- **CHECK (MariaDB)**: saldo ≥ 0, rate komisi 0–1, `jumlah` > 0, nominal pembayaran/withdrawal > 0.

## Deviasi dan keputusan terhadap SRS (harap ditinjau)

1. **`users.name`** dipakai, bukan `nama_pengguna` (ERD), supaya kompatibel dengan auth bawaan Laravel (register/profile).
2. **Uang BIGINT**, bukan `decimal` seperti tertulis di ERD SRS — Rupiah tidak memakai sen, dan modul mewajibkan BIGINT.
3. **`format_buku`** mengikuti ERD (`fisik`, `ebook`, `keduanya`). SRS Bab 1.2 menyebut filter
   Softcover/Hardcover/E-book; bila ingin dipenuhi, tambahkan kolom `jenis_sampul` pada revisi SRS.
4. **`seller_id` vs `tenant_id`**: SRS 1.3.4 menyebut `seller_id`, sedangkan ERD dan seluruh FR memakai `tenant_id`.
   Implementasi memakai `tenant_id`.
5. **`seller_order_items.tenant_id`** ditambahkan (tidak ada di ERD) agar FK komposit dapat menjaga isolasi tenant.
6. **Nilai status diperluas** sesuai FR: `seller_orders.status_pesanan` + `diterima`, `siap_diambil`, `batal`
   (FR-TEN-03, FR-TEN-05); `withdrawals.status` = menunggu/disetujui/ditolak/dicairkan (FR-ADM-04).
7. **Kolom tambahan dari FR**: `tenants` (kontak, district_id, latitude/longitude, pickup_aktif,
   bayar_di_tempat_aktif), `books` (tahun_terbit, berat_gram, deskripsi, foto_sampul, is_active),
   `orders` (checkout_key, biaya_layanan, kecamatan, kode_pos, batas_pembayaran),
   `seller_orders` (metode_pemenuhan, kurir, snapshot komisi).
8. **`payments` 1:N terhadap `orders`** (SRS: "1 : 1 atau 1 : N") agar pembayaran ulang QRIS yang kedaluwarsa dapat dicatat.
9. Pembayaran **VA** disebut di FR-PAY-01, tetapi Bab 1.3.4 membatasi metode ke QRIS dan cash; enum mengikuti 1.3.4.
