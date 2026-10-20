<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Cart;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tenant_id' => Tenant::factory(),
            // Buku dibuat untuk tenant yang sama (FK komposit) — closure menerima atribut terselesaikan.
            'book_id' => fn (array $attributes) => Book::factory()
                ->create(['tenant_id' => $attributes['tenant_id']])->id,
            'jumlah' => 1,
        ];
    }
}
