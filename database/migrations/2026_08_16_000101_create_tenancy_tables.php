<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Domain A — Tenancy & keuangan tenant (SRS 1.5 ERD: TENANTS, USERS.tenant_id;
 * FR-TEN-01, FR-TEN-04, FR-ADM-01, FR-ADM-02).
 * Uang = unsignedBigInteger (Rupiah). Rate = decimal exact (bukan float).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_toko', 120);
            $table->string('domain_slug', 100)->unique();
            $table->text('alamat_gudang');
            // Kolom tambahan dari FR (bukan dari ERD): ongkir (FR-CUS-03), lokasi (FR-CUS-07),
            // pickup & bayar di tempat (FR-CUS-06, FR-PAY-03).
            $table->string('kontak', 30)->nullable();
            $table->string('district_id', 30)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('pickup_aktif')->default(false);
            $table->boolean('bayar_di_tempat_aktif')->default(false);
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active');
            $table->timestamps(6);

            $table->index('status');
        });

        // users dibuat oleh starter kit; lengkapi kolom tenant_id dari ERD (NULL = pembeli / admin platform).
        // Catatan: pada SQLite, FK di ALTER TABLE tidak diterapkan; MariaDB (target SRS) menerapkannya.
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->nullable()->after('role')
                ->constrained('tenants')->nullOnDelete();
        });

        Schema::create('tenant_balances', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->primary()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('saldo_tersedia')->default(0); // "Saldo Utama" (FR-TEN-04)
            $table->unsignedBigInteger('saldo_tertahan')->default(0);
            $table->timestamps(6);
        });

        Schema::create('tenant_bank_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('bank_code', 20);
            $table->text('nomor_rekening_cipher'); // terenkripsi (cast encrypted)
            $table->string('rekening_last4', 4);
            $table->string('nama_pemilik', 120);
            $table->enum('status', ['unverified', 'verified', 'rejected'])->default('unverified');
            $table->boolean('is_primary')->default(false);
            $table->timestamps(6);

            $table->unique(['tenant_id', 'id']); // target FK komposit withdrawals
        });

        // Komisi effective-dated (FR-ADM-02). tenant_id NULL = komisi default platform.
        Schema::create('commission_schemes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('persen_komisi', 6, 4); // exact, mis. 0.1000 = 10%
            $table->dateTime('berlaku_mulai', 6);
            $table->dateTime('berlaku_sampai', 6)->nullable(); // null = masih berlaku
            $table->timestamps(6);

            $table->index(['tenant_id', 'berlaku_mulai']);
        });

        $this->addCheck('tenant_balances', 'chk_tenant_balance_nonneg', 'saldo_tersedia >= 0 AND saldo_tertahan >= 0');
        $this->addCheck('commission_schemes', 'chk_commission_rate_range', 'persen_komisi >= 0 AND persen_komisi <= 1');
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_schemes');
        Schema::dropIfExists('tenant_bank_accounts');
        Schema::dropIfExists('tenant_balances');

        Schema::table('users', function (Blueprint $table): void {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['tenant_id']);
            }
            $table->dropColumn('tenant_id');
        });

        Schema::dropIfExists('tenants');
    }

    /** CHECK constraint hanya dipasang di MariaDB/MySQL (SQLite tidak mendukung ALTER ADD CONSTRAINT). */
    private function addCheck(string $table, string $name, string $expression): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$expression})");
        }
    }
};
