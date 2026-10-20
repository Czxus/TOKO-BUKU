<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Domain C — Pesanan induk & sub-pesanan per toko (SRS 1.5 ERD: ORDERS, SELLER_ORDERS,
 * SELLER_ORDER_ITEMS; FR-CUS-03, FR-CUS-04, FR-CUS-06, FR-TEN-03, FR-TEN-05).
 * orders = platform-scoped (TANPA tenant_id): satu checkout bisa memuat banyak toko.
 * seller_orders menyimpan tenant_id + snapshot komisi; seller_order_items menyimpan snapshot harga.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('nomor_nota', 30)->unique();      // mis. INV-20260929-001
            $table->string('checkout_key', 100)->unique();   // idempotensi checkout
            $table->text('alamat_pengiriman')->nullable();   // null bila semua item e-book/pickup
            $table->string('kecamatan', 100)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->unsignedBigInteger('biaya_layanan')->default(0);
            $table->unsignedBigInteger('total_tagihan')->default(0);
            $table->enum('status_pembayaran', ['menunggu', 'dibayar', 'batal'])->default('menunggu');
            $table->dateTime('batas_pembayaran', 6)->nullable(); // reservasi stok sementara (FR-CUS-03)
            $table->timestamps(6);

            $table->index(['user_id', 'status_pembayaran']);
        });

        Schema::create('seller_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignId('commission_scheme_id')->nullable()
                ->constrained('commission_schemes')->nullOnDelete();
            // pengiriman = kurir; pickup = ambil di toko; ebook = tidak ada item fisik.
            $table->enum('metode_pemenuhan', ['pengiriman', 'pickup', 'ebook'])->default('pengiriman');
            $table->string('kurir', 50)->nullable();
            $table->unsignedBigInteger('subtotal_buku');
            $table->unsignedBigInteger('ongkos_kirim')->default(0);
            // Snapshot komisi: saldo tenant = subtotal_buku + ongkos_kirim - komisi_platform (FR-PAY-02).
            $table->decimal('komisi_rate_snapshot', 6, 4)->default(0);
            $table->unsignedBigInteger('komisi_platform')->default(0);
            $table->unsignedBigInteger('pendapatan_tenant')->default(0);
            $table->string('nomor_resi', 60)->nullable();
            $table->enum('status_pesanan', [
                'pending', 'diterima', 'diproses', 'siap_diambil', 'dikirim', 'selesai', 'batal',
            ])->default('pending');
            $table->timestamps(6);

            $table->unique(['order_id', 'tenant_id']);  // satu sub-pesanan per toko per order induk
            $table->unique(['tenant_id', 'id']);        // target FK komposit
            $table->index(['tenant_id', 'status_pesanan']);
            $table->index('nomor_resi');
        });

        Schema::create('seller_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('seller_order_id');
            $table->unsignedBigInteger('book_id');
            $table->string('judul_snapshot', 255);
            $table->unsignedInteger('jumlah');
            $table->unsignedBigInteger('harga_satuan'); // snapshot harga saat transaksi
            $table->timestamps(6);

            $table->index(['tenant_id', 'seller_order_id']);
            // Item harus milik tenant yang sama dengan sub-pesanan dan bukunya.
            $table->foreign(['tenant_id', 'seller_order_id'])
                ->references(['tenant_id', 'id'])->on('seller_orders')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'book_id'])
                ->references(['tenant_id', 'id'])->on('books')->restrictOnDelete();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE seller_order_items ADD CONSTRAINT chk_item_jumlah_positive CHECK (jumlah > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_order_items');
        Schema::dropIfExists('seller_orders');
        Schema::dropIfExists('orders');
    }
};
