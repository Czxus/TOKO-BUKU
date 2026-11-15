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
        $tenantA = Tenant::factory()->create(['status' => 'active']);
        $tenantB = Tenant::factory()->create(['status' => 'active']);

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

        app(TenantContext::class)->set($tenantA);

        $this->assertSame(2, Book::query()->count());
        $this->assertTrue(Book::query()->get()->every(fn (Book $b) => $b->tenant_id === $tenantA->id));
    }

    public function test_context_is_reset_between_requests(): void
    {
        $tenantA = Tenant::factory()->create();
        app(TenantContext::class)->set($tenantA);

        $this->assertTrue(app(TenantContext::class)->has());

        $this->app->forgetScopedInstances();

        $this->assertFalse(app(TenantContext::class)->has(), 'TenantContext tidak boleh bocor antar request!');
    }

    public function test_tenant_id_is_autofilled_on_book_creation(): void
    {
        $tenantA = Tenant::factory()->create();
        app(TenantContext::class)->set($tenantA);

        $category = Category::factory()->create();

        $book = Book::create([
            'category_id' => $category->id,
            'judul'       => 'Buku Laravel Multi-Tenant',
            'penulis'     => 'Penulis Contoh',
            'penerbit'    => 'Penerbit Contoh',
            'isbn'        => '9786020000099',
            'harga'       => 50000,
        ]);

        $this->assertSame($tenantA->id, $book->tenant_id);
    }

    public function test_scoped_binding_blocks_cross_tenant_access(): void
    {
        $tenantA = Tenant::factory()->create(['domain_slug' => 'toko-a']);
        $tenantB = Tenant::factory()->create(['domain_slug' => 'toko-b']);

        $categoryB = Category::create(['nama_kategori' => 'Kategori Toko B']);

        $userA = User::factory()->create();

        $bookB = Book::factory()->create([
            'tenant_id'   => $tenantB->id,
            'category_id' => $categoryB->id,
        ]);

        $this->actingAs($userA);

        $response = $this->getJson("/tenant/toko-a/books/{$bookB->id}");

        $response->assertNotFound();
    }
}
