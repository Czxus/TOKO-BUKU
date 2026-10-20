<?php

namespace App\Models\Scopes;

use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantContext = app(TenantContext::class);

        // Hanya terapkan filter jika TenantContext sudah diisi
        if ($tenantContext->has()) {
            $builder->where(
                $model->qualifyColumn('tenant_id'),
                $tenantContext->id(),
            );
        }
    }
}