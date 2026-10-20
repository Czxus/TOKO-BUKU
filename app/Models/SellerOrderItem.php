<?php

namespace App\Models;

use Database\Factories\SellerOrderItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerOrderItem extends Model
{
    /** @use HasFactory<SellerOrderItemFactory> */
    use HasFactory;

    protected $fillable = ['judul_snapshot', 'jumlah', 'harga_satuan'];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_satuan' => 'integer',
        ];
    }

    public function lineTotal(): int
    {
        return $this->jumlah * $this->harga_satuan;
    }

    /** @return BelongsTo<SellerOrder, $this> */
    public function sellerOrder(): BelongsTo
    {
        return $this->belongsTo(SellerOrder::class);
    }

    /** @return BelongsTo<Book, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
