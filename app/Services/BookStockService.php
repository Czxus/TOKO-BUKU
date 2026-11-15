<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Scopes\TenantScope;
use App\Support\Tenancy\TenantContext;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Perubahan stok atomik + teraudit: kunci baris buku, catat movement idempoten
 * (unique idempotency_key), ubah stok, tulis audit — dalam satu transaksi.
 */
final class BookStockService
{
    public function __construct(private AuditLogger $audit) {}

    public function adjust(Book $book, int $delta, string $type, string $idempotencyKey, ?string $reason = null): Book
    {
        return DB::transaction(function () use ($book, $delta, $type, $idempotencyKey, $reason): Book {
            $locked = $this->lock($book);

            // Idempoten: kunci yang sama tidak diproses dua kali.
            if (DB::table('book_stock_movements')->where('idempotency_key', $idempotencyKey)->exists()) {
                return $locked;
            }

            $newStock = $locked->stok + $delta;
            if ($newStock < 0) {
                throw new DomainException('Stok tidak boleh kurang dari 0.');
            }

            DB::table('book_stock_movements')->insert([
                'tenant_id' => $locked->tenant_id,
                'book_id' => $locked->id,
                'idempotency_key' => $idempotencyKey,
                'type' => $type,
                'quantity_delta' => $delta,
                'reason' => $reason,
                'actor_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $before = $locked->stok;
            $locked->forceFill(['stok' => $newStock])->save();

            $this->audit->record('book', $locked->id, 'stock_'.$type,
                ['stok' => $before],
                ['stok' => $newStock, 'delta' => $delta, 'reason' => $reason],
                $locked->tenant_id);

            return $locked;
        });
    }

    /** UC-14: tandai habis dengan satu aksi (stok -> 0, tercatat sebagai movement). */
    public function markOutOfStock(Book $book): Book
    {
        return DB::transaction(function () use ($book): Book {
            $locked = $this->lock($book);

            if ($locked->stok === 0) {
                return $locked;
            }

            return $this->adjust($locked, -$locked->stok, 'mark_out', (string) Str::uuid(), 'tandai habis');
        });
    }

    /** Tampilkan / sembunyikan buku dari katalog publik (is_active). */
    public function toggleActive(Book $book): Book
    {
        return DB::transaction(function () use ($book): Book {
            $locked = $this->lock($book);
            $before = $locked->is_active;
            $locked->forceFill(['is_active' => ! $before])->save();

            $this->audit->record('book', $locked->id, 'toggle_active',
                ['is_active' => $before], ['is_active' => $locked->is_active], $locked->tenant_id);

            return $locked;
        });
    }

    private function lock(Book $book): Book
    {
        $locked = Book::query()->withoutGlobalScope(TenantScope::class)->lockForUpdate()->findOrFail($book->id);

        // Pertahanan berlapis: kalau ada tenant aktif di context, buku harus miliknya.
        $context = app(TenantContext::class);
        abort_if($context->has() && $locked->tenant_id !== $context->id(), 403);

        return $locked;
    }
}
