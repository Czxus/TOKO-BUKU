<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Tambahkan import ini

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nomor_nota',
        'checkout_key',
        'alamat_pengiriman',
        'kecamatan',
        'kode_pos',
        'biaya_layanan',
        'total_tagihan',
        'status_pembayaran',
        'batas_pembayaran',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasOne<Payment, $this> */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /** 
     * Relasi ke SellerOrder (Pesanan per Tenant/Toko)
     * @return HasMany<SellerOrder, $this> 
     */
    public function sellerOrders(): HasMany
    {
        return $this->hasMany(SellerOrder::class);
    }
}