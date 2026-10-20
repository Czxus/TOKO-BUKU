<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    // tenant_id & category_id di-set eksplisit oleh aturan domain, bukan dari input pengguna.
    protected $fillable = [
        'judul', 'penulis', 'penerbit', 'isbn', 'tahun_terbit', 'format_buku', 'harga',
        'stok', 'berat_gram', 'deskripsi', 'foto_sampul', 'file_preview_pdf',
        'file_ebook_full', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tahun_terbit' => 'integer',
            'harga' => 'integer',
            'stok' => 'integer',
            'berat_gram' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function isPhysical(): bool
    {
        return in_array($this->format_buku, ['fisik', 'keduanya'], true);
    }

    public function isEbook(): bool
    {
        return in_array($this->format_buku, ['ebook', 'keduanya'], true);
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
