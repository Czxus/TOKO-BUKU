<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
   public function handle(Request $request, Closure $next): Response
{
    $tenantParam = $request->route('tenant');

    $tenant = $tenantParam instanceof Tenant
        ? $tenantParam
        : Tenant::where('domain_slug', (string) $tenantParam)->firstOrFail();

    if (! $tenant->isActive()) {
        abort(403, 'Toko buku ini sedang tidak aktif.');
    }

    // Seller hanya boleh membuka tokonya sendiri (FR-TEN-01)
    $user = $request->user();
    if ($user->role !== 'admin_platform' && $user->tenant_id !== $tenant->id) {
        abort(403, 'Anda tidak memiliki akses ke toko ini.');
    }

    $tenantContext = app(TenantContext::class);
    $tenantContext->set($tenant);

    try {
        return $next($request);
    } finally {
        $tenantContext->clear();
    }
}
}
