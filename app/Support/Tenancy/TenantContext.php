<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use RuntimeException;

/**
 * Menyimpan satu Tenant aktif selama SATU request/job.
 * Didaftarkan sebagai scoped() binding di AppServiceProvider.
 */
final class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }

    public function has(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * Gagal cepat bila context belum diisi.
     */
    public function tenant(): Tenant
    {
        return $this->tenant ?? throw new RuntimeException('TenantContext belum diisi.');
    }

    public function id(): int
    {
        return $this->tenant()->id;
    }

    public function idOrNull(): ?int
    {
        return $this->tenant?->id;
    }
}