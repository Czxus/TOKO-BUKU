<?php

namespace App\Modules\Admin\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

final class AuditLogger
{
    public function record(string $entity, ?int $entityId, string $action, ?array $before = null, ?array $after = null, ?int $tenantId = null, ?int $canteenId = null): AuditLog
    {
        return AuditLog::create([
            'actor_id' => Auth::id(),
            'canteen_id' => $canteenId,
            'tenant_id' => $tenantId,
            'entity' => $entity,
            'entity_id' => $entityId !== null ? (string) $entityId : null,
            'action' => $action,
            'request_id' => request()->header('X-Request-Id') ?? (string) Str::uuid(),
            'before' => $before !== null ? $this->sanitize($before) : null,
            'after' => $after !== null ? $this->sanitize($after) : null,
            'logged_at' => now(),
        ]);
    }

    private function sanitize(array $data): array
    {
        foreach (['account_number_cipher', 'account_number', 'password', 'password_hash'] as $key) {
            unset($data[$key]);
        }
        return $data;
    }
}