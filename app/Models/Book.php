<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'books';

    protected $fillable = [
        'tenant_id',
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
}
