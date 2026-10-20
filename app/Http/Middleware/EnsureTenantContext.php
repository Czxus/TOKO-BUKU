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
        $tenantContext = app(TenantContext::class);

        // (1) Cari tenant dari parameter route / slug
        $tenantParam = $request->route('tenant');

        if ($tenantParam instanceof Tenant) {
            $tenant = $tenantParam;
        } elseif (is_string($tenantParam)) {
            $tenant = Tenant::where('slug', $tenantParam)->firstOrFail();
        } else {
            abort(404, 'Tenant tidak ditemukan.');
        }

        // (2) Verifikasi status tenant aktif (opsional jika kolom is_active tersedia)
        if (isset($tenant->is_active) && ! $tenant->is_active) {
            abort(403, 'Toko buku ini sedang tidak aktif.');
        }

        // (3) Isi TenantContext
        $tenantContext->set($tenant);

        try {
            // Lanjutkan request
            return $next($request);
        } finally {
            // (4) Kosongkan/reset context di blok finally agar tidak bocor ke request berikutnya
            $tenantContext->clear();
        }
    }
}