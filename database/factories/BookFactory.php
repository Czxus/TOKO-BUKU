<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'category_id' => Category::factory(),
            'judul' => Str::title(fake()->words(3, true)),
            'penulis' => fake()->name(),
            'penerbit' => fake()->company(),
            'isbn' => fake()->unique()->numerify('978##########'),
            'tahun_terbit' => fake()->numberBetween(2015, 2026),
            'format_buku' => 'fisik',
            'harga' => fake()->numberBetween(30, 250) * 1000,
            'stok' => 25,
            'berat_gram' => fake()->numberBetween(200, 900),
            'deskripsi' => fake()->paragraph(),
            'is_active' => true,
        ];
    }

    public function ebook(): static
    {
        return $this->state(fn (array $attributes) => [
            'format_buku' => 'ebook',
            'stok' => 0,
            'berat_gram' => 0,
            'file_preview_pdf' => 'previews/contoh.pdf',
            'file_ebook_full' => 'ebooks/contoh.pdf',
        ]);
    }

    public function habis(): static
    {
        return $this->state(['stok' => 0]);
    }
}
