<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_scope_returns_only_active_tenant_books(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $category = Category::factory()->create(); // kategori milik platform

<<<<<<< HEAD
        Book::factory()->count(2)->create(['tenant_id' => $tenantA->id, 'category_id' => $category->id]);
        Book::factory()->count(3)->create(['tenant_id' => $tenantB->id, 'category_id' => $category->id]);
=======
        $categoryA = Category::create(['nama_kategori' => 'Kategori A']);
        $categoryB = Category::create(['nama_kategori' => 'Kategori B']);

        Book::factory()->count(2)->create([
            'tenant_id'   => $tenantA->id,
            'category_id' => $categoryA->id,
        ]);

        Book::factory()->count(3)->create([
            'tenant_id'   => $tenantB->id,
            'category_id' => $categoryB->id,
        ]);
>>>>>>> 71c104605f2a013ea8ee69d3eff2f98de517e881

        app(TenantContext::class)->set($tenantA);

        $this->assertSame(2, Book::query()->count());
        $this->assertTrue(Book::query()->get()->every(fn (Book $b) => $b->tenant_id === $tenantA->id));
    }

    public function test_context_is_reset_between_requests(): void
    {
        app(TenantContext::class)->set(Tenant::factory()->create());
        $this->assertTrue(app(TenantContext::class)->has());

        $this->app->forgetScopedInstances();

        $this->assertFalse(app(TenantContext::class)->has(), 'TenantContext tidak boleh bocor antar request!');
    }

    public function test_tenant_id_is_autofilled_on_book_creation(): void
    {
        $tenantA = Tenant::factory()->create();
        $category = Category::factory()->create();
        app(TenantContext::class)->set($tenantA);

        $category = Category::factory()->create();

        $book = Book::create([
            'category_id' => $category->id,
<<<<<<< HEAD
            'judul' => 'Buku Laravel Multi-Tenant',
            'penulis' => 'Penulis Uji',
            'penerbit' => 'Penerbit Uji',
            'isbn' => '9786020000999',
            'format_buku' => 'fisik',
            'harga' => 50000,
            'stok' => 10,
            'berat_gram' => 300,
=======
            'judul'       => 'Buku Laravel Multi-Tenant',
            'penulis'     => 'Penulis Contoh',
            'penerbit'    => 'Penerbit Contoh',
            'isbn'        => '9786020000099',
            'harga'       => 50000,
>>>>>>> 71c104605f2a013ea8ee69d3eff2f98de517e881
        ]);

        $this->assertSame($tenantA->id, $book->tenant_id);
    }

    public function test_tenant_id_cannot_be_mass_assigned(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $category = Category::factory()->create();
        app(TenantContext::class)->set($tenantA);

<<<<<<< HEAD
        $book = Book::create([
            'tenant_id' => $tenantB->id, // harus diabaikan
            'category_id' => $category->id,
            'judul' => 'Coba Bobol', 'penulis' => 'X', 'penerbit' => 'Y', 'isbn' => '9786020000888',
            'format_buku' => 'fisik', 'harga' => 1000, 'stok' => 1, 'berat_gram' => 100,
        ]);

        $this->assertSame($tenantA->id, $book->tenant_id);
    }
=======
        $categoryB = Category::create(['nama_kategori' => 'Kategori Toko B']);

        $userA = User::factory()->create();

        $bookB = Book::factory()->create([
            'tenant_id'   => $tenantB->id,
            'category_id' => $categoryB->id,
        ]);
>>>>>>> 71c104605f2a013ea8ee69d3eff2f98de517e881

    public function test_seller_cannot_open_another_tenants_portal(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $sellerA = User::factory()->create(['role' => 'seller_admin', 'tenant_id' => $tenantA->id]);

        $this->actingAs($sellerA)
            ->get(route('tenant.books', $tenantB->domain_slug))
            ->assertForbidden();
    }
}
