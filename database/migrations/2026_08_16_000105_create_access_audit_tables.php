<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domain E — Akses e-book & audit (FR-CUS-05, FR-PAY-02 audit keamanan, FR-ADM-02/04 pencatatan).
 * ebook_accesses: akses e-book dibatasi kepemilikan transaksi yang sudah dibayar.
 * audit_logs: append-only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ebook_accesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->dateTime('akses_diberikan_pada', 6);
            $table->timestamps(6);

            $table->unique(['user_id', 'book_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entity', 80);
            $table->string('entity_id', 64)->nullable();
            $table->string('action', 60);
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->json('metadata')->nullable();
            $table->dateTime('logged_at', 6);
            $table->timestamps(6);

            $table->index(['entity', 'entity_id']);
            $table->index(['tenant_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('ebook_accesses');
    }
};
