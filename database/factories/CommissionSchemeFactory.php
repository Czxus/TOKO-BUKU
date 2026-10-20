<?php

namespace Database\Factories;

use App\Models\CommissionScheme;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommissionScheme>
 */
class CommissionSchemeFactory extends Factory
{
    public function definition(): array
    {
        // tenant_id NULL = komisi default platform.
        return [
            'tenant_id' => null,
            'persen_komisi' => 0.1000,
            'berlaku_mulai' => now()->subMonth(),
            'berlaku_sampai' => null,
        ];
    }

    public function untukTenant(): static
    {
        return $this->state(['tenant_id' => Tenant::factory()]);
    }
}
