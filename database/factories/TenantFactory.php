<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        $nama = 'Toko Buku '.fake()->unique()->lastName();

        return [
            'nama_toko' => $nama,
            'domain_slug' => Str::slug($nama).'-'.Str::lower(Str::random(4)),
            'alamat_gudang' => fake()->address(),
            'kontak' => fake()->numerify('08##########'),
            'district_id' => (string) fake()->numberBetween(1000, 9999),
            'pickup_aktif' => true,
            'bayar_di_tempat_aktif' => false,
            'status' => 'active',
        ];
    }

    public function suspended(): static
    {
        return $this->state(['status' => 'suspended']);
    }

    public function bayarDiTempat(): static
    {
        return $this->state(['pickup_aktif' => true, 'bayar_di_tempat_aktif' => true]);
    }
}
