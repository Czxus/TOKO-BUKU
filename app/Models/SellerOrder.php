<?php

namespace App\Models;

use Database\Factories\SellerOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Sub-pesanan per toko; menyimpan snapshot komisi (historis, tidak berubah). */
class SellerOrder extends Model
{
    /** @use HasFactory<SellerOrderFactory> */
    use HasFactory;

    protected $fillable = ['kurir', 'nomor_resi'];

    protected function casts(): array
    {
        return [
            'subtotal_buku' => 'integer',
            'ongkos_kirim' => 'integer',
            'komisi_rate_snapshot' => 'decimal:4',
            'komisi_platform' => 'integer',
            'pendapatan_tenant' => 'integer',
        ];
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<CommissionScheme, $this> */
    public function commissionScheme(): BelongsTo
    {
        return $this->belongsTo(CommissionScheme::class);
    }

    /** @return HasMany<SellerOrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(SellerOrderItem::class);
    }
}
