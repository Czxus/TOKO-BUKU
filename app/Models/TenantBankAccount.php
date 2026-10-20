<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantBankAccount extends Model
{
    protected $fillable = ['bank_code', 'nomor_rekening_cipher', 'rekening_last4', 'nama_pemilik', 'is_primary'];

    protected $hidden = ['nomor_rekening_cipher'];

    protected function casts(): array
    {
        return [
            'nomor_rekening_cipher' => 'encrypted',
            'is_primary' => 'boolean',
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
