<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
<<<<<<< HEAD
    public function handle(Request $request, Closure $next): Response
    {
        $param = $request->route('tenant');

        // (1) Cari tenant dari parameter route (model hasil binding, atau string domain_slug)
        if ($param instanceof Tenant) {
            $tenant = $param;
        } elseif (is_string($param)) {
            $tenant = Tenant::where('domain_slug', $param)->first();
        } else {
            $tenant = null;
        }

        abort_if($tenant === null, 404, 'Tenant tidak ditemukan.');

        // (2) Toko harus aktif
        abort_unless($tenant->isActive(), 403, 'Toko buku ini sedang tidak aktif.');

        // (3) User harus seller dari toko ini (mencegah seller A membuka toko B)
        $user = $request->user();
        abort_unless(
            $user !== null && $user->isSellerAdmin() && (int) $user->tenant_id === $tenant->id,
            403,
            'Anda tidak memiliki akses ke toko ini.'
        );

        // (4) Isi TenantContext, lalu pastikan dikosongkan setelah request selesai
        $context = app(TenantContext::class);
        $context->set($tenant);

        try {
            return $next($request);
        } finally {
            $context->clear();
        }
    }
=======
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
>>>>>>> 71c104605f2a013ea8ee69d3eff2f98de517e881
}
