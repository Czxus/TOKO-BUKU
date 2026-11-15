<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Tenant;
use App\Services\PublicCatalogQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatalogPublicTest extends TestCase
{
    use RefreshDatabase;

    private function book(Tenant $tenant, array $attrs = []): Book
    {
        return Book::factory()->create(array_merge([
            'tenant_id' => $tenant->id, 'category_id' => Category::factory()->create()->id,
        ], $attrs));
    }

    public function test_catalog_rules_inactive_deleted_and_suspended_tenant(): void
    {
        $aktif = Tenant::factory()->create(['status' => 'active']);
        $ditutup = Tenant::factory()->create(['status' => 'suspended']);

        $this->book($aktif, ['judul' => 'Buku Tampil']);
        $this->book($aktif, ['judul' => 'Buku Habis Fisik', 'stok' => 0]);
        $this->book($aktif, ['judul' => 'Buku Disembunyikan', 'is_active' => false]);
        $this->book($aktif, ['judul' => 'Buku Terhapus'])->delete();
        $this->book($ditutup, ['judul' => 'Buku Toko Suspended']);

        $this->get(route('katalog'))
            ->assertOk()
            ->assertSee('Buku Tampil')
            ->assertSee('Buku Habis Fisik')      // habis tetap tampil, berlabel
            ->assertSee('Stok Habis')
            ->assertDontSee('Buku Disembunyikan')
            ->assertDontSee('Buku Terhapus')
            ->assertDontSee('Buku Toko Suspended');
    }

    public function test_public_catalog_query_is_free_of_n_plus_one(): void
    {
        $tenant = Tenant::factory()->create();
        Book::factory()->count(20)->create([
            'tenant_id' => $tenant->id, 'category_id' => Category::factory()->create()->id,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        app(PublicCatalogQuery::class)->getPublicBooks()->each(function (Book $b): void {
            $b->tenant->nama_toko;
            $b->category?->nama_kategori;
        });

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(6, $count, "Query terlalu banyak ({$count}) — indikasi N+1");
    }
}
