<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class BugfixVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        Config::set('services.payos', [
            'client_id' => 'test-client-id',
            'api_key' => 'test-api-key',
            'checksum_key' => 'test_checksum_key_64_chars_example_1234567890abcdef1234567890',
            'base_url' => 'https://api-merchant.payos.vn',
        ]);
    }

    public function test_payos_cancel_restores_stock_and_admin_cancel_does_not_double_restore(): void
    {
        $author = Author::create(['name' => 'Author 1', 'slug' => 'author-1']);
        $category = Category::create(['name' => 'Cat 1', 'slug' => 'cat-1']);
        $book = Book::create([
            'title' => 'Book 1',
            'slug' => 'book-1',
            'author_id' => $author->id,
            'category_id' => $category->id,
            'price_current' => 100000,
            'stock' => 2, // Đã trừ 1 khi checkout từ 3 -> 2
        ]);
        $coupon = Coupon::create([
            'code' => 'TEST10',
            'type' => 'fixed',
            'value' => 10000,
            'min_order_value' => 0,
            'is_active' => true,
            'used_count' => 1,
        ]);

        $order = Order::create([
            'order_number' => 'MNM-TEST-PAYOS-CANCEL',
            'user_id' => null,
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '0901234567',
            'shipping_address' => 'TP.HCM',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
            'status' => Order::STATUS_PENDING,
            'subtotal' => 100000,
            'shipping_fee' => 30000,
            'discount_amount' => 10000,
            'total_amount' => 120000,
            'coupon_code' => 'TEST10',
            'payos_order_code' => 123456789,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book->id,
            'book_title' => $book->title,
            'price' => 100000,
            'quantity' => 1,
            'total' => 100000,
        ]);

        Http::fake([
            'api-merchant.payos.vn/*' => Http::response(['code' => '00', 'data' => ['status' => 'CANCELLED']], 200),
        ]);

        $this->post(route('checkout.payos.cancel-order', $order->order_number))
            ->assertRedirect(route('checkout.success', $order->order_number));

        $this->assertSame(Order::STATUS_PAYMENT_FAILED, $order->fresh()->status);
        $this->assertSame(3, $book->fresh()->stock);
        $this->assertSame(0, $coupon->fresh()->used_count);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), [
            'status' => Order::STATUS_CANCELLED,
            'payment_status' => 'pending',
        ])->assertRedirect();

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(3, $book->fresh()->stock);
        $this->assertSame(0, $coupon->fresh()->used_count);
    }

    public function test_admin_cannot_delete_author_with_books(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);
        $admin->assignRole('admin');

        $author = Author::create(['name' => 'Author Has Book', 'slug' => 'author-has-book']);
        $category = Category::create(['name' => 'Cat 2', 'slug' => 'cat-2']);
        $book = Book::create([
            'title' => 'Kept Book',
            'slug' => 'kept-book',
            'author_id' => $author->id,
            'category_id' => $category->id,
            'price_current' => 50000,
            'stock' => 5,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.authors.destroy', $author))
            ->assertRedirect(route('admin.authors.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('authors', ['id' => $author->id]);
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_admin_user_crud_syncs_spatie_roles_and_book_crud_saves_stock_and_description(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin3@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Staff',
            'email' => 'newstaff@example.com',
            'password' => 'password123',
            'role' => 'staff',
        ])->assertRedirect(route('admin.users.index'));

        $staff = User::where('email', 'newstaff@example.com')->firstOrFail();
        $this->assertTrue($staff->hasRole('staff'));
        $this->assertTrue($staff->can('manage_orders'));
        $this->assertFalse($staff->can('manage_users'));

        $author = Author::create(['name' => 'Author B', 'slug' => 'author-b']);
        $category = Category::create(['name' => 'Cat B', 'slug' => 'cat-b']);

        $this->actingAs($admin)->post(route('admin.books.store'), [
            'title' => 'Book Stock Test',
            'slug' => 'book-stock-test',
            'category_id' => $category->id,
            'author_id' => $author->id,
            'price_current' => 75000,
            'stock' => 42,
            'description' => 'Mo ta sach test',
        ])->assertRedirect(route('admin.books.index'));

        $this->assertDatabaseHas('books', [
            'slug' => 'book-stock-test',
            'stock' => 42,
            'description' => 'Mo ta sach test',
        ]);
    }

    public function test_chat_history_never_exceeds_10_and_notification_read_all_returns_json(): void
    {
        $user = User::create([
            'name' => 'Reader',
            'email' => 'reader@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        $this->actingAs($user)
            ->postJson(route('account.notifications.readAll'))
            ->assertOk()
            ->assertJson(['success' => true, 'unreadCount' => 0]);

        Session::put('chat_history', array_fill(0, 10, ['role' => 'user', 'parts' => [['text' => 'hi']]]));
        if (env('GEMINI_API_KEY')) {
            Http::fake([
                'generativelanguage.googleapis.com/*' => Http::response([
                    'candidates' => [['content' => ['parts' => [['text' => 'Chao ban']]]]],
                ], 200),
            ]);
            $this->postJson(route('chat.respond'), ['message' => 'Xin chao'])
                ->assertOk();
            $this->assertLessThanOrEqual(10, count(Session::get('chat_history', [])));
        }
    }
}
