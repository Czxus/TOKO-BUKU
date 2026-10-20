<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Domain B — Katalog & keranjang (SRS 1.5 ERD: CATEGORIES, BOOKS, CARTS;
 * FR-CUS-01, FR-CUS-02, FR-TEN-02).
 * categories = platform-scoped (dipakai semua tenant). books & carts = tenant-owned.
 * Isolasi lintas tenant ditegakkan DB lewat FK komposit (tenant_id, id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_kategori', 100)->unique();
            $table->timestamps(6);
        });

        Schema::create('books', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('judul', 255);
            $table->string('penulis', 150);
            $table->string('penerbit', 150);
            $table->string('isbn', 20);
            $table->unsignedSmallInteger('tahun_terbit')->nullable();   // FR-TEN-02
            $table->enum('format_buku', ['fisik', 'ebook', 'keduanya'])->default('fisik');
            $table->unsignedBigInteger('harga');                        // Rupiah
            $table->unsignedInteger('stok')->default(0);                // stok buku fisik
            $table->unsignedInteger('berat_gram')->default(0);          // FR-TEN-02, dasar ongkir
            $table->text('deskripsi')->nullable();
            $table->string('foto_sampul')->nullable();
            $table->string('file_preview_pdf')->nullable();
            $table->string('file_ebook_full')->nullable();              // hanya diakses setelah bayar
            $table->boolean('is_active')->default(true);                // "menonaktifkan" (FR-TEN-02)
            $table->timestamps(6);

            $table->unique(['tenant_id', 'id']);       // target FK komposit (carts, seller_order_items)
            $table->unique(['tenant_id', 'isbn']);     // ISBN unik per toko; toko lain boleh jual ISBN sama
            $table->index('isbn');                     // pencarian ISBN lintas tenant
            $table->index('judul');
            $table->index(['tenant_id', 'category_id', 'is_active']);
        });

        Schema::create('carts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('book_id');
            $table->unsignedInteger('jumlah')->default(1);
            $table->timestamps(6);

            $table->unique(['user_id', 'book_id']);
            $table->index(['user_id', 'tenant_id']); // grouping keranjang per toko (FR-CUS-02)
            // tenant_id keranjang harus sama dengan tenant pemilik buku.
            $table->foreign(['tenant_id', 'book_id'])
                ->references(['tenant_id', 'id'])->on('books')->cascadeOnDelete();
        });

        $this->addCheck('carts', 'chk_cart_jumlah_positive', 'jumlah > 0');
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
        Schema::dropIfExists('books');
        Schema::dropIfExists('categories');
    }

    private function addCheck(string $table, string $name, string $expression): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$expression})");
        }
    }
};
