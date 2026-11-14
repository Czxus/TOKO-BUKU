<?php

namespace App\Services;

use App\Models\CommissionScheme;
use Carbon\Carbon;

class CommissionService
{
    /**
     * Hitung komisi platform dan pendapatan bersih tenant berdasarkan subtotal.
     * 
     * @param int|null $tenantId
     * @param int $subtotalBuku
     * @return array
     */
    public function calculate(?int $tenantId, int $subtotalBuku): array
    {
        $now = Carbon::now();

        // 1. Cari skema komisi khusus tenant yang sedang aktif
        $scheme = CommissionScheme::where('tenant_id', $tenantId)
            ->where('berlaku_mulai', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('berlaku_sampai')
                      ->orWhere('berlaku_sampai', '>=', $now);
            })
            ->first();

        // 2. Jika tidak ada skema khusus tenant, ambil skema global (tenant_id = null)
        if (!$scheme) {
            $scheme = CommissionScheme::whereNull('tenant_id')
                ->where('berlaku_mulai', '<=', $now)
                ->where(function ($query) use ($now) {
                    $query->whereNull('berlaku_sampai')
                          ->orWhere('berlaku_sampai', '>=', $now);
                })
                ->first();
        }

        // Tentukan rate komisi (default ke 10% / 0.10 jika tidak ada skema sama sekali)
        $rate = $scheme ? (float) $scheme->persen_komisi : 0.1000;

        // Hitung nominal komisi platform dan pendapatan tenant
        $komisiPlatform = (int) round($subtotalBuku * $rate);
        $pendapatanTenant = $subtotalBuku - $komisiPlatform;

        return [
            'commission_scheme_id' => $scheme ? $scheme->id : null,
            'komisi_rate_snapshot' => $rate,
            'komisi_platform' => $komisiPlatform,
            'pendapatan_tenant' => $pendapatanTenant,
        ];
    }
}