<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Scopes\TenantScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PublicCatalogQuery
{
    /**
     * Katalog publik (FR-CUS-01): lintas toko, atau satu toko bila $tokoSlug diisi.
     * TenantScope dilepas, diganti filter eksplisit: buku aktif dan toko berstatus active.
     */
    public function getPublicBooks(
        int $perPage = 12,
        ?string $cari = null,
        ?int $kategoriId = null,
        ?string $format = null,
        ?string $tokoSlug = null,
    ): LengthAwarePaginator {
        return $this->dasar()
            ->with(['tenant:id,nama_toko,domain_slug,alamat_gudang', 'category:id,nama_kategori'])
            ->when($tokoSlug, fn (Builder $q) => $q->whereHas(
                'tenant', fn ($t) => $t->where('domain_slug', $tokoSlug)
            ))
            ->when($cari !== null && trim($cari) !== '', function (Builder $q) use ($cari) {
                $cari = trim($cari);
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cari).'%';
                $digit = preg_replace('/\D/', '', $cari);

                $q->where(function (Builder $w) use ($like, $digit) {
                    $w->where('judul', 'like', $like)
                        ->orWhere('penulis', 'like', $like)
                        ->orWhere('penerbit', 'like', $like)
                        ->orWhere('isbn', 'like', $like);
                    if (strlen($digit) >= 5) {
                        $w->orWhere('isbn', 'like', '%'.$digit.'%'); // ISBN diketik dengan tanda hubung
                    }
                });
            })
            ->when($kategoriId, fn (Builder $q) => $q->where('category_id', $kategoriId))
            ->when($format === 'fisik', fn (Builder $q) => $q->whereIn('format_buku', ['fisik', 'keduanya']))
            ->when($format === 'ebook', fn (Builder $q) => $q->whereIn('format_buku', ['ebook', 'keduanya']))
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /** Satu buku publik (untuk tombol keranjang), null bila tidak aktif / toko nonaktif. */
    public function findPublic(int $id): ?Book
    {
        return $this->dasar()->whereKey($id)->first();
    }

    private function dasar(): Builder
    {
        return Book::query()
            ->withoutGlobalScope(TenantScope::class)
            ->where('books.is_active', true)
            ->whereHas('tenant', fn ($t) => $t->where('status', 'active'));
    }
}
