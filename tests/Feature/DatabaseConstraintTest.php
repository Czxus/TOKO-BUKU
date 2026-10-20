<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Cart;
use App\Models\CommissionScheme;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SellerOrder;
use App\Models\SellerOrderItem;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\DemoTokoBukuSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Constraint database (SRS Toko Buku): FK komposit anti lintas-tenant, unique/idempotensi,
 * invariant "orders tanpa tenant_id", dan seeder idempoten.
 * Disarankan dijalankan pada MariaDB (target SRS); juga lulus di SQLite.
 */
class DatabaseConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_cannot_point_to_another_tenants_book(): void
    {
        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create();
        $bookB = Book::factory()->create(['tenant_id' => $b->id]);
        $user = User::factory()->create();

        $this->expectException(QueryException::class);
        Cart::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $a->id,      // tenant A
            'book_id' => $bookB->id,    // buku milik tenant B -> FK komposit gagal
        ]);
    }

    public function test_seller_order_item_cannot_reference_another_tenants_book(): void
    {
        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create();
        $sellerOrderA = SellerOrder::factory()->create(['tenant_id' => $a->id]);
        $bookB = Book::factory()->create(['tenant_id' => $b->id]);

        $this->expectException(QueryException::class);
        SellerOrderItem::factory()->create([
            'tenant_id' => $a->id,
            'seller_order_id' => $sellerOrderA->id,
            'book_id' => $bookB->id,
        ]);
    }

    public function test_duplicate_domain_slug_is_rejected(): void
    {
        Tenant::factory()->create(['domain_slug' => 'toko-dup']);

        $this->expectException(QueryException::class);
        Tenant::factory()->create(['domain_slug' => 'toko-dup']);
    }

    public function test_isbn_is_unique_per_tenant_but_may_repeat_across_tenants(): void
    {
        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create();

        Book::factory()->create(['tenant_id' => $a->id, 'isbn' => '9786020000011']);
        Book::factory()->create(['tenant_id' => $b->id, 'isbn' => '9786020000011']); // boleh

        $this->assertSame(2, Book::where('isbn', '9786020000011')->count());

        $this->expectException(QueryException::class);
        Book::factory()->create(['tenant_id' => $a->id, 'isbn' => '9786020000011']); // duplikat di tenant A
    }

    public function test_same_book_cannot_be_added_twice_to_one_users_cart(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $attrs = ['user_id' => $user->id, 'tenant_id' => $book->tenant_id, 'book_id' => $book->id];

        Cart::factory()->create($attrs);

        $this->expectException(QueryException::class);
        Cart::factory()->create($attrs);
    }

    public function test_orders_has_no_tenant_id_and_supports_multiple_seller_orders(): void
    {
        $this->assertFalse(Schema::hasColumn('orders', 'tenant_id'), 'orders TIDAK boleh punya tenant_id');

        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create();
        $order = Order::factory()->create();

        SellerOrder::factory()->create(['order_id' => $order->id, 'tenant_id' => $a->id]);
        SellerOrder::factory()->create(['order_id' => $order->id, 'tenant_id' => $b->id]);

        // Satu order induk memuat dua sub-pesanan lintas toko.
        $this->assertSame(2, $order->sellerOrders()->count());
    }

    public function test_only_one_seller_order_per_tenant_per_order(): void
    {
        $tenant = Tenant::factory()->create();
        $order = Order::factory()->create();

        SellerOrder::factory()->create(['order_id' => $order->id, 'tenant_id' => $tenant->id]);

        $this->expectException(QueryException::class);
        SellerOrder::factory()->create(['order_id' => $order->id, 'tenant_id' => $tenant->id]);
    }

    public function test_payment_event_provider_id_is_unique(): void
    {
        $payment = Payment::factory()->create();
        $row = fn () => [
            'payment_id' => $payment->id,
            'provider_event_id' => 'evt-1',
            'payload' => json_encode(['status' => 'settlement']),
            'result' => 'verified',
            'received_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('payment_events')->insert($row());

        $this->expectException(QueryException::class);
        DB::table('payment_events')->insert($row()); // webhook duplikat ditolak
    }

    public function test_ledger_idempotency_key_is_unique(): void
    {
        $tenant = Tenant::factory()->create();
        $row = fn () => [
            'tenant_id' => $tenant->id,
            'idempotency_key' => 'led-1',
            'type' => 'sale_credit',
            'delta_tersedia' => 55000,
            'delta_tertahan' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('ledger_entries')->insert($row());

        $this->expectException(QueryException::class);
        DB::table('ledger_entries')->insert($row());
    }

    public function test_invalid_role_is_rejected(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('Enum role hanya diuji pada MariaDB/MySQL.');
        }

        $this->expectException(QueryException::class);
        User::factory()->create(['role' => 'hacker']);
    }

    public function test_money_columns_cast_to_integer(): void
    {
        $book = Book::factory()->create(['harga' => 85000]);

        $this->assertIsInt($book->refresh()->harga);
        $this->assertSame(85000, $book->harga);
    }

    public function test_commission_rate_keeps_exact_decimal(): void
    {
        $skema = CommissionScheme::factory()->create(['persen_komisi' => 0.1250]);

        $this->assertSame('0.1250', $skema->refresh()->persen_komisi);
    }

    public function test_seeder_is_idempotent_and_creates_two_tenants(): void
    {
        $this->seed(DemoTokoBukuSeeder::class);
        $this->seed(DemoTokoBukuSeeder::class); // run kedua tidak menambah baris

        $this->assertSame(2, Tenant::count());
        $this->assertSame(6, Book::count());
        $this->assertSame(4, User::count());
        $this->assertSame(1, CommissionScheme::whereNull('tenant_id')->count());
        $this->assertSame(2, User::where('role', 'seller_admin')->whereNotNull('tenant_id')->count());
    }
}
