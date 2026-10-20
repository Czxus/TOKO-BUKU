<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\SellerOrder;
use App\Models\SellerOrderItem;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SellerOrderItem>
 */
class SellerOrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            // Sub-pesanan & buku harus milik tenant yang sama (FK komposit).
            'seller_order_id' => fn (array $attributes) => SellerOrder::factory()
                ->create(['tenant_id' => $attributes['tenant_id']])->id,
            'book_id' => fn (array $attributes) => Book::factory()
                ->create(['tenant_id' => $attributes['tenant_id']])->id,
            'judul_snapshot' => fake()->words(3, true),
            'jumlah' => 1,
            'harga_satuan' => 50000,
        ];
    }
}
