<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'role' => 'pembeli',
            'tenant_id' => null,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Admin pengelola platform (tanpa tenant).
     */
    public function adminPlatform(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin_platform',
            'tenant_id' => null,
        ]);
    }

    /**
     * Admin toko/penerbit yang terikat pada satu tenant.
     */
    public function sellerAdmin(?Tenant $tenant = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'seller_admin',
            'tenant_id' => $tenant?->id ?? Tenant::factory(),
        ]);
    }
}
