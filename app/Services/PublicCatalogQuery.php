<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Scopes\TenantScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PublicCatalogQuery
{
    /**
     * Mengambil katalog buku publik dengan melepaskan TenantScope, 
     * tetapi menggantinya dengan filter status aktif yang eksplisit.
     */
    public function getPublicBooks(int $perPage = 12): LengthAwarePaginator
    {
        return Book::query()
            ->withoutGlobalScope(TenantScope::class)
            ->whereHas('tenant', function ($query) {
                // Hanya tampilkan buku jika tokonya aktif
                $query->where('is_active', true);
            })
            ->latest()
            ->paginate($perPage);
    }
}