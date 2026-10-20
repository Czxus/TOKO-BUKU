<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    // Status & referensi gateway hanya diubah oleh alur verifikasi webhook (Modul 11).
    protected $fillable = ['payment_method', 'jumlah_bayar'];

    protected function casts(): array
    {
        return [
            'jumlah_bayar' => 'integer',
            'expires_at' => 'datetime',
            'payment_time' => 'datetime',
        ];
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
