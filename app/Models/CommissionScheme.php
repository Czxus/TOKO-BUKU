<?php

namespace App\Models;

use Database\Factories\CommissionSchemeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Komisi platform effective-dated. tenant_id NULL = komisi default platform. */
class CommissionScheme extends Model
{
    /** @use HasFactory<CommissionSchemeFactory> */
    use HasFactory;

    protected $fillable = ['persen_komisi', 'berlaku_mulai', 'berlaku_sampai'];

    protected function casts(): array
    {
        return [
            'persen_komisi' => 'decimal:4',
            'berlaku_mulai' => 'datetime',
            'berlaku_sampai' => 'datetime',
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
