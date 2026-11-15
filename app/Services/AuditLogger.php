<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

final class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @param  array<string, mixed>|null  $metadata
     */
    public function record(
        string $entity,
        ?int $entityId,
        string $action,
        ?array $before = null,
        ?array $after = null,
        ?int $tenantId = null,
        ?array $metadata = null,
    ): AuditLog {
        return AuditLog::create([
            'actor_id' => Auth::id(),
            'tenant_id' => $tenantId,
            'entity' => $entity,
            'entity_id' => $entityId !== null ? (string) $entityId : null,
            'action' => $action,
            'before' => $before !== null ? $this->sanitize($before) : null,
            'after' => $after !== null ? $this->sanitize($after) : null,
            // request_id disimpan di metadata karena tabel tidak punya kolomnya
            'metadata' => array_merge(
                ['request_id' => request()->header('X-Request-Id') ?? (string) Str::uuid()],
                $metadata ?? [],
            ),
            'logged_at' => now(),
        ]);
    }

    /** @param array<string, mixed> $data */
    private function sanitize(array $data): array
    {
        foreach (['nomor_rekening_cipher', 'account_number', 'password', 'password_hash'] as $key) {
            unset($data[$key]);
        }

        return $data;
    }
}
