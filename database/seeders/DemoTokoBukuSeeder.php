<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\CommissionScheme;
use App\Models\Tenant;
use App\Models\TenantBalance;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * Data demo deterministik & idempoten: 4 kategori, 2 tenant (toko/penerbit), komisi default,
 * 6 buku (fisik + e-book), 1 admin platform, 2 seller admin, 1 pembeli.
 * Idempoten: kunci bisnis dicari dulu (firstOrNew), lalu semua kolom di-forceFill karena
 * kolom guarded (tenant_id, category_id, ...) tidak ikut terisi lewat fill() biasa.
 */
class DemoTokoBukuSeeder extends Seeder
{
    public function run(): void
    {
        // Kategori (platform-scoped).
        $kategori = [];
        foreach (['Fiksi', 'Teknologi', 'Pendidikan', 'Komik'] as $nama) {
            $kategori[$nama] = $this->upsert(Category::class, ['nama_kategori' => $nama]);
        }

        // Komisi default platform (tenant_id NULL), berlaku sejak awal 2026.
        $this->upsert(CommissionScheme::class, [
            'tenant_id' => null,
            'berlaku_mulai' => '2026-01-01 00:00:00',
        ], [
            'persen_komisi' => 0.1000,
            'berlaku_sampai' => null,
        ]);

        $blueprint = [
            'toko-harapan' => [
                'nama_toko' => 'Toko Buku Harapan',
                'alamat_gudang' => 'Jl. Gajah Mada No. 10, Banyuwangi, Jawa Timur',
                'kontak' => '081234560001',
                'district_id' => '3510',
                'pickup_aktif' => true,
                'bayar_di_tempat_aktif' => true,
                'seller_email' => 'seller.harapan@tokobuku.test',
                'seller_name' => 'Admin Toko Harapan',
                'books' => [
                    ['9786020000011', 'Dasar Pemrograman Web', 'Teknologi', 'Penulis Contoh A', 'fisik', 85000, 40],
                    ['9786020000028', 'Basis Data untuk Pemula', 'Pendidikan', 'Penulis Contoh B', 'keduanya', 95000, 25],
                    ['9786020000035', 'Kumpulan Cerita Senja', 'Fiksi', 'Penulis Contoh C', 'ebook', 45000, 0],
                ],
            ],
            'penerbit-cahaya' => [
                'nama_toko' => 'Penerbit Cahaya Ilmu',
                'alamat_gudang' => 'Jl. Raya Genteng No. 25, Banyuwangi, Jawa Timur',
                'kontak' => '081234560002',
                'district_id' => '3511',
                'pickup_aktif' => true,
                'bayar_di_tempat_aktif' => false,
                'seller_email' => 'seller.cahaya@tokobuku.test',
                'seller_name' => 'Admin Penerbit Cahaya',
                'books' => [
                    ['9786020000042', 'Petualangan Kota Kecil', 'Komik', 'Penulis Contoh D', 'fisik', 55000, 60],
                    ['9786020000059', 'Jaringan Komputer Praktis', 'Teknologi', 'Penulis Contoh E', 'fisik', 120000, 15],
                    ['9786020000066', 'Matematika Dasar Mandiri', 'Pendidikan', 'Penulis Contoh F', 'ebook', 60000, 0],
                ],
            ],
        ];

        $tenants = [];
        foreach ($blueprint as $slug => $spec) {
            $tenant = $this->upsert(Tenant::class, ['domain_slug' => $slug], [
                'nama_toko' => $spec['nama_toko'],
                'alamat_gudang' => $spec['alamat_gudang'],
                'kontak' => $spec['kontak'],
                'district_id' => $spec['district_id'],
                'pickup_aktif' => $spec['pickup_aktif'],
                'bayar_di_tempat_aktif' => $spec['bayar_di_tempat_aktif'],
                'status' => 'active',
            ]);
            $tenants[$slug] = $tenant;

            $this->upsert(TenantBalance::class, ['tenant_id' => $tenant->id], [
                'saldo_tersedia' => 0,
                'saldo_tertahan' => 0,
            ]);

            foreach ($spec['books'] as [$isbn, $judul, $namaKategori, $penulis, $format, $harga, $stok]) {
                $isEbook = in_array($format, ['ebook', 'keduanya'], true);
                $this->upsert(Book::class, ['tenant_id' => $tenant->id, 'isbn' => $isbn], [
                    'category_id' => $kategori[$namaKategori]->id,
                    'judul' => $judul,
                    'penulis' => $penulis,
                    'penerbit' => $spec['nama_toko'],
                    'tahun_terbit' => 2025,
                    'format_buku' => $format,
                    'harga' => $harga,
                    'stok' => $stok,
                    'berat_gram' => $format === 'ebook' ? 0 : 350,
                    'deskripsi' => 'Buku demo untuk pengujian katalog.',
                    'file_preview_pdf' => $isEbook ? "previews/{$isbn}.pdf" : null,
                    'file_ebook_full' => $isEbook ? "ebooks/{$isbn}.pdf" : null,
                    'is_active' => true,
                ]);
            }
        }

        // Pengguna demo (password: "password" — hanya untuk development).
        $this->akun('admin@tokobuku.test', 'Admin Platform', 'admin_platform', null);
        foreach ($blueprint as $slug => $spec) {
            $this->akun($spec['seller_email'], $spec['seller_name'], 'seller_admin', $tenants[$slug]->id);
        }
        $this->akun('pembeli@tokobuku.test', 'Pembeli Demo', 'pembeli', null);
    }

    /**
     * Cari berdasarkan kunci bisnis; buat bila belum ada; selalu perbarui nilainya.
     *
     * @param  class-string<Model>  $model
     * @param  array<string, mixed>  $keys
     * @param  array<string, mixed>  $values
     */
    private function upsert(string $model, array $keys, array $values = []): Model
    {
        $record = $model::firstOrNew($keys);
        $record->forceFill([...$keys, ...$values])->save();

        return $record;
    }

    private function akun(string $email, string $name, string $role, ?int $tenantId): void
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'role' => $role,
            'tenant_id' => $tenantId,
            'status' => 'active',
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        if (! $user->exists) {
            $user->password = 'password'; // di-hash otomatis oleh cast 'hashed'
        }

        $user->save();
    }
}
