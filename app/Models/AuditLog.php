<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    // Sesuai kolom tabel audit_logs (migrasi 000105). Tidak ada canteen_id / request_id.
    protected $fillable = [
        'actor_id', 'tenant_id', 'entity', 'entity_id',
        'action', 'before', 'after', 'metadata', 'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
            'metadata' => 'array',
            'logged_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
