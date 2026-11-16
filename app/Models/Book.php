<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    // tenant_id TIDAK mass-assignable: diisi otomatis dari TenantContext (BelongsToTenant).
    // foto_sampul diisi eksplisit oleh CoverStore, bukan dari input.
    protected $fillable = [
        'category_id',
        'judul',
        'penulis',
        'penerbit',
        'isbn',
        'tahun_terbit',
        'format_buku',
        'harga',
        'stok',
        'berat_gram',
        'deskripsi',
        'foto_sampul',
        'file_preview_pdf',
        'file_ebook_full',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function isOutOfStock(): bool
    {
        return $this->format_buku !== 'ebook' && $this->stok <= 0;
    }
}