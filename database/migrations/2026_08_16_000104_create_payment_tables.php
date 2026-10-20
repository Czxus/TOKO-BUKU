<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Domain D — Pembayaran & keuangan (SRS 1.5 ERD: PAYMENTS; FR-PAY-01..03, FR-TEN-04, FR-ADM-04).
 * payment_events & ledger_entries bersifat append-only (koreksi via reversal, bukan edit).
 * idempotency_key / provider_event_id unik mencegah proses ganda (double-spend).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete(); // 1:N (percobaan ulang bayar)
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete(); // bayar di tempat (FR-PAY-03)
            $table->enum('payment_method', ['qris', 'cash']);
            $table->string('payment_reference', 100)->nullable()->unique(); // referensi Payment Gateway
            $table->string('idempotency_key', 100)->unique();
            $table->unsignedBigInteger('jumlah_bayar');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->text('qris_payload')->nullable();
            $table->dateTime('expires_at', 6)->nullable();
            $table->dateTime('payment_time', 6)->nullable();
            $table->timestamps(6);

            $table->index(['order_id', 'status']);
        });

        // Append-only: bukti callback Payment Gateway (FR-PAY-02).
        Schema::create('payment_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->string('provider_event_id', 150)->unique(); // guard duplikat / replay webhook
            $table->string('signature', 255)->nullable();
            $table->json('payload');
            $table->enum('result', ['verified', 'rejected', 'duplicate']);
            $table->dateTime('received_at', 6);
            $table->timestamps(6);

            $table->index(['payment_id', 'result']);
        });

        Schema::create('withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('bank_account_id');
            $table->foreignId('diajukan_oleh')->constrained('users')->restrictOnDelete();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key', 100)->unique();
            $table->unsignedBigInteger('jumlah');
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'dicairkan'])->default('menunggu');
            $table->text('catatan_admin')->nullable();
            $table->dateTime('diverifikasi_pada', 6)->nullable();
            // Satu withdrawal aktif per tenant: diisi tenant_id saat aktif, NULL saat final.
            $table->unsignedBigInteger('active_tenant_lock')->nullable()->unique();
            $table->timestamps(6);

            $table->index(['tenant_id', 'status']);
            // Rekening tujuan harus milik tenant yang sama.
            $table->foreign(['tenant_id', 'bank_account_id'])
                ->references(['tenant_id', 'id'])->on('tenant_bank_accounts')->restrictOnDelete();
        });

        // Append-only ledger; saldo diturunkan dari delta tersedia/tertahan.
        Schema::create('ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('seller_order_id')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('withdrawal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('idempotency_key', 100)->unique();
            $table->enum('type', [
                'sale_credit', 'commission_debit', 'hold', 'release', 'withdrawal_debit', 'reversal',
            ]);
            $table->bigInteger('delta_tersedia')->default(0);
            $table->bigInteger('delta_tertahan')->default(0);
            $table->timestamps(6);

            $table->index(['tenant_id', 'type']);
            $table->foreign(['tenant_id', 'seller_order_id'])
                ->references(['tenant_id', 'id'])->on('seller_orders')->restrictOnDelete();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE payments ADD CONSTRAINT chk_payment_amount_positive CHECK (jumlah_bayar > 0)');
            DB::statement('ALTER TABLE withdrawals ADD CONSTRAINT chk_withdrawal_amount_positive CHECK (jumlah > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
    }
};
