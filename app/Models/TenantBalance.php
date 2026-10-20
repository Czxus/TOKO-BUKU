<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantBalance extends Model
{
    protected $primaryKey = 'tenant_id';

    public $incrementing = false;

    protected $fillable = ['saldo_tersedia', 'saldo_tertahan'];

    protected function casts(): array
    {
        return ['saldo_tersedia' => 'integer', 'saldo_tertahan' => 'integer'];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
