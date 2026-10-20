<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_method' => 'qris',
            'payment_reference' => 'PG-'.Str::upper(Str::random(12)),
            'idempotency_key' => (string) Str::uuid(),
            'jumlah_bayar' => 60000,
            'status' => 'pending',
            'expires_at' => now()->addDay(),
        ];
    }

    public function success(): static
    {
        return $this->state(['status' => 'success', 'payment_time' => now()]);
    }
}
