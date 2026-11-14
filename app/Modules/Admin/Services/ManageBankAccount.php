<?php

namespace App\Modules\Admin\Services;

use App\Models\Tenant;
use App\Models\TenantBankAccount;

final class ManageBankAccount
{
    /**
     * @param array<string, mixed> $data
     */
    public function store(Tenant $tenant, array $data): TenantBankAccount
    {
        return TenantBankAccount::create([
            'tenant_id' => $tenant->id,
            'bank_name' => $data['bank_name'] ?? null,
            'account_number' => $data['account_number'],
            'account_holder' => $data['account_holder'] ?? null,
        ]);
    }
}