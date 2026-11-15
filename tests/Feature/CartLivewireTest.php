<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Ordering\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

/** Modul 8 — UI keranjang (halaman, badge) dan tombol "Tambah ke keranjang" di katalog. */
class CartLivewireTest extends TestCase
{
    use RefreshDatabase;

    private function pembeli(): User
    {
        return User::factory()->create(['role' => 'pembeli']);
    }

    private function book(array $attrs = [], ?Tenant $tenant = null): Book
    {
        $tenant ??= Tenant::factory()->create();

        return Book::factory()->create(array_merge([
            'tenant_id' => $tenant->id,
            'harga' => 10000,
            'stok' => 10,
        ], $attrs));
    }

    private function cart(): CartService
    {
        return app(CartService::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('keranjang'))->assertRedirect(route('login'));
    }

    public function test_non_buyer_cannot_open_cart_page(): void
    {
        $tenant = Tenant::factory()->create();
        $seller = User::factory()->create(['role' => 'seller_admin', 'tenant_id' => $tenant->id]);

        $this->actingAs($seller);

        Volt::test('pages.cart.index')->assertForbidden();
    }

    public function test_empty_cart_shows_empty_state(): void
    {
        $this->actingAs($this->pembeli());

        Volt::test('pages.cart.index')
            ->assertSee('Keranjang kosong')
            ->assertDontSee('Lanjut ke Pembayaran');
    }

    public function test_cart_groups_lines_per_store_with_subtotals_and_total(): void
    {
        $user = $this->pembeli();
        $alfa = Tenant::factory()->create(['nama_toko' => 'Toko Alfa']);
        $beta = Tenant::factory()->create(['nama_toko' => 'Toko Beta']);
        $satu = $this->book(['judul' => 'Buku Satu', 'harga' => 10000], $alfa);
        $dua = $this->book(['judul' => 'Buku Dua', 'harga' => 20000], $beta);

        $this->cart()->add($user, $satu->id, 1);
        $this->cart()->add($user, $dua->id, 1);

        $this->actingAs($user);

        Volt::test('pages.cart.index')
            ->assertSeeInOrder(['Toko Alfa', 'Buku Satu', 'Toko Beta', 'Buku Dua'])
            ->assertSee('Rp 30.000'); // total = 10.000 + 20.000
    }

    public function test_increment_decrement_and_remove_update_cart_and_notify_badge(): void
    {
        $user = $this->pembeli();
        $book = $this->book(['harga' => 10000]);
        $this->cart()->add($user, $book->id, 1);

        $this->actingAs($user);

        Volt::test('pages.cart.index')
            ->call('increment', $book->id)
            ->assertSee('Rp 20.000') // 2 × 10.000
            ->assertDispatched('cart-updated')
            ->call('decrement', $book->id)
            ->call('decrement', $book->id)
            ->assertSee('Keranjang kosong');

        $this->cart()->add($user, $book->id, 1);

        Volt::test('pages.cart.index')
            ->call('remove', $book->id)
            ->assertSee('Keranjang kosong');

        $this->assertSame(0, $this->cart()->itemCount($user));
    }

    public function test_increment_beyond_stock_shows_error_and_keeps_quantity(): void
    {
        $user = $this->pembeli();
        $book = $this->book(['stok' => 1]);
        $this->cart()->add($user, $book->id, 1);

        $this->actingAs($user);

        Volt::test('pages.cart.index')
            ->call('increment', $book->id)
            ->assertSee('Jumlah di keranjang sudah mencapai batas yang tersedia.');

        $this->assertSame(1, $this->cart()->itemCount($user));
    }

    public function test_stock_drop_blocks_checkout_and_explains_why(): void
    {
        $user = $this->pembeli();
        $book = $this->book(['stok' => 10]);
        $this->cart()->add($user, $book->id, 3);
        $book->update(['stok' => 1]);

        $this->actingAs($user);

        Volt::test('pages.cart.index')
            ->assertSee('Stok tidak mencukupi')
            ->assertSee('Perbaiki item bermasalah sebelum melanjutkan.');
    }

    public function test_price_change_is_explained_and_can_be_accepted(): void
    {
        $user = $this->pembeli();
        $book = $this->book(['harga' => 10000]);
        $this->cart()->add($user, $book->id, 1);
        $book->update(['harga' => 12500]);

        $this->actingAs($user);

        Volt::test('pages.cart.index')
            ->assertSee('Harga diperbarui')
            ->assertSee('Rp 12.500')
            ->call('acceptPrices')
            ->assertDontSee('Harga diperbarui');
    }

    public function test_clear_empties_cart(): void
    {
        $user = $this->pembeli();
        $this->cart()->add($user, $this->book()->id, 2);

        $this->actingAs($user);

        Volt::test('pages.cart.index')
            ->call('clear')
            ->assertSee('Keranjang kosong');

        $this->assertSame(0, $this->cart()->itemCount($user));
    }

    public function test_catalog_add_button_stores_book_in_cart_and_notifies_badge(): void
    {
        $user = $this->pembeli();
        $book = $this->book();

        $this->actingAs($user);

        Volt::test('pages.catalog.index')
            ->call('tambah', $book->id)
            ->assertSee('ditambahkan ke keranjang')
            ->assertDispatched('cart-updated');

        $this->assertSame(1, $this->cart()->itemCount($user));
    }

    public function test_catalog_add_button_rejects_guest_seller_and_unavailable_books(): void
    {
        $book = $this->book();

        // Tamu → diarahkan ke login, tidak ada yang tersimpan.
        Volt::test('pages.catalog.index')
            ->call('tambah', $book->id)
            ->assertRedirect(route('login'));

        // Akun non-pembeli ditolak.
        $tenant = Tenant::factory()->create();
        $seller = User::factory()->create(['role' => 'seller_admin', 'tenant_id' => $tenant->id]);
        $this->actingAs($seller);

        Volt::test('pages.catalog.index')
            ->call('tambah', $book->id)
            ->assertSee('Hanya akun pembeli yang dapat berbelanja.');

        $this->assertSame(0, $this->cart()->itemCount($seller));

        // Pembeli: buku habis dan buku disembunyikan ditolak dengan pesan yang jelas.
        $this->actingAs($this->pembeli());
        $habis = $this->book(['stok' => 0]);
        $tersembunyi = $this->book(['is_active' => false]);

        Volt::test('pages.catalog.index')
            ->call('tambah', $habis->id)
            ->assertSee('Stok buku ini habis.')
            ->call('tambah', $tersembunyi->id)
            ->assertSee('Buku ini sudah tidak tersedia.');
    }

    public function test_badge_shows_item_count_and_refreshes_on_event(): void
    {
        $user = $this->pembeli();
        $book = $this->book();

        $this->actingAs($user);

        $badge = Volt::test('cart.badge')->assertDontSeeHtml('data-test="cart-badge"');

        $this->cart()->add($user, $book->id, 2);

        $badge->dispatch('cart-updated')->assertSeeHtml('data-test="cart-badge">2<');
    }
}
