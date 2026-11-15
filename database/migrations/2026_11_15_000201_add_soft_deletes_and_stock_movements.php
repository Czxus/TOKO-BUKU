<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * UC-13: soft delete buku (riwayat pesanan tetap utuh) dan buku besar mutasi stok (append-only).
 * Catatan: unique (tenant_id, isbn) tetap berlaku untuk buku yang sudah di-soft-delete.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->softDeletes('deleted_at', 6);
        });

        Schema::create('book_stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('book_id');
            $table->string('idempotency_key', 64)->unique();
            $table->string('type', 30);               // initial|restock|adjustment|mark_out|sale
            $table->integer('quantity_delta');
            $table->string('reason', 191)->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(6);

            $table->index(['book_id', 'created_at']);
            // movement harus milik tenant yang sama dengan bukunya
            $table->foreign(['tenant_id', 'book_id'])
                ->references(['tenant_id', 'id'])->on('books')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_stock_movements');

        Schema::table('books', function (Blueprint $table): void {
            $table->dropSoftDeletes('deleted_at');
        });
    }
};
