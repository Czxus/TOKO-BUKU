<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    use BelongsToTenant, HasFactory;

    // UC-13 alur 3a: buku yang dihapus tetap ada agar riwayat pesanan utuh.
    use SoftDeletes;

    // tenant_id TIDAK mass-assignable: diisi otomatis dari TenantContext (BelongsToTenant).
    // foto_sampul diisi eksplisit oleh CoverStore, bukan dari input.
    protected $fillable = [
        'category_id', 'judul', 'penulis', 'penerbit', 'isbn', 'tahun_terbit',
        'format_buku', 'harga', 'stok', 'berat_gram', 'deskripsi', 'is_active',
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

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Buku fisik yang stoknya 0 dianggap habis. E-book tidak memakai stok. */
    public function isOutOfStock(): bool
    {
        return $this->format_buku === 'fisik' && $this->stok <= 0;
    }

    /**
     * Predikat "sellable": aktif, dan (format punya e-book ATAU stok > 0).
     * Selaras dengan aturan tombol keranjang di halaman katalog.
     *
     * @param  Builder<Book>  $query
     */
    public function scopeSellable(Builder $query): void
    {
        $query->where('is_active', true)->where(function (Builder $w): void {
            $w->whereIn('format_buku', ['ebook', 'keduanya'])->orWhere('stok', '>', 0);
        });
    }

    public function coverUrl(): ?string
    {
        return $this->foto_sampul !== null ? Storage::disk('public')->url($this->foto_sampul) : null;
    }
}
