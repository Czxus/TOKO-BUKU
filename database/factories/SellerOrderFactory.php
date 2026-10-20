<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\SellerOrder;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SellerOrder>
 */
class SellerOrderFactory extends Factory
{
    public function definition(): array
    {
        // Pendapatan tenant = subtotal + ongkir - komisi (SRS FR-PAY-02).
        return [
            'order_id' => Order::factory(),
            'tenant_id' => Tenant::factory(),
            'metode_pemenuhan' => 'pengiriman',
            'kurir' => 'JNE',
            'subtotal_buku' => 50000,
            'ongkos_kirim' => 10000,
            'komisi_rate_snapshot' => 0.1000,
            'komisi_platform' => 5000,
            'pendapatan_tenant' => 55000,
            'status_pesanan' => 'pending',
        ];
    }

    public function pickup(): static
    {
        return $this->state([
            'metode_pemenuhan' => 'pickup',
            'kurir' => null,
            'ongkos_kirim' => 0,
            'pendapatan_tenant' => 45000,
        ]);
    }
}
