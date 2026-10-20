<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    protected $fillable = [
        'nama_toko', 'domain_slug', 'alamat_gudang', 'kontak', 'district_id',
        'latitude', 'longitude', 'pickup_aktif', 'bayar_di_tempat_aktif', 'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'pickup_aktif' => 'boolean',
            'bayar_di_tempat_aktif' => 'boolean',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return HasMany<Book, $this> */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /** @return HasMany<SellerOrder, $this> */
    public function sellerOrders(): HasMany
    {
        return $this->hasMany(SellerOrder::class);
    }

    /** @return HasOne<TenantBalance, $this> */
    public function balance(): HasOne
    {
        return $this->hasOne(TenantBalance::class);
    }

    /** @return HasMany<TenantBankAccount, $this> */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(TenantBankAccount::class);
    }
}
