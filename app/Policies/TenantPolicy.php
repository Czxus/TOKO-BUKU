<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminPlatform();
    }

    public function create(User $user): bool
    {
        return $user->isAdminPlatform();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->isAdminPlatform()
            || ($user->isSellerAdmin() && (int) $user->tenant_id === $tenant->id);
    }
}
