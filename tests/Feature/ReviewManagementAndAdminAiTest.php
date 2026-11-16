<?php

namespace Tests\Feature;

use App\Models\AdminAiLog;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReviewManagementAndAdminAiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;
    private Category $category;
    private Author $author;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->staff->assignRole('staff');

        $this->customer = User::factory()->create(['role' => 'customer']);

        $this->category = Category::create(['name' => 'Văn học', 'slug' => 'van-hoc']);
        $this->author = Author::create(['name' => 'Nguyễn Nhật Ánh', 'slug' => 'nguyen-nhat-anh']);
        $this->book = Book::create([
            'title' => 'Mắt Biếc',
            'slug' => 'mat-biec',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'price_current' => 90000,
            'stock' => 15,
        ]);

        $order = Order::create([
            'order_number' => 'MNM-REV-001',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'customer_phone' => '0909123456',
            'shipping_address' => 'TP.HCM',
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'status' => Order::STATUS_COMPLETED,
            'subtotal' => 90000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 90000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $this->book->id,
            'book_title' => $this->book->title,
            'price' => 90000,
            'quantity' => 1,
            'total' => 90000,
        ]);
    }

    public function test_customer_cannot_access_admin_reviews_or_ai_endpoints(): void
    {
        $this->actingAs($this->customer)
            ->get(route('admin.reviews.index'))
            ->assertStatus(403);

        $this->actingAs($this->customer)
            ->postJson(route('admin.ai.respond'), ['message' => 'Xin chào'])
            ->assertStatus(403);
    }

    public function test_admin_and_staff_can_moderate_reply_and_delete_reviews(): void
    {
        $review = Review::create([
            'user_id' => $this->customer->id,
            'book_id' => $this->book->id,
            'rating' => 5,
            'comment' => 'Sách rất hay và cảm động!',
            'is_visible' => true,
        ]);
        $this->book->updateRatingStats();
        $this->assertEquals(5.0, (float) $this->book->fresh()->rating);
        $this->assertEquals(1, $this->book->fresh()->reviews_count);

        // Staff có quyền xem danh sách và trả lời
        $this->actingAs($this->staff)
            ->get(route('admin.reviews.index', ['status' => 'unreplied']))
            ->assertOk()
            ->assertSee('Sách rất hay và cảm động!');

        $this->actingAs($this->staff)
            ->post(route('admin.reviews.reply', $review), [
                'admin_reply' => 'Cảm ơn bạn đã ủng hộ MNM Bookstore.',
            ])
            ->assertRedirect();

        $this->get(route('books.show', $this->book->slug))
            ->assertOk()
            ->assertSee('Cảm ơn bạn đã ủng hộ MNM Bookstore.');

        // Admin ẩn bình luận -> điểm sách về 0 và khóa quyền khách sửa lại bình luận
        $this->actingAs($this->admin)
            ->patch(route('admin.reviews.toggleVisibility', $review))
            ->assertRedirect();

        $this->assertFalse($review->fresh()->is_visible);
        $this->assertEquals(0.0, (float) $this->book->fresh()->rating);
        $this->assertEquals(0, $this->book->fresh()->reviews_count);

        $this->actingAs($this->customer)
            ->postJson(route('books.reviews.store', $this->book->slug), [
                'rating' => 5,
                'comment' => 'Cố sửa để hiện lại',
            ])
            ->assertStatus(403);

        $this->actingAs($this->customer)
            ->get(route('books.show', $this->book->slug))
            ->assertOk()
            ->assertSee('Đánh giá của bạn đang bị quản trị viên ẩn')
            ->assertDontSee('Sách rất hay và cảm động!');

        // Hiện lại rồi xóa vĩnh viễn
        $this->actingAs($this->admin)
            ->patch(route('admin.reviews.toggleVisibility', $review))
            ->assertRedirect();
        $this->assertEquals(1, $this->book->fresh()->reviews_count);

        $this->actingAs($this->admin)
            ->delete(route('admin.reviews.destroy', $review))
            ->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertEquals(0, $this->book->fresh()->reviews_count);
    }

    public function test_admin_ai_prepares_confirmation_creates_snapshot_and_undoes_book_update(): void
    {
        Config::set('services.gemini.key', 'fake-gemini-key');

        // 1. Giả lập Gemini gọi tool sửa giá & tồn kho sách
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'functionCall' => [
                                'name' => 'prepare_book_update',
                                'args' => [
                                    'book_id' => $this->book->id,
                                    'price_current' => 120000,
                                    'stock' => 30,
                                ],
                            ],
                        ]],
                    ],
                ]],
            ], 200),
        ]);

        $resp = $this->actingAs($this->admin)
            ->postJson(route('admin.ai.respond'), ['message' => 'Sửa giá sách Mắt Biếc thành 120000 và tồn kho 30']);

        $resp->assertOk()->assertJsonPath('pending_action.action_type', 'update');
        $actionId = $resp->json('pending_action.action_id');
        $this->assertNotEmpty($actionId);

        // Trước khi bấm xác nhận: DB chưa hề thay đổi
        $this->assertEquals(90000, $this->book->fresh()->price_current);
        $this->assertEquals(15, $this->book->fresh()->stock);

        // 2. Xác nhận thực thi -> DB cập nhật + có AdminAiLog snapshot
        $confirm = $this->actingAs($this->admin)
            ->postJson(route('admin.ai.confirm'), ['action_id' => $actionId]);
        $confirm->assertOk()->assertJsonPath('success', true);

        $this->assertEquals(120000, $this->book->fresh()->price_current);
        $this->assertEquals(30, $this->book->fresh()->stock);

        $logId = $confirm->json('log_id');
        $log = AdminAiLog::findOrFail($logId);
        $this->assertEquals('executed', $log->status);
        $this->assertEquals(90000, $log->before_data['price_current']);

        // 3. Hoàn tác (Undo) -> khôi phục đúng giá 90000 và tồn kho 15
        $this->actingAs($this->admin)
            ->postJson(route('admin.ai.undo', $log))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertEquals(90000, $this->book->fresh()->price_current);
        $this->assertEquals(15, $this->book->fresh()->stock);
        $this->assertEquals('undone', $log->fresh()->status);
    }

    public function test_admin_ai_deletes_review_and_undoes_with_rating_recalculation(): void
    {
        Config::set('services.gemini.key', 'fake-gemini-key');

        $review = Review::create([
            'user_id' => $this->customer->id,
            'book_id' => $this->book->id,
            'rating' => 4,
            'comment' => 'Đọc ổn áp',
            'is_visible' => true,
        ]);
        $this->book->updateRatingStats();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'functionCall' => [
                                'name' => 'prepare_review_delete',
                                'args' => ['review_id' => $review->id],
                            ],
                        ]],
                    ],
                ]],
            ], 200),
        ]);

        $delResp = $this->actingAs($this->admin)
            ->postJson(route('admin.ai.respond'), ['message' => 'Xóa bình luận #' . $review->id]);
        $delResp->assertOk()->assertJsonPath('pending_action.action_type', 'delete');
        $delActionId = $delResp->json('pending_action.action_id');

        $delConfirm = $this->actingAs($this->admin)
            ->postJson(route('admin.ai.confirm'), ['action_id' => $delActionId]);
        $delConfirm->assertOk()->assertJsonPath('success', true);
        $delLog = AdminAiLog::findOrFail($delConfirm->json('log_id'));

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertEquals(0, $this->book->fresh()->reviews_count);

        $this->actingAs($this->admin)
            ->postJson(route('admin.ai.undo', $delLog))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'comment' => 'Đọc ổn áp']);
        $this->assertEquals(1, $this->book->fresh()->reviews_count);
    }

    public function test_admin_ai_blocks_write_and_provides_read_fallback_when_api_fails(): void
    {
        Config::set('services.gemini.key', 'fake-gemini-key');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'Service Unavailable'], 503),
        ]);

        // Lệnh xóa khi AI ngoại tuyến -> từ chối an toàn
        $writeResp = $this->actingAs($this->admin)
            ->postJson(route('admin.ai.respond'), ['message' => 'Xóa cuốn sách Mắt Biếc']);

        $writeResp->assertOk()
            ->assertJsonPath('api_error', true)
            ->assertJsonMissingPath('pending_action');

        // Truy vấn đọc thống kê khi AI ngoại tuyến -> fallback nội bộ hoạt động
        $readResp = $this->actingAs($this->admin)
            ->postJson(route('admin.ai.respond'), ['message' => 'Thống kê doanh thu tổng quan']);

        $readResp->assertOk()
            ->assertJsonPath('api_error', true);
        $this->assertStringContainsString('Chế độ dự phòng nội bộ', $readResp->json('reply'));
    }

    public function test_admin_ai_preserves_thought_signature_on_multi_step_query_orders(): void
    {
        Config::set('services.gemini.key', 'fake-gemini-key');

        Order::create([
            'order_number' => 'MNM-SHIP-UNPAID',
            'user_id' => $this->customer->id,
            'customer_name' => 'Khách Đang Giao',
            'customer_email' => 'ship@example.com',
            'customer_phone' => '0901112222',
            'shipping_address' => 'TP.HCM',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => Order::STATUS_SHIPPING,
            'subtotal' => 90000,
            'shipping_fee' => 30000,
            'discount_amount' => 0,
            'total_amount' => 120000,
        ]);

        Http::fakeSequence()
            ->push([
                'candidates' => [[
                    'content' => [
                        'role' => 'model',
                        'parts' => [[
                            'functionCall' => [
                                'name' => 'query_orders',
                                'args' => [
                                    'status' => 'shipping',
                                    'payment_status' => 'pending',
                                ],
                            ],
                            'thoughtSignature' => 'sig_abc_123',
                        ]],
                    ],
                ]],
            ], 200)
            ->push([
                'candidates' => [[
                    'content' => [
                        'role' => 'model',
                        'parts' => [[
                            'text' => 'Hiện có 1 đơn hàng đang giao nhưng chưa thanh toán (MNM-SHIP-UNPAID).',
                        ]],
                    ],
                ]],
            ], 200);

        $resp = $this->actingAs($this->admin)
            ->postJson(route('admin.ai.respond'), ['message' => 'còn bao nhiêu đơn đang giao mà chưa thanh toán']);

        $resp->assertOk()
            ->assertJsonPath('reply', 'Hiện có 1 đơn hàng đang giao nhưng chưa thanh toán (MNM-SHIP-UNPAID).');

        Http::assertSentCount(2);
        $recorded = Http::recorded();
        $secondPayload = $recorded[1][0]->data();

        $this->assertEquals('sig_abc_123', $secondPayload['contents'][1]['parts'][0]['thoughtSignature']);
        $this->assertEquals(1, $secondPayload['contents'][2]['parts'][0]['functionResponse']['response']['total_matching']);
    }
}
