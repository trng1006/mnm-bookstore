<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class Flow2ShoppingAndOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $admin;

    private Book $book;

    private Coupon $coupon;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->customer = User::factory()->create([
            'name' => 'Nguyễn Khách Hàng',
            'email' => 'customer@test.com',
            'role' => 'customer',
            'phone' => '0912345678',
            'address' => '123 Đường Sách, Q1, TP.HCM',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Quản Trị Viên',
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);
        $this->admin->assignRole('admin');

        $author = Author::create(['name' => 'Tác Giả A', 'slug' => 'tac-gia-a']);
        $category = Category::create(['name' => 'Văn Học', 'slug' => 'van-hoc']);

        $this->book = Book::create([
            'title' => 'Sách Thử Nghiệm Luồng 2',
            'slug' => 'sach-thu-nghiem-luong-2',
            'author_id' => $author->id,
            'category_id' => $category->id,
            'price_current' => 100000,
            'price_old' => 120000,
            'stock' => 50,
            'description' => 'Mô tả tác phẩm sách thử nghiệm.',
            'is_new' => true,
        ]);

        $this->coupon = Coupon::create([
            'code' => 'GIAM10',
            'type' => 'percent',
            'value' => 10,
            'min_order_value' => 50000,
            'is_active' => true,
        ]);
    }

    /** 1. Chi tiết sản phẩm */
    public function test_can_view_book_details_page(): void
    {
        $response = $this->get(route('books.show', $this->book->slug));

        $response->assertStatus(200);
        $response->assertSee('Sách Thử Nghiệm Luồng 2');
        $response->assertSee('100.000');
        $response->assertSee('Tác Giả A');
    }

    /** 2. Đánh giá sách (Review / Rating) */
    public function test_guest_cannot_submit_review(): void
    {
        $response = $this->post(route('books.reviews.store', $this->book->slug), [
            'rating' => 5,
            'comment' => 'Sách rất hay và ý nghĩa!',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_submit_review_without_completed_order(): void
    {
        $response = $this->actingAs($this->customer)
            ->post(route('books.reviews.store', $this->book->slug), [
                'rating' => 5,
                'comment' => 'Cuốn sách này cực kỳ xuất sắc!',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('reviews', [
            'user_id' => $this->customer->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_logged_in_user_can_submit_review_after_order_completed(): void
    {
        // Tạo đơn hàng đã hoàn tất cho khách hàng này
        $order = Order::create([
            'order_number' => 'MNM-TEST-REVIEW-COMPLETED',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'customer_phone' => $this->customer->phone,
            'shipping_address' => $this->customer->address,
            'payment_method' => 'cod',
            'status' => Order::STATUS_COMPLETED,
            'payment_status' => 'paid',
            'subtotal' => 100000,
            'shipping_fee' => 0,
            'total_amount' => 100000,
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $this->book->id,
            'book_title' => $this->book->title,
            'price' => 100000,
            'quantity' => 1,
            'total' => 100000,
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('books.reviews.store', $this->book->slug), [
                'rating' => 5,
                'comment' => 'Cuốn sách này cực kỳ xuất sắc!',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->customer->id,
            'book_id' => $this->book->id,
            'rating' => 5,
        ]);

        $this->book->refresh();
        $this->assertEquals(5.0, $this->book->rating);
        $this->assertEquals(1, $this->book->reviews_count);

        // Xem trang chi tiết đơn hàng: thấy nút "Sửa đánh giá" cho quyển này
        $viewResponse = $this->actingAs($this->customer)->get(route('account.orders.show', $order->order_number));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Sửa đánh giá');
    }

    /** 3. Giỏ hàng (Cart) */
    public function test_can_add_item_to_cart_and_view_cart(): void
    {
        $response = $this->post(route('cart.add'), [
            'book_id' => $this->book->id,
            'quantity' => 2,
        ]);

        $response->assertSessionHas('cart');
        $cart = session('cart');
        $this->assertArrayHasKey($this->book->id, $cart);
        $this->assertEquals(2, $cart[$this->book->id]['quantity']);

        // Xem trang giỏ hàng
        $viewResponse = $this->get(route('cart.index'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Sách Thử Nghiệm Luồng 2');
        $viewResponse->assertSee('200.000'); // 2 x 100.000
    }

    public function test_can_apply_coupon_in_cart(): void
    {
        $this->withSession([
            'cart' => [
                $this->book->id => [
                    'id' => $this->book->id,
                    'title' => $this->book->title,
                    'slug' => $this->book->slug,
                    'price' => 100000,
                    'quantity' => 1,
                    'stock' => 50,
                ],
            ],
        ]);

        $response = $this->post(route('cart.coupon.apply'), [
            'coupon_code' => 'GIAM10',
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('coupon');
        $coupon = session('coupon');
        $this->assertEquals('GIAM10', $coupon['code']);
        $this->assertEquals(10000, $coupon['discount']); // 10% của 100.000
    }

    /** 4. Khách vãng lai có thể vào trang thanh toán nhưng nếu giỏ hàng trống sẽ bị đẩy về giỏ hàng */
    public function test_guest_is_redirected_to_cart_when_cart_empty(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('cart.show'));
    }

    public function test_customer_can_checkout_successfully(): void
    {
        Notification::fake();

        $this->actingAs($this->customer);

        // Chuẩn bị giỏ hàng trong session
        $this->withSession([
            'cart' => [
                $this->book->id => [
                    'id' => $this->book->id,
                    'title' => $this->book->title,
                    'slug' => $this->book->slug,
                    'cover_image' => null,
                    'price' => 100000,
                    'quantity' => 2,
                    'stock' => 50,
                ],
            ],
        ]);

        $response = $this->post(route('checkout.process'), [
            'customer_name' => 'Nguyễn Khách Hàng',
            'customer_phone' => '0912345678',
            'customer_email' => 'customer@test.com',
            'shipping_address' => 'Số 123 Đường Hoa Hồng, Phường 1, Quận 3, TP.HCM',
            'shipping_notes' => 'Giao hàng cẩn thận',
            'payment_method' => 'cod',
        ]);

        $order = Order::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(200000, $order->subtotal);
        $this->assertEquals(230000, $order->total_amount); // 200k + 30k ship
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'book_id' => $this->book->id,
            'quantity' => 2,
        ]);

        // Kiểm tra tồn kho đã bị trừ ngay khi đặt hàng (Pending) để chống Race Condition
        $this->book->refresh();
        $this->assertEquals(48, $this->book->stock); // 50 - 2 = 48

        // Kiểm tra gửi notification
        Notification::assertSentTo($this->customer, OrderPlacedNotification::class);
        Notification::assertSentTo($this->admin, OrderPlacedNotification::class);

        // Chuyển hướng đến trang hoàn tất
        $response->assertRedirect(route('checkout.success', $order->order_number));
    }

    public function test_guest_can_checkout_successfully(): void
    {
        Notification::fake();

        // Chuẩn bị giỏ hàng trong session
        $this->withSession([
            'cart' => [
                $this->book->id => [
                    'id' => $this->book->id,
                    'title' => $this->book->title,
                    'slug' => $this->book->slug,
                    'cover_image' => null,
                    'price' => 100000,
                    'quantity' => 2,
                    'stock' => 50,
                ],
            ],
        ]);

        $response = $this->post(route('checkout.process'), [
            'customer_name' => 'Khách Vãng Lai',
            'customer_email' => 'guest@test.com',
            'customer_phone' => '0999999999',
            'shipping_address' => '123 Đường Vãng Lai, Phường Vãng Lai',
            'payment_method' => 'bank_transfer', // Khách phải dùng bank_transfer
        ]);

        $order = Order::where('customer_email', 'guest@test.com')->first();
        $this->assertNotNull($order);
        $this->assertNull($order->user_id);
        $this->assertEquals('bank_transfer', $order->payment_method);

        $response->assertRedirect(route('checkout.payos.pay', $order->order_number));
    }

    /** 5. Admin xem danh sách đơn hàng */
    public function test_admin_can_view_orders_list(): void
    {
        Order::create([
            'order_number' => 'MNM-TEST-ORDER-1',
            'user_id' => $this->customer->id,
            'customer_name' => 'Khách Hàng',
            'customer_email' => 'khach@test.com',
            'customer_phone' => '0912345678',
            'shipping_address' => 'Địa chỉ test',
            'payment_method' => 'cod',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 100000,
            'shipping_fee' => 30000,
            'total_amount' => 130000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.index'));
        $response->assertStatus(200);
        $response->assertSee('MNM-TEST-ORDER-1');
        $response->assertSee('Chờ xác nhận');
    }

    /** 5. Tra cứu đơn hàng công khai */
    public function test_can_track_order_publicly(): void
    {
        $order = Order::create([
            'order_number' => 'MNM-TEST-ORDER-1',
            'user_id' => $this->customer->id,
            'customer_name' => 'Khách Hàng',
            'customer_email' => 'khach@test.com',
            'customer_phone' => '0912345678',
            'shipping_address' => 'Địa chỉ test',
            'payment_method' => 'cod',
            'status' => 'pending',
            'subtotal' => 100000,
            'shipping_fee' => 30000,
            'total_amount' => 130000,
        ]);

        $response = $this->get(route('orders.tracking', [
            'order_number' => 'MNM-TEST-ORDER-1',
            'phone' => '0912345678',
        ]));

        $response->assertStatus(200);
        $response->assertSee('MNM-TEST-ORDER-1');
        $response->assertSee('Chờ xác nhận');
    }

    /** 6. Admin cập nhật trạng thái đơn hàng & gửi thông báo */
    public function test_admin_can_update_order_status_and_notify_customer(): void
    {
        Notification::fake();

        $order = Order::create([
            'order_number' => 'MNM-TEST-ORDER-2',
            'user_id' => $this->customer->id,
            'customer_name' => 'Khách Hàng',
            'customer_email' => 'khach@test.com',
            'customer_phone' => '0912345678',
            'shipping_address' => 'Địa chỉ test',
            'payment_method' => 'cod',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 100000,
            'shipping_fee' => 30000,
            'total_amount' => 130000,
        ]);
        
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $this->book->id,
            'book_title' => $this->book->title,
            'price' => 100000,
            'quantity' => 1,
            'total' => 100000,
        ]);

        // Cập nhật trạng thái từ pending -> confirmed
        $response = $this->actingAs($this->admin)->from(route('admin.orders.show', $order->id))->patch(route('admin.orders.updateStatus', $order->id), [
            'status' => 'confirmed',
            'payment_status' => 'pending',
        ]);

        $response->assertRedirect(route('admin.orders.show', $order->id));
        $order->refresh();
        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals('pending', $order->payment_status);
        
        // Kiểm tra không bị trừ kho lần 2 khi Admin duyệt confirmed (vì đã trừ lúc đặt hàng)
        $this->book->refresh();
        $this->assertEquals(50, $this->book->stock);

        Notification::assertSentTo($this->customer, OrderStatusUpdatedNotification::class);
    }

    /** 7. Kiểm tra hoàn lại tồn kho khi Admin hủy đơn hàng */
    public function test_admin_cancelling_order_restores_book_stock(): void
    {
        $order = Order::create([
            'order_number' => 'MNM-TEST-CANCEL-1',
            'user_id' => $this->customer->id,
            'customer_name' => 'Khách Hàng',
            'customer_email' => 'khach@test.com',
            'customer_phone' => '0912345678',
            'shipping_address' => 'Địa chỉ test',
            'payment_method' => 'cod',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 100000,
            'shipping_fee' => 30000,
            'total_amount' => 130000,
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $this->book->id,
            'book_title' => $this->book->title,
            'price' => 100000,
            'quantity' => 2,
            'total' => 200000,
        ]);

        // Giả lập tồn kho đã bị trừ 2 cuốn khi checkout (50 -> 48)
        $this->book->update(['stock' => 48]);

        // Admin hủy đơn hàng
        $response = $this->actingAs($this->admin)->from(route('admin.orders.show', $order->id))->patch(route('admin.orders.updateStatus', $order->id), [
            'status' => 'cancelled',
            'payment_status' => 'pending',
        ]);

        $response->assertRedirect(route('admin.orders.show', $order->id));
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        // Kiểm tra tồn kho được hoàn lại đầy đủ: 48 + 2 = 50
        $this->book->refresh();
        $this->assertEquals(50, $this->book->stock);
    }

    /** 8. Kiểm tra hoàn lại tồn kho khi Khách hàng tự hủy đơn hàng */
    public function test_customer_cancelling_order_restores_book_stock(): void
    {
        $order = Order::create([
            'order_number' => 'MNM-TEST-CUSTOMER-CANCEL',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'customer_phone' => $this->customer->phone,
            'shipping_address' => $this->customer->address,
            'payment_method' => 'cod',
            'status' => Order::STATUS_PENDING,
            'payment_status' => 'pending',
            'subtotal' => 100000,
            'shipping_fee' => 30000,
            'total_amount' => 130000,
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $this->book->id,
            'book_title' => $this->book->title,
            'price' => 100000,
            'quantity' => 3,
            'total' => 300000,
        ]);

        // Giả lập tồn kho đã bị trừ 3 cuốn khi checkout (50 -> 47)
        $this->book->update(['stock' => 47]);

        // Khách hàng tự hủy đơn
        $response = $this->actingAs($this->customer)->post(route('account.orders.cancel', $order->order_number));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(Order::STATUS_CANCELLED, $order->status);

        // Kiểm tra tồn kho được hoàn lại đầy đủ: 47 + 3 = 50
        $this->book->refresh();
        $this->assertEquals(50, $this->book->stock);
    }

    /** 8. Tìm kiếm nhanh đơn hàng qua AJAX trong Admin */
    public function test_admin_can_quick_search_orders_via_ajax(): void
    {
        $order = Order::create([
            'order_number' => 'MNM-TEST-AJAX-SEARCH-999',
            'user_id' => $this->customer->id,
            'customer_name' => 'Trần Văn Demo',
            'customer_email' => 'demo_ajax@test.com',
            'customer_phone' => '0988776655',
            'shipping_address' => '789 Đường Test, Q3, TP.HCM',
            'payment_method' => 'cod',
            'status' => Order::STATUS_PENDING,
            'payment_status' => 'pending',
            'subtotal' => 200000,
            'shipping_fee' => 0,
            'total_amount' => 200000,
        ]);

        // 1. Tìm theo mã đơn hàng
        $response = $this->actingAs($this->admin)->getJson(route('admin.orders.ajaxSearch', ['q' => 'SEARCH-999']));
        $response->assertStatus(200);
        $response->assertJsonFragment(['order_number' => 'MNM-TEST-AJAX-SEARCH-999']);
        $response->assertJsonFragment(['customer_name' => 'Trần Văn Demo']);

        // 2. Tìm theo số điện thoại
        $responsePhone = $this->actingAs($this->admin)->getJson(route('admin.orders.ajaxSearch', ['q' => '098877']));
        $responsePhone->assertStatus(200);
        $responsePhone->assertJsonFragment(['order_number' => 'MNM-TEST-AJAX-SEARCH-999']);

        // 3. Từ khóa dưới 2 ký tự trả về mảng rỗng
        $responseShort = $this->actingAs($this->admin)->getJson(route('admin.orders.ajaxSearch', ['q' => 'M']));
        $responseShort->assertStatus(200);
        $responseShort->assertExactJson(['orders' => []]);

        // 4. Khách hàng thông thường không có quyền truy cập
        $responseForbidden = $this->actingAs($this->customer)->getJson(route('admin.orders.ajaxSearch', ['q' => 'SEARCH-999']));
        $responseForbidden->assertStatus(403);
    }
}
