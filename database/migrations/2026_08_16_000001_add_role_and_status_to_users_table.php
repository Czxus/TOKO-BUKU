<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Role & status pengguna sesuai SRS Toko Buku (ERD: USERS.role).
 * Role: admin_platform | seller_admin | pembeli.
 * Kolom tenant_id (FK ke tenants) ditambahkan di migrasi 000101 setelah tabel tenants ada.
 * Kolom `name` sengaja dipertahankan (SRS: nama_pengguna) agar kompatibel dengan auth bawaan Laravel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->enum('role', ['admin_platform', 'seller_admin', 'pembeli'])
                ->default('pembeli')
                ->after('email');
            $table->string('status', 20)->default('active')->after('role'); // active|suspended
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['role', 'status']);
        });
    }
};
