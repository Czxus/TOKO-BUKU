<?php

namespace App\Modules\Admin\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class AssignTenantRole
{
    public function assign(Tenant $tenant, User $user, string $role): void
    {
        DB::table('user_tenant_roles')->updateOrInsert(
            ['tenant_id' => $tenant->id, 'user_id' => $user->id],
            ['role' => $role, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}