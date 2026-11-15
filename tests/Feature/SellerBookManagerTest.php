<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

/** UC-13 Kelola Buku & Stok dan UC-14 Tandai Habis. */
class SellerBookManagerTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Tenant, 1: User, 2: Category} */
    private function sellerWithTenant(): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['role' => 'seller_admin', 'tenant_id' => $tenant->id]);

        return [$tenant, $user, Category::factory()->create()];
    }

    private function book(Tenant $tenant, Category $category, array $attrs = []): Book
    {
        return Book::factory()->create(array_merge(
            ['tenant_id' => $tenant->id, 'category_id' => $category->id], $attrs
        ));
    }

    public function test_page_renders_for_own_seller(): void
    {
        [$tenant, $user] = $this->sellerWithTenant();

        $this->actingAs($user)
            ->get(route('tenant.books', $tenant->domain_slug))
            ->assertOk()
            ->assertSee('Daftar buku');
    }

    public function test_non_member_is_forbidden_when_mounting_component(): void
    {
        [, $userA] = $this->sellerWithTenant();
        $tenantB = Tenant::factory()->create();

        $this->actingAs($userA);

        Volt::test('pages.seller.books', ['tenant' => $tenantB])->assertForbidden();
    }

    public function test_seller_creates_book_with_tenant_from_context_and_initial_movement(): void
    {
        [$tenant, $user, $category] = $this->sellerWithTenant();

        $this->actingAs($user);

        Volt::test('pages.seller.books', ['tenant' => $tenant])
            ->set('judul', 'Belajar Laravel')->set('penulis', 'Penulis A')->set('penerbit', 'Penerbit B')
            ->set('isbn', '978-602-0000-777')->set('categoryId', $category->id)
            ->set('formatBuku', 'fisik')->set('harga', 90000)->set('stok', 12)->set('beratGram', 400)
            ->call('createBook')->assertHasNoErrors();

        $book = Book::withoutGlobalScopes()->where('judul', 'Belajar Laravel')->firstOrFail();
        $this->assertSame($tenant->id, $book->tenant_id);
        $this->assertSame(12, $book->stok);
        $this->assertSame(1, DB::table('book_stock_movements')->where('book_id', $book->id)->where('type', 'initial')->count());
    }

    public function test_validation_errors_are_per_field_and_input_is_kept(): void
    {
        [$tenant, $user] = $this->sellerWithTenant();

        $this->actingAs($user);

        Volt::test('pages.seller.books', ['tenant' => $tenant])
            ->set('judul', 'Tanpa Kategori')->set('harga', 0)
            ->call('createBook')
            ->assertHasErrors(['categoryId' => 'required', 'harga' => 'min', 'penulis' => 'required'])
            ->assertSet('judul', 'Tanpa Kategori');
    }

    public function test_isbn_must_be_unique_within_same_tenant_only(): void
    {
        [$tenant, $user, $category] = $this->sellerWithTenant();
        $this->book($tenant, $category, ['isbn' => '9786020000111']);

        $this->actingAs($user);

        Volt::test('pages.seller.books', ['tenant' => $tenant])
            ->set('judul', 'Duplikat')->set('penulis', 'A')->set('penerbit', 'B')->set('isbn', '9786020000111')
            ->set('categoryId', $category->id)->set('harga', 1000)->set('stok', 1)->set('beratGram', 100)
            ->call('createBook')->assertHasErrors(['isbn' => 'unique']);
    }

    public function test_mark_out_restock_and_toggle_are_atomic_and_audited(): void
    {
        [$tenant, $user, $category] = $this->sellerWithTenant();
        $book = $this->book($tenant, $category, ['stok' => 5, 'is_active' => true]);

        $this->actingAs($user);

        Volt::test('pages.seller.books', ['tenant' => $tenant])
            ->call('markOut', $book->id)
            ->call('restock', $book->id, 10)
            ->call('toggleActive', $book->id);

        $book->refresh();
        $this->assertSame(10, $book->stok);
        $this->assertFalse($book->is_active);
        $this->assertSame(2, DB::table('book_stock_movements')->where('book_id', $book->id)->count());
        $this->assertTrue(DB::table('audit_logs')->where('entity', 'book')->where('action', 'toggle_active')->exists());
        $this->assertTrue(DB::table('audit_logs')->where('entity', 'book')->where('action', 'stock_mark_out')->exists());
    }

    public function test_edit_updates_book_and_delete_is_soft(): void
    {
        [$tenant, $user, $category] = $this->sellerWithTenant();
        $book = $this->book($tenant, $category, ['judul' => 'Lama', 'harga' => 50000, 'stok' => 5]);

        $this->actingAs($user);

        Volt::test('pages.seller.books', ['tenant' => $tenant])
            ->call('editBook', $book->id)->assertSet('judul', 'Lama')
            ->set('harga', 65000)->set('stok', 8)->call('updateBook')->assertHasNoErrors()
            ->call('deleteBook', $book->id);

        $row = Book::withTrashed()->withoutGlobalScopes()->find($book->id);
        $this->assertSame(65000, $row->harga);
        $this->assertSame(8, $row->stok);
        $this->assertSoftDeleted('books', ['id' => $book->id]);
        $this->assertSame(1, DB::table('book_stock_movements')->where('book_id', $book->id)->where('type', 'adjustment')->count());
    }

    public function test_cover_is_compressed_to_webp(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD tanpa dukungan WebP.');
        }
        Storage::fake('public');
        [$tenant, $user, $category] = $this->sellerWithTenant();

        $this->actingAs($user);

        Volt::test('pages.seller.books', ['tenant' => $tenant])
            ->set('judul', 'Bersampul')->set('penulis', 'A')->set('penerbit', 'B')->set('isbn', '9786020000222')
            ->set('categoryId', $category->id)->set('harga', 1000)->set('stok', 1)->set('beratGram', 100)
            ->set('foto', UploadedFile::fake()->image('sampul.jpg', 1600, 1200))
            ->call('createBook')->assertHasNoErrors();

        $book = Book::withoutGlobalScopes()->where('judul', 'Bersampul')->firstOrFail();
        $this->assertStringEndsWith('.webp', $book->foto_sampul);
        Storage::disk('public')->assertExists($book->foto_sampul);
        [$width] = getimagesize(Storage::disk('public')->path($book->foto_sampul));
        $this->assertSame(800, $width);
    }

    public function test_seller_scope_hides_other_tenants_books(): void
    {
        [$tenant, $user, $category] = $this->sellerWithTenant();
        $otherBook = $this->book(Tenant::factory()->create(), $category);

        $this->actingAs($user);

        Volt::test('pages.seller.books', ['tenant' => $tenant]);

        $this->assertNull(Book::query()->find($otherBook->id), 'buku tenant lain tidak boleh terbaca');
    }
}
