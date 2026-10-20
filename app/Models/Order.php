<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pesanan induk — platform-scoped (TANPA tenant_id): satu checkout bisa lintas toko.
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    // Nomor nota, status, dan total dihitung server (bukan dari input pelanggan).
    protected $fillable = ['alamat_pengiriman', 'kecamatan', 'kode_pos'];

    protected function casts(): array
    {
        return [
            'biaya_layanan' => 'integer',
            'total_tagihan' => 'integer',
            'batas_pembayaran' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<SellerOrder, $this> */
    public function sellerOrders(): HasMany
    {
        return $this->hasMany(SellerOrder::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
