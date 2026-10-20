<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nomor_nota' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'checkout_key' => (string) Str::uuid(),
            'alamat_pengiriman' => fake()->address(),
            'kecamatan' => fake()->city(),
            'kode_pos' => fake()->numerify('#####'),
            'biaya_layanan' => 0,
            'total_tagihan' => 0,
            'status_pembayaran' => 'menunggu',
        ];
    }

    public function dibayar(): static
    {
        return $this->state(['status_pembayaran' => 'dibayar']);
    }
}
