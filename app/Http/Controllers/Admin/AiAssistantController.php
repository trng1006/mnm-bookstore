<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAiLog;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Review;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    private const SESSION_HISTORY_KEY = 'admin_ai_chat_history';
    private const SESSION_PENDING_KEY = 'admin_ai_pending_actions';

    /**
     * Hiển thị trang lịch sử thao tác và hoàn tác (Undo) của AI Admin.
     */
    public function listLogs(Request $request): View
    {
        $query = AdminAiLog::with(['user', 'undoneBy'])->latest();

        if ($request->filled('status') && in_array($request->status, ['executed', 'undone'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        $logs = $query->paginate(15)->withQueryString();

        return view('admin.ai_logs.index', compact('logs'));
    }

    /**
     * Xử lý tin nhắn từ widget AI Admin (hỗ trợ truy vấn ngay + chuẩn bị thẻ xác nhận cho Thêm/Sửa/Xóa).
     */
    public function respond(Request $request): JsonResponse
    {
        $message = trim((string) $request->input('message', ''));
        if ($message === '') {
            return response()->json([
                'reply' => 'Vui lòng nhập yêu cầu hoặc câu hỏi quản trị.',
            ], 422);
        }

        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        if (! $apiKey) {
            return $this->handleApiUnavailable($message, 'Chưa cấu hình GEMINI_API_KEY trong file .env.');
        }

        $history = Session::get(self::SESSION_HISTORY_KEY, []);
        $conversation = $history;
        $conversation[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        // Giữ tối đa 12 lượt gần nhất để tránh tràn context
        if (count($conversation) > 12) {
            $conversation = array_slice($conversation, -12);
        }

        $systemPrompt = $this->buildAdminSystemPrompt();
        $tools = [['functionDeclarations' => $this->getToolDeclarations()]];

        $pendingAction = null;
        $finalReply = null;
        $apiFailed = false;

        // Vòng lặp Function Calling (tối đa 4 bước gọi tool cho mỗi câu hỏi)
        for ($step = 0; $step < 4; $step++) {
            try {
                $response = Http::timeout(25)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key={$apiKey}",
                    [
                        'systemInstruction' => [
                            'parts' => [['text' => $systemPrompt]],
                        ],
                        'contents' => $conversation,
                        'tools' => $tools,
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('Admin AI Gemini connection error: ' . $e->getMessage());
                $apiFailed = true;
                break;
            }

            if (! $response->successful()) {
                Log::warning('Admin AI Gemini error response', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $apiFailed = true;
                break;
            }

            $modelContent = $response->json('candidates.0.content', []);
            $parts = $modelContent['parts'] ?? [];
            if (empty($parts)) {
                $apiFailed = true;
                break;
            }

            // Kiểm tra xem model có yêu cầu gọi tool không (hỗ trợ cả 1 hoặc nhiều functionCall cùng lúc)
            $functionCallParts = [];
            $textParts = [];
            foreach ($parts as $part) {
                if (isset($part['functionCall'])) {
                    $functionCallParts[] = $part['functionCall'];
                } elseif (isset($part['text']) && empty($part['thought'])) {
                    $textParts[] = $part['text'];
                }
            }

            if (empty($functionCallParts)) {
                $finalReply = trim(implode("\n", $textParts));
                break;
            }

            $functionResponseParts = [];
            foreach ($functionCallParts as $functionCallPart) {
                $toolName = (string) ($functionCallPart['name'] ?? '');
                $toolArgs = is_array($functionCallPart['args'] ?? null) ? $functionCallPart['args'] : [];

                $toolResult = $this->executeToolCall($toolName, $toolArgs);

                // Nếu là tool Thêm/Sửa/Xóa hợp lệ -> dừng ngay và trả về thẻ xác nhận cho Admin
                if (isset($toolResult['pending_action'])) {
                    $pendingAction = $toolResult['pending_action'];
                    $finalReply = $toolResult['message'];
                    break 2;
                }

                $functionResponseParts[] = [
                    'functionResponse' => [
                        'name' => $toolName,
                        'response' => $toolResult,
                    ],
                ];
            }

            // Giữ nguyên toàn bộ parts của model (bao gồm thoughtSignature) để Gemini không báo lỗi 400
            $conversation[] = [
                'role' => $modelContent['role'] ?? 'model',
                'parts' => $parts,
            ];
            $conversation[] = [
                'role' => 'user',
                'parts' => $functionResponseParts,
            ];
        }

        if ($apiFailed) {
            return $this->handleApiUnavailable($message, 'Kết nối Gemini AI tạm thời gián đoạn hoặc vượt hạn mức.');
        }

        if ($finalReply === null || $finalReply === '') {
            $finalReply = 'Tôi đã xử lý yêu cầu nhưng chưa nhận được phản hồi rõ ràng từ AI. Bạn vui lòng diễn đạt cụ thể hơn nhé.';
        }

        // Lưu lại lịch sử dạng text thuần gọn nhẹ trong Session
        $history[] = ['role' => 'user', 'parts' => [['text' => $message]]];
        $history[] = ['role' => 'model', 'parts' => [['text' => $finalReply]]];
        if (count($history) > 12) {
            $history = array_slice($history, -12);
        }
        Session::put(self::SESSION_HISTORY_KEY, $history);

        return response()->json([
            'reply' => $finalReply,
            'pending_action' => $pendingAction,
        ]);
    }

    /**
     * Thực thi thao tác Thêm / Sửa / Xóa sau khi Admin bấm nút Xác nhận và lưu snapshot backup.
     */
    public function confirmAction(Request $request): JsonResponse
    {
        $actionId = (string) $request->input('action_id', '');
        $pendingActions = Session::get(self::SESSION_PENDING_KEY, []);

        if ($actionId === '' || ! isset($pendingActions[$actionId])) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu xác nhận không tồn tại hoặc đã hết hạn.',
            ], 404);
        }

        $pending = $pendingActions[$actionId];
        unset($pendingActions[$actionId]);
        Session::put(self::SESSION_PENDING_KEY, $pendingActions);

        if ((int) ($pending['user_id'] ?? 0) !== (int) auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xác nhận thao tác của người dùng khác.',
            ], 403);
        }

        $targetType = (string) $pending['target_type'];
        if (! $this->userCanManageTarget($targetType)) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn không có quyền thực hiện thao tác này.',
            ], 403);
        }

        $orderNotificationData = null;

        try {
            $log = DB::transaction(function () use ($pending, &$orderNotificationData) {
                return $this->applyPendingAction($pending, $orderNotificationData);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể thực thi thao tác: ' . $e->getMessage(),
            ], 422);
        }

        if ($orderNotificationData && $orderNotificationData['order']->user) {
            try {
                $orderNotificationData['order']->user->notify(
                    new OrderStatusUpdatedNotification(
                        $orderNotificationData['order'],
                        $orderNotificationData['old_status'],
                        $orderNotificationData['new_status']
                    )
                );
            } catch (\Throwable $e) {
                Log::warning('Admin AI order notification failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã thực thi thành công: ' . $log->summary,
            'log_id' => $log->id,
            'undo_url' => route('admin.ai.undo', $log),
        ]);
    }

    /**
     * Hủy thao tác đang chờ xác nhận.
     */
    public function cancelAction(Request $request): JsonResponse
    {
        $actionId = (string) $request->input('action_id', '');
        $pendingActions = Session::get(self::SESSION_PENDING_KEY, []);

        if ($actionId !== '' && isset($pendingActions[$actionId])) {
            unset($pendingActions[$actionId]);
            Session::put(self::SESSION_PENDING_KEY, $pendingActions);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy thao tác. Không có dữ liệu nào bị thay đổi.',
        ]);
    }

    /**
     * Xóa lịch sử hội thoại AI của Admin trong Session.
     */
    public function clearHistory(): JsonResponse
    {
        Session::forget([self::SESSION_HISTORY_KEY, self::SESSION_PENDING_KEY]);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa lịch sử trò chuyện AI Admin.',
        ]);
    }

    /**
     * Hoàn tác (Undo) một thao tác đã được AI thực thi dựa trên snapshot `before_data`.
     */
    public function undoAction(Request $request, AdminAiLog $log): JsonResponse|RedirectResponse
    {
        if (! $this->userCanManageTarget($log->target_type)) {
            $msg = 'Bạn không có quyền hoàn tác thao tác trên đối tượng này.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 403)
                : redirect()->back()->with('error', $msg);
        }

        if ($log->status === 'undone') {
            $msg = 'Thao tác này đã được hoàn tác trước đó.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : redirect()->back()->with('error', $msg);
        }

        try {
            DB::transaction(function () use ($log) {
                $this->performUndo($log);
                $log->update([
                    'status' => 'undone',
                    'undone_by' => auth()->id(),
                    'undone_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            $msg = 'Hoàn tác thất bại: ' . $e->getMessage();
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : redirect()->back()->with('error', $msg);
        }

        $msg = 'Đã hoàn tác thành công thao tác #' . $log->id . ': ' . $log->summary;

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $msg])
            : redirect()->back()->with('success', $msg);
    }

    /**
     * Kiểm tra quyền Spatie theo từng nhóm đối tượng.
     */
    private function userCanManageTarget(string $targetType): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return match ($targetType) {
            'book', 'author' => $user->can('manage_books'),
            'category' => $user->can('manage_categories'),
            'coupon', 'order' => $user->can('manage_orders'),
            'review' => $user->can('manage_reviews'),
            'dashboard' => $user->can('view_dashboard'),
            default => false,
        };
    }

    private function buildAdminSystemPrompt(): string
    {
        $user = auth()->user();

        return "Bạn là Trợ lý Quản trị AI nội bộ của MNM Bookstore.\n"
            . "Người đang thao tác: {$user->name} (vai trò: {$user->role}).\n"
            . "NGUYÊN TẮC BẮT BUỘC:\n"
            . "1. Sử dụng các công cụ (tools) được cung cấp để truy vấn dữ liệu thật từ database; tuyệt đối không bịa số liệu hay ID.\n"
            . "2. Khi Admin yêu cầu Thêm, Sửa hoặc Xóa một bản ghi cụ thể, hãy gọi tool `prepare_*` tương ứng. Nếu Admin chưa nêu rõ ID hoặc có nhiều bản ghi trùng tên, hãy gọi tool `query_*` trước để tìm và hỏi lại Admin chọn đúng ID.\n"
            . "3. Chỉ hỗ trợ thao tác trên từng bản ghi cụ thể (không tự ý sửa/xóa hàng loạt).\n"
            . "4. Trả lời bằng tiếng Việt rõ ràng, ngắn gọn, chuyên nghiệp. Không dùng ký tự markdown rối mắt hay biểu tượng cảm xúc.\n";
    }

    /**
     * Khai báo danh sách tools cho Gemini Function Calling.
     */
    private function getToolDeclarations(): array
    {
        return [
            // --- NHÓM TRUY VẤN (READ-ONLY) ---
            [
                'name' => 'get_dashboard_stats',
                'description' => 'Lấy thống kê tổng quan cửa hàng: tổng doanh thu đơn hoàn thành, số đơn theo trạng thái, tổng sách, sách sắp hết hàng, bình luận chưa phản hồi.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => (object) [],
                ],
            ],
            [
                'name' => 'query_books',
                'description' => 'Tìm kiếm hoặc liệt kê sách trong kho theo từ khóa, danh mục, tác giả hoặc tồn kho tối đa.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'keyword' => ['type' => 'STRING', 'description' => 'Từ khóa tên sách hoặc slug'],
                        'category' => ['type' => 'STRING', 'description' => 'Tên danh mục'],
                        'author' => ['type' => 'STRING', 'description' => 'Tên tác giả'],
                        'low_stock_max' => ['type' => 'INTEGER', 'description' => 'Lọc sách có tồn kho <= giá trị này'],
                        'limit' => ['type' => 'INTEGER', 'description' => 'Số lượng kết quả tối đa (mặc định 10)'],
                    ],
                ],
            ],
            [
                'name' => 'query_categories',
                'description' => 'Liệt kê hoặc tìm kiếm danh mục sách.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'keyword' => ['type' => 'STRING', 'description' => 'Từ khóa tên danh mục'],
                    ],
                ],
            ],
            [
                'name' => 'query_authors',
                'description' => 'Liệt kê hoặc tìm kiếm tác giả.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'keyword' => ['type' => 'STRING', 'description' => 'Từ khóa tên tác giả'],
                    ],
                ],
            ],
            [
                'name' => 'query_coupons',
                'description' => 'Liệt kê hoặc tìm kiếm mã giảm giá.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'keyword' => ['type' => 'STRING', 'description' => 'Mã giảm giá cần tìm'],
                        'only_active' => ['type' => 'BOOLEAN', 'description' => 'Chỉ lấy mã đang kích hoạt'],
                    ],
                ],
            ],
            [
                'name' => 'query_reviews',
                'description' => 'Truy vấn bình luận & đánh giá sản phẩm theo trạng thái hiển thị, số sao hoặc từ khóa.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'status' => ['type' => 'STRING', 'description' => 'all, visible, hidden, hoặc unreplied'],
                        'rating' => ['type' => 'INTEGER', 'description' => 'Số sao từ 1 đến 5'],
                        'keyword' => ['type' => 'STRING', 'description' => 'Từ khóa tên sách, khách hàng hoặc nội dung'],
                        'limit' => ['type' => 'INTEGER', 'description' => 'Số lượng tối đa (mặc định 10)'],
                    ],
                ],
            ],
            [
                'name' => 'query_orders',
                'description' => 'Tìm kiếm và đếm đơn hàng theo trạng thái đơn, trạng thái thanh toán, mã đơn hoặc tên/số điện thoại khách hàng.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'status' => ['type' => 'STRING', 'description' => 'pending, confirmed, packing, shipping, completed, cancelled, payment_failed'],
                        'payment_status' => ['type' => 'STRING', 'description' => 'pending (chưa thanh toán), paid (đã thanh toán), failed'],
                        'keyword' => ['type' => 'STRING', 'description' => 'Mã đơn, tên, SĐT hoặc email khách hàng'],
                        'limit' => ['type' => 'INTEGER', 'description' => 'Số lượng tối đa (mặc định 10)'],
                    ],
                ],
            ],

            // --- NHÓM CHUẨN BỊ THAO TÁC GHI (CẦN ADMIN XÁC NHẬN) ---
            [
                'name' => 'prepare_book_create',
                'description' => 'Chuẩn bị thêm một cuốn sách mới (trả về thẻ xác nhận).',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'title' => ['type' => 'STRING', 'description' => 'Tên sách'],
                        'category_id' => ['type' => 'INTEGER', 'description' => 'ID danh mục'],
                        'author_id' => ['type' => 'INTEGER', 'description' => 'ID tác giả'],
                        'price_current' => ['type' => 'INTEGER', 'description' => 'Giá bán hiện tại (VNĐ)'],
                        'stock' => ['type' => 'INTEGER', 'description' => 'Số lượng tồn kho'],
                        'price_old' => ['type' => 'INTEGER', 'description' => 'Giá gốc (tùy chọn)'],
                        'description' => ['type' => 'STRING', 'description' => 'Mô tả ngắn'],
                    ],
                    'required' => ['title', 'category_id', 'author_id', 'price_current'],
                ],
            ],
            [
                'name' => 'prepare_book_update',
                'description' => 'Chuẩn bị cập nhật thông tin một cuốn sách theo book_id.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'book_id' => ['type' => 'INTEGER', 'description' => 'ID sách cần sửa'],
                        'title' => ['type' => 'STRING'],
                        'price_current' => ['type' => 'INTEGER'],
                        'price_old' => ['type' => 'INTEGER'],
                        'stock' => ['type' => 'INTEGER'],
                        'discount_percent' => ['type' => 'INTEGER'],
                        'description' => ['type' => 'STRING'],
                        'category_id' => ['type' => 'INTEGER'],
                        'author_id' => ['type' => 'INTEGER'],
                    ],
                    'required' => ['book_id'],
                ],
            ],
            [
                'name' => 'prepare_book_delete',
                'description' => 'Chuẩn bị xóa một cuốn sách theo book_id.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'book_id' => ['type' => 'INTEGER', 'description' => 'ID sách cần xóa'],
                    ],
                    'required' => ['book_id'],
                ],
            ],
            [
                'name' => 'prepare_category_create',
                'description' => 'Chuẩn bị thêm danh mục sách mới.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'name' => ['type' => 'STRING', 'description' => 'Tên danh mục'],
                        'description' => ['type' => 'STRING', 'description' => 'Mô tả danh mục'],
                    ],
                    'required' => ['name'],
                ],
            ],
            [
                'name' => 'prepare_category_update',
                'description' => 'Chuẩn bị cập nhật danh mục theo category_id.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'category_id' => ['type' => 'INTEGER'],
                        'name' => ['type' => 'STRING'],
                        'description' => ['type' => 'STRING'],
                    ],
                    'required' => ['category_id'],
                ],
            ],
            [
                'name' => 'prepare_category_delete',
                'description' => 'Chuẩn bị xóa danh mục theo category_id (chỉ xóa khi không có sách thuộc danh mục).',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'category_id' => ['type' => 'INTEGER'],
                    ],
                    'required' => ['category_id'],
                ],
            ],
            [
                'name' => 'prepare_author_create',
                'description' => 'Chuẩn bị thêm tác giả mới.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'name' => ['type' => 'STRING', 'description' => 'Tên tác giả'],
                        'bio' => ['type' => 'STRING', 'description' => 'Tiểu sử ngắn'],
                    ],
                    'required' => ['name'],
                ],
            ],
            [
                'name' => 'prepare_author_update',
                'description' => 'Chuẩn bị cập nhật thông tin tác giả theo author_id.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'author_id' => ['type' => 'INTEGER'],
                        'name' => ['type' => 'STRING'],
                        'bio' => ['type' => 'STRING'],
                    ],
                    'required' => ['author_id'],
                ],
            ],
            [
                'name' => 'prepare_author_delete',
                'description' => 'Chuẩn bị xóa tác giả theo author_id (chỉ xóa khi không có sách của tác giả).',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'author_id' => ['type' => 'INTEGER'],
                    ],
                    'required' => ['author_id'],
                ],
            ],
            [
                'name' => 'prepare_coupon_create',
                'description' => 'Chuẩn bị tạo mã giảm giá mới.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'code' => ['type' => 'STRING', 'description' => 'Mã giảm giá (viết hoa, không dấu cách)'],
                        'type' => ['type' => 'STRING', 'description' => 'percent hoặc fixed'],
                        'value' => ['type' => 'INTEGER', 'description' => 'Giá trị giảm (% hoặc VNĐ)'],
                        'min_order_value' => ['type' => 'INTEGER', 'description' => 'Đơn tối thiểu (VNĐ)'],
                        'usage_limit' => ['type' => 'INTEGER', 'description' => 'Giới hạn lượt dùng'],
                        'expires_at' => ['type' => 'STRING', 'description' => 'Ngày hết hạn YYYY-MM-DD'],
                        'is_active' => ['type' => 'BOOLEAN'],
                    ],
                    'required' => ['code', 'type', 'value'],
                ],
            ],
            [
                'name' => 'prepare_coupon_update',
                'description' => 'Chuẩn bị cập nhật mã giảm giá theo coupon_id.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'coupon_id' => ['type' => 'INTEGER'],
                        'type' => ['type' => 'STRING'],
                        'value' => ['type' => 'INTEGER'],
                        'min_order_value' => ['type' => 'INTEGER'],
                        'usage_limit' => ['type' => 'INTEGER'],
                        'expires_at' => ['type' => 'STRING'],
                        'is_active' => ['type' => 'BOOLEAN'],
                    ],
                    'required' => ['coupon_id'],
                ],
            ],
            [
                'name' => 'prepare_coupon_delete',
                'description' => 'Chuẩn bị xóa mã giảm giá theo coupon_id.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'coupon_id' => ['type' => 'INTEGER'],
                    ],
                    'required' => ['coupon_id'],
                ],
            ],
            [
                'name' => 'prepare_review_toggle_visibility',
                'description' => 'Chuẩn bị ẩn hoặc hiện lại một bình luận theo review_id.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'review_id' => ['type' => 'INTEGER'],
                        'is_visible' => ['type' => 'BOOLEAN', 'description' => 'true để hiện, false để ẩn'],
                    ],
                    'required' => ['review_id', 'is_visible'],
                ],
            ],
            [
                'name' => 'prepare_review_reply',
                'description' => 'Chuẩn bị viết hoặc cập nhật phản hồi của cửa hàng cho một bình luận theo review_id.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'review_id' => ['type' => 'INTEGER'],
                        'admin_reply' => ['type' => 'STRING', 'description' => 'Nội dung phản hồi của cửa hàng'],
                    ],
                    'required' => ['review_id', 'admin_reply'],
                ],
            ],
            [
                'name' => 'prepare_review_delete',
                'description' => 'Chuẩn bị xóa một bình luận theo review_id.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'review_id' => ['type' => 'INTEGER'],
                    ],
                    'required' => ['review_id'],
                ],
            ],
            [
                'name' => 'prepare_order_update_status',
                'description' => 'Chuẩn bị cập nhật trạng thái đơn hàng theo order_id hoặc order_number.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'order_id' => ['type' => 'INTEGER', 'description' => 'ID đơn hàng (hoặc truyền order_number)'],
                        'order_number' => ['type' => 'STRING', 'description' => 'Mã đơn hàng như MNM-...'],
                        'status' => ['type' => 'STRING', 'description' => 'pending, confirmed, packing, shipping, completed, cancelled, payment_failed'],
                        'payment_status' => ['type' => 'STRING', 'description' => 'pending hoặc paid (tùy chọn)'],
                        'note' => ['type' => 'STRING', 'description' => 'Ghi chú lịch sử trạng thái'],
                    ],
                    'required' => ['status'],
                ],
            ],
        ];
    }

    /**
     * Điều phối thực thi tool call từ Gemini.
     */
    private function executeToolCall(string $toolName, array $args): array
    {
        return match ($toolName) {
            'get_dashboard_stats' => $this->toolGetDashboardStats(),
            'query_books' => $this->toolQueryBooks($args),
            'query_categories' => $this->toolQueryCategories($args),
            'query_authors' => $this->toolQueryAuthors($args),
            'query_coupons' => $this->toolQueryCoupons($args),
            'query_reviews' => $this->toolQueryReviews($args),
            'query_orders' => $this->toolQueryOrders($args),
            'prepare_book_create' => $this->prepareBookCreate($args),
            'prepare_book_update' => $this->prepareBookUpdate($args),
            'prepare_book_delete' => $this->prepareBookDelete($args),
            'prepare_category_create' => $this->prepareCategoryCreate($args),
            'prepare_category_update' => $this->prepareCategoryUpdate($args),
            'prepare_category_delete' => $this->prepareCategoryDelete($args),
            'prepare_author_create' => $this->prepareAuthorCreate($args),
            'prepare_author_update' => $this->prepareAuthorUpdate($args),
            'prepare_author_delete' => $this->prepareAuthorDelete($args),
            'prepare_coupon_create' => $this->prepareCouponCreate($args),
            'prepare_coupon_update' => $this->prepareCouponUpdate($args),
            'prepare_coupon_delete' => $this->prepareCouponDelete($args),
            'prepare_review_toggle_visibility' => $this->prepareReviewToggleVisibility($args),
            'prepare_review_reply' => $this->prepareReviewReply($args),
            'prepare_review_delete' => $this->prepareReviewDelete($args),
            'prepare_order_update_status' => $this->prepareOrderUpdateStatus($args),
            default => ['error' => 'Công cụ không được hỗ trợ: ' . $toolName],
        };
    }

    // ==================== TOOL TRUY VẤN (READ-ONLY) ====================

    private function toolGetDashboardStats(): array
    {
        if (! $this->userCanManageTarget('dashboard')) {
            return ['error' => 'Bạn không có quyền xem thống kê tổng quan.'];
        }

        return [
            'completed_revenue_vnd' => (int) Order::where('status', Order::STATUS_COMPLETED)->sum('total_amount'),
            'orders_total' => Order::count(),
            'orders_pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'orders_completed' => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'books_total' => Book::count(),
            'books_low_stock_lte_5' => Book::where('stock', '<=', 5)->count(),
            'reviews_total' => Review::count(),
            'reviews_hidden' => Review::where('is_visible', false)->count(),
            'reviews_unreplied' => Review::where(function ($q) {
                $q->whereNull('admin_reply')->orWhere('admin_reply', '');
            })->count(),
        ];
    }

    private function toolQueryBooks(array $args): array
    {
        if (! $this->userCanManageTarget('book')) {
            return ['error' => 'Bạn không có quyền xem dữ liệu sách.'];
        }

        $query = Book::with(['author', 'category']);

        if (! empty($args['keyword'])) {
            $kw = trim((string) $args['keyword']);
            $query->where(function ($q) use ($kw) {
                $q->where('title', 'like', "%{$kw}%")
                    ->orWhere('slug', 'like', "%{$kw}%");
            });
        }

        if (! empty($args['category'])) {
            $cat = trim((string) $args['category']);
            $query->whereHas('category', fn ($q) => $q->where('name', 'like', "%{$cat}%"));
        }

        if (! empty($args['author'])) {
            $auth = trim((string) $args['author']);
            $query->whereHas('author', fn ($q) => $q->where('name', 'like', "%{$auth}%"));
        }

        if (isset($args['low_stock_max'])) {
            $query->where('stock', '<=', (int) $args['low_stock_max'])->orderBy('stock');
        } else {
            $query->latest('id');
        }

        $limit = min(20, max(1, (int) ($args['limit'] ?? 10)));
        $books = $query->take($limit)->get();

        return [
            'count' => $books->count(),
            'books' => $books->map(fn (Book $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author?->name,
                'category' => $b->category?->name,
                'price_current' => (int) $b->price_current,
                'stock' => (int) $b->stock,
                'rating' => (float) $b->rating,
                'reviews_count' => (int) $b->reviews_count,
            ])->values()->all(),
        ];
    }

    private function toolQueryCategories(array $args): array
    {
        if (! $this->userCanManageTarget('category')) {
            return ['error' => 'Bạn không có quyền xem danh mục.'];
        }

        $query = Category::withCount('books');
        if (! empty($args['keyword'])) {
            $kw = trim((string) $args['keyword']);
            $query->where('name', 'like', "%{$kw}%");
        }

        return [
            'categories' => $query->orderBy('name')->take(25)->get()->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'books_count' => $c->books_count,
            ])->values()->all(),
        ];
    }

    private function toolQueryAuthors(array $args): array
    {
        if (! $this->userCanManageTarget('author')) {
            return ['error' => 'Bạn không có quyền xem tác giả.'];
        }

        $query = Author::withCount('books');
        if (! empty($args['keyword'])) {
            $kw = trim((string) $args['keyword']);
            $query->where('name', 'like', "%{$kw}%");
        }

        return [
            'authors' => $query->orderBy('name')->take(25)->get()->map(fn (Author $a) => [
                'id' => $a->id,
                'name' => $a->name,
                'slug' => $a->slug,
                'books_count' => $a->books_count,
            ])->values()->all(),
        ];
    }

    private function toolQueryCoupons(array $args): array
    {
        if (! $this->userCanManageTarget('coupon')) {
            return ['error' => 'Bạn không có quyền xem mã giảm giá.'];
        }

        $query = Coupon::latest('id');
        if (! empty($args['keyword'])) {
            $kw = strtoupper(trim((string) $args['keyword']));
            $query->where('code', 'like', "%{$kw}%");
        }
        if (! empty($args['only_active'])) {
            $query->where('is_active', true);
        }

        return [
            'coupons' => $query->take(20)->get()->map(fn (Coupon $c) => [
                'id' => $c->id,
                'code' => $c->code,
                'type' => $c->type,
                'value' => (int) $c->value,
                'min_order_value' => (int) $c->min_order_value,
                'used_count' => (int) ($c->used_count ?? 0),
                'usage_limit' => $c->usage_limit,
                'is_active' => (bool) $c->is_active,
                'expires_at' => $c->expires_at?->format('Y-m-d'),
            ])->values()->all(),
        ];
    }

    private function toolQueryReviews(array $args): array
    {
        if (! $this->userCanManageTarget('review')) {
            return ['error' => 'Bạn không có quyền xem bình luận.'];
        }

        $query = Review::with(['book', 'user'])->latest('id');
        $status = $args['status'] ?? 'all';
        if ($status === 'visible') {
            $query->where('is_visible', true);
        } elseif ($status === 'hidden') {
            $query->where('is_visible', false);
        } elseif ($status === 'unreplied') {
            $query->where(fn ($q) => $q->whereNull('admin_reply')->orWhere('admin_reply', ''));
        }

        if (isset($args['rating']) && in_array((int) $args['rating'], [1, 2, 3, 4, 5], true)) {
            $query->where('rating', (int) $args['rating']);
        }

        if (! empty($args['keyword'])) {
            $kw = trim((string) $args['keyword']);
            $query->where(function ($q) use ($kw) {
                $q->where('comment', 'like', "%{$kw}%")
                    ->orWhereHas('book', fn ($qb) => $qb->where('title', 'like', "%{$kw}%"))
                    ->orWhereHas('user', fn ($qu) => $qu->where('name', 'like', "%{$kw}%"));
            });
        }

        $limit = min(20, max(1, (int) ($args['limit'] ?? 10)));
        $reviews = $query->take($limit)->get();

        return [
            'count' => $reviews->count(),
            'reviews' => $reviews->map(fn (Review $r) => [
                'id' => $r->id,
                'book' => $r->book?->title,
                'user' => $r->user?->name,
                'rating' => (int) $r->rating,
                'comment' => $r->comment,
                'is_visible' => (bool) $r->is_visible,
                'admin_reply' => $r->admin_reply,
                'created_at' => $r->created_at?->format('Y-m-d H:i'),
            ])->values()->all(),
        ];
    }

    private function toolQueryOrders(array $args): array
    {
        if (! $this->userCanManageTarget('order')) {
            return ['error' => 'Bạn không có quyền xem đơn hàng.'];
        }

        $query = Order::latest('id');
        if (! empty($args['status'])) {
            $query->where('status', $args['status']);
        }
        if (! empty($args['payment_status'])) {
            $query->where('payment_status', $args['payment_status']);
        }
        if (! empty($args['keyword'])) {
            $kw = trim((string) $args['keyword']);
            $query->where(function ($q) use ($kw) {
                $q->where('order_number', 'like', "%{$kw}%")
                    ->orWhere('customer_name', 'like', "%{$kw}%")
                    ->orWhere('customer_phone', 'like', "%{$kw}%");
            });
        }

        $totalMatching = (clone $query)->count();
        $limit = min(20, max(1, (int) ($args['limit'] ?? 10)));
        $orders = $query->take($limit)->get();

        return [
            'total_matching' => $totalMatching,
            'count_returned' => $orders->count(),
            'orders' => $orders->map(fn (Order $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'customer_name' => $o->customer_name,
                'customer_phone' => $o->customer_phone,
                'status' => $o->status,
                'status_label' => $o->status_label,
                'payment_status' => $o->payment_status,
                'total_amount' => (int) $o->total_amount,
                'created_at' => $o->created_at?->format('Y-m-d H:i'),
            ])->values()->all(),
        ];
    }

    // ==================== NHÓM CHUẨN BỊ XÁC NHẬN (PENDING ACTIONS) ====================

    private function storePendingAction(
        string $actionType,
        string $targetType,
        ?int $targetId,
        string $summary,
        array $changes,
        array $payload,
        ?string $warning = null
    ): array {
        $actionId = (string) Str::uuid();
        $pending = [
            'action_id' => $actionId,
            'user_id' => auth()->id(),
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'summary' => $summary,
            'changes' => $changes,
            'payload' => $payload,
            'warning' => $warning,
        ];

        $allPending = Session::get(self::SESSION_PENDING_KEY, []);
        // Chỉ giữ tối đa 10 thẻ chờ gần nhất trong session
        if (count($allPending) >= 10) {
            $allPending = array_slice($allPending, -9, null, true);
        }
        $allPending[$actionId] = $pending;
        Session::put(self::SESSION_PENDING_KEY, $allPending);

        return [
            'message' => 'Đã chuẩn bị thao tác. Vui lòng kiểm tra thông tin bên dưới và bấm Xác nhận để thực thi.',
            'pending_action' => [
                'action_id' => $actionId,
                'action_type' => $actionType,
                'target_type' => $targetType,
                'summary' => $summary,
                'changes' => $changes,
                'warning' => $warning,
            ],
        ];
    }

    private function makeUniqueSlug(string $table, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'item';
        $slug = $base;
        $counter = 1;
        while (
            DB::table($table)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    private function prepareBookCreate(array $args): array
    {
        if (! $this->userCanManageTarget('book')) {
            return ['error' => 'Bạn không có quyền thêm sách.'];
        }

        $title = trim((string) ($args['title'] ?? ''));
        $category = Category::find((int) ($args['category_id'] ?? 0));
        $author = Author::find((int) ($args['author_id'] ?? 0));
        $price = (int) ($args['price_current'] ?? -1);

        if ($title === '' || ! $category || ! $author || $price < 0) {
            return ['error' => 'Thiếu tên sách, category_id, author_id hợp lệ hoặc price_current >= 0.'];
        }

        $slug = $this->makeUniqueSlug('books', $title);
        $stock = max(0, (int) ($args['stock'] ?? 0));
        $payload = [
            'title' => $title,
            'slug' => $slug,
            'category_id' => $category->id,
            'author_id' => $author->id,
            'price_current' => $price,
            'price_old' => isset($args['price_old']) ? max(0, (int) $args['price_old']) : null,
            'stock' => $stock,
            'description' => isset($args['description']) ? (string) $args['description'] : null,
            'is_new' => true,
        ];

        return $this->storePendingAction(
            'create',
            'book',
            null,
            "Thêm sách mới \"{$title}\"",
            [
                'Tên sách' => $title,
                'Danh mục' => "{$category->name} (#{$category->id})",
                'Tác giả' => "{$author->name} (#{$author->id})",
                'Giá bán' => number_format($price, 0, ',', '.') . ' đ',
                'Tồn kho' => (string) $stock,
            ],
            $payload
        );
    }

    private function prepareBookUpdate(array $args): array
    {
        if (! $this->userCanManageTarget('book')) {
            return ['error' => 'Bạn không có quyền sửa sách.'];
        }

        $book = Book::find((int) ($args['book_id'] ?? 0));
        if (! $book) {
            return ['error' => 'Không tìm thấy sách theo book_id cung cấp.'];
        }

        $payload = [];
        $changes = [];

        if (isset($args['title']) && trim((string) $args['title']) !== '') {
            $payload['title'] = trim((string) $args['title']);
            $changes['Tên sách'] = "{$book->title} -> {$payload['title']}";
        }
        if (isset($args['price_current']) && (int) $args['price_current'] >= 0) {
            $payload['price_current'] = (int) $args['price_current'];
            $changes['Giá bán'] = number_format($book->price_current) . ' đ -> ' . number_format($payload['price_current']) . ' đ';
        }
        if (isset($args['price_old']) && (int) $args['price_old'] >= 0) {
            $payload['price_old'] = (int) $args['price_old'];
            $changes['Giá gốc'] = number_format((int) $book->price_old) . ' đ -> ' . number_format($payload['price_old']) . ' đ';
        }
        if (isset($args['stock']) && (int) $args['stock'] >= 0) {
            $payload['stock'] = (int) $args['stock'];
            $changes['Tồn kho'] = "{$book->stock} -> {$payload['stock']}";
        }
        if (isset($args['discount_percent'])) {
            $dp = max(0, min(100, (int) $args['discount_percent']));
            $payload['discount_percent'] = $dp;
            $changes['Giảm giá (%)'] = "{$book->discount_percent}% -> {$dp}%";
        }
        if (isset($args['description'])) {
            $payload['description'] = (string) $args['description'];
            $changes['Mô tả'] = 'Cập nhật nội dung mô tả mới';
        }
        if (isset($args['category_id']) && Category::where('id', (int) $args['category_id'])->exists()) {
            $payload['category_id'] = (int) $args['category_id'];
            $changes['Category ID'] = "{$book->category_id} -> {$payload['category_id']}";
        }
        if (isset($args['author_id']) && Author::where('id', (int) $args['author_id'])->exists()) {
            $payload['author_id'] = (int) $args['author_id'];
            $changes['Author ID'] = "{$book->author_id} -> {$payload['author_id']}";
        }

        if (empty($payload)) {
            return ['error' => 'Không có trường hợp lệ nào cần cập nhật cho sách này.'];
        }

        return $this->storePendingAction(
            'update',
            'book',
            $book->id,
            "Cập nhật sách #{$book->id}: {$book->title}",
            $changes,
            $payload
        );
    }

    private function prepareBookDelete(array $args): array
    {
        if (! $this->userCanManageTarget('book')) {
            return ['error' => 'Bạn không có quyền xóa sách.'];
        }

        $book = Book::withCount(['orderItems', 'reviews'])->find((int) ($args['book_id'] ?? 0));
        if (! $book) {
            return ['error' => 'Không tìm thấy sách cần xóa.'];
        }

        if ($book->order_items_count > 0) {
            return ['error' => "Sách \"{$book->title}\" (#{$book->id}) đã xuất hiện trong {$book->order_items_count} mục đơn hàng nên không được phép xóa. Gợi ý: cập nhật tồn kho về 0 thay vì xóa."];
        }

        $warning = $book->reviews_count > 0
            ? "Sách đang có {$book->reviews_count} đánh giá. Hệ thống sẽ sao lưu đánh giá trước khi xóa để có thể hoàn tác."
            : null;

        return $this->storePendingAction(
            'delete',
            'book',
            $book->id,
            "Xóa sách #{$book->id}: {$book->title}",
            [
                'ID sách' => "#{$book->id}",
                'Tên sách' => $book->title,
                'Giá bán' => number_format($book->price_current) . ' đ',
            ],
            [],
            $warning
        );
    }

    private function prepareCategoryCreate(array $args): array
    {
        if (! $this->userCanManageTarget('category')) {
            return ['error' => 'Bạn không có quyền thêm danh mục.'];
        }

        $name = trim((string) ($args['name'] ?? ''));
        if ($name === '') {
            return ['error' => 'Vui lòng cung cấp tên danh mục.'];
        }

        $slug = $this->makeUniqueSlug('categories', $name);

        return $this->storePendingAction(
            'create',
            'category',
            null,
            "Thêm danh mục mới \"{$name}\"",
            ['Tên danh mục' => $name, 'Slug' => $slug],
            [
                'name' => $name,
                'slug' => $slug,
                'description' => isset($args['description']) ? (string) $args['description'] : null,
            ]
        );
    }

    private function prepareCategoryUpdate(array $args): array
    {
        if (! $this->userCanManageTarget('category')) {
            return ['error' => 'Bạn không có quyền sửa danh mục.'];
        }

        $category = Category::find((int) ($args['category_id'] ?? 0));
        if (! $category) {
            return ['error' => 'Không tìm thấy danh mục.'];
        }

        $payload = [];
        $changes = [];
        if (isset($args['name']) && trim((string) $args['name']) !== '') {
            $payload['name'] = trim((string) $args['name']);
            $payload['slug'] = $this->makeUniqueSlug('categories', $payload['name'], $category->id);
            $changes['Tên danh mục'] = "{$category->name} -> {$payload['name']}";
        }
        if (isset($args['description'])) {
            $payload['description'] = (string) $args['description'];
            $changes['Mô tả'] = 'Cập nhật mô tả mới';
        }

        if (empty($payload)) {
            return ['error' => 'Không có dữ liệu thay đổi cho danh mục.'];
        }

        return $this->storePendingAction(
            'update',
            'category',
            $category->id,
            "Cập nhật danh mục #{$category->id}: {$category->name}",
            $changes,
            $payload
        );
    }

    private function prepareCategoryDelete(array $args): array
    {
        if (! $this->userCanManageTarget('category')) {
            return ['error' => 'Bạn không có quyền xóa danh mục.'];
        }

        $category = Category::withCount('books')->find((int) ($args['category_id'] ?? 0));
        if (! $category) {
            return ['error' => 'Không tìm thấy danh mục.'];
        }

        if ($category->books_count > 0) {
            return ['error' => "Không thể xóa danh mục \"{$category->name}\" vì đang chứa {$category->books_count} cuốn sách."];
        }

        return $this->storePendingAction(
            'delete',
            'category',
            $category->id,
            "Xóa danh mục #{$category->id}: {$category->name}",
            ['ID' => "#{$category->id}", 'Tên danh mục' => $category->name],
            []
        );
    }

    private function prepareAuthorCreate(array $args): array
    {
        if (! $this->userCanManageTarget('author')) {
            return ['error' => 'Bạn không có quyền thêm tác giả.'];
        }

        $name = trim((string) ($args['name'] ?? ''));
        if ($name === '') {
            return ['error' => 'Vui lòng cung cấp tên tác giả.'];
        }

        $slug = $this->makeUniqueSlug('authors', $name);

        return $this->storePendingAction(
            'create',
            'author',
            null,
            "Thêm tác giả mới \"{$name}\"",
            ['Tên tác giả' => $name, 'Slug' => $slug],
            [
                'name' => $name,
                'slug' => $slug,
                'bio' => isset($args['bio']) ? (string) $args['bio'] : null,
            ]
        );
    }

    private function prepareAuthorUpdate(array $args): array
    {
        if (! $this->userCanManageTarget('author')) {
            return ['error' => 'Bạn không có quyền sửa tác giả.'];
        }

        $author = Author::find((int) ($args['author_id'] ?? 0));
        if (! $author) {
            return ['error' => 'Không tìm thấy tác giả.'];
        }

        $payload = [];
        $changes = [];
        if (isset($args['name']) && trim((string) $args['name']) !== '') {
            $payload['name'] = trim((string) $args['name']);
            $payload['slug'] = $this->makeUniqueSlug('authors', $payload['name'], $author->id);
            $changes['Tên tác giả'] = "{$author->name} -> {$payload['name']}";
        }
        if (isset($args['bio'])) {
            $payload['bio'] = (string) $args['bio'];
            $changes['Tiểu sử'] = 'Cập nhật tiểu sử mới';
        }

        if (empty($payload)) {
            return ['error' => 'Không có dữ liệu thay đổi cho tác giả.'];
        }

        return $this->storePendingAction(
            'update',
            'author',
            $author->id,
            "Cập nhật tác giả #{$author->id}: {$author->name}",
            $changes,
            $payload
        );
    }

    private function prepareAuthorDelete(array $args): array
    {
        if (! $this->userCanManageTarget('author')) {
            return ['error' => 'Bạn không có quyền xóa tác giả.'];
        }

        $author = Author::withCount('books')->find((int) ($args['author_id'] ?? 0));
        if (! $author) {
            return ['error' => 'Không tìm thấy tác giả.'];
        }

        if ($author->books_count > 0) {
            return ['error' => "Không thể xóa tác giả \"{$author->name}\" vì đang có {$author->books_count} cuốn sách."];
        }

        return $this->storePendingAction(
            'delete',
            'author',
            $author->id,
            "Xóa tác giả #{$author->id}: {$author->name}",
            ['ID' => "#{$author->id}", 'Tên tác giả' => $author->name],
            []
        );
    }

    private function prepareCouponCreate(array $args): array
    {
        if (! $this->userCanManageTarget('coupon')) {
            return ['error' => 'Bạn không có quyền tạo mã giảm giá.'];
        }

        $code = strtoupper(trim((string) ($args['code'] ?? '')));
        $type = (string) ($args['type'] ?? '');
        $value = (int) ($args['value'] ?? 0);

        if ($code === '' || ! preg_match('/^[A-Z0-9_-]+$/', $code)) {
            return ['error' => 'Mã giảm giá chỉ được chứa chữ cái không dấu, số, dấu gạch ngang hoặc gạch dưới.'];
        }
        if (Coupon::where('code', $code)->exists()) {
            return ['error' => "Mã giảm giá {$code} đã tồn tại."];
        }
        if (! in_array($type, ['percent', 'fixed'], true) || $value < 1) {
            return ['error' => 'Loại giảm giá phải là percent hoặc fixed và giá trị >= 1.'];
        }
        if ($type === 'percent' && $value > 100) {
            return ['error' => 'Giá trị giảm theo phần trăm không được vượt quá 100%.'];
        }

        $payload = [
            'code' => $code,
            'type' => $type,
            'value' => $value,
            'min_order_value' => max(0, (int) ($args['min_order_value'] ?? 0)),
            'usage_limit' => isset($args['usage_limit']) && (int) $args['usage_limit'] > 0 ? (int) $args['usage_limit'] : null,
            'expires_at' => ! empty($args['expires_at']) ? (string) $args['expires_at'] : null,
            'is_active' => array_key_exists('is_active', $args) ? (bool) $args['is_active'] : true,
        ];

        return $this->storePendingAction(
            'create',
            'coupon',
            null,
            "Tạo mã giảm giá {$code}",
            [
                'Mã' => $code,
                'Loại' => $type === 'percent' ? "Giảm {$value}%" : 'Giảm ' . number_format($value) . ' đ',
                'Đơn tối thiểu' => number_format($payload['min_order_value']) . ' đ',
                'Trạng thái' => $payload['is_active'] ? 'Kích hoạt' : 'Tạm khóa',
            ],
            $payload
        );
    }

    private function prepareCouponUpdate(array $args): array
    {
        if (! $this->userCanManageTarget('coupon')) {
            return ['error' => 'Bạn không có quyền sửa mã giảm giá.'];
        }

        $coupon = Coupon::find((int) ($args['coupon_id'] ?? 0));
        if (! $coupon) {
            return ['error' => 'Không tìm thấy mã giảm giá.'];
        }

        $payload = [];
        $changes = [];

        $effectiveType = $args['type'] ?? $coupon->type;
        if (isset($args['type']) && in_array($args['type'], ['percent', 'fixed'], true)) {
            $payload['type'] = $args['type'];
            $changes['Loại'] = "{$coupon->type} -> {$payload['type']}";
        }
        if (isset($args['value']) && (int) $args['value'] >= 1) {
            $val = (int) $args['value'];
            if ($effectiveType === 'percent' && $val > 100) {
                return ['error' => 'Phần trăm giảm tối đa là 100%.'];
            }
            $payload['value'] = $val;
            $changes['Giá trị'] = "{$coupon->value} -> {$val}";
        }
        if (isset($args['min_order_value']) && (int) $args['min_order_value'] >= 0) {
            $payload['min_order_value'] = (int) $args['min_order_value'];
            $changes['Đơn tối thiểu'] = number_format($payload['min_order_value']) . ' đ';
        }
        if (array_key_exists('is_active', $args)) {
            $payload['is_active'] = (bool) $args['is_active'];
            $changes['Trạng thái'] = $payload['is_active'] ? 'Kích hoạt' : 'Tạm khóa';
        }
        if (isset($args['expires_at'])) {
            $payload['expires_at'] = $args['expires_at'] ?: null;
            $changes['Hết hạn'] = $payload['expires_at'] ?? 'Không giới hạn';
        }

        if (empty($payload)) {
            return ['error' => 'Không có trường thay đổi hợp lệ cho mã giảm giá.'];
        }

        return $this->storePendingAction(
            'update',
            'coupon',
            $coupon->id,
            "Cập nhật mã giảm giá {$coupon->code} (#{$coupon->id})",
            $changes,
            $payload
        );
    }

    private function prepareCouponDelete(array $args): array
    {
        if (! $this->userCanManageTarget('coupon')) {
            return ['error' => 'Bạn không có quyền xóa mã giảm giá.'];
        }

        $coupon = Coupon::find((int) ($args['coupon_id'] ?? 0));
        if (! $coupon) {
            return ['error' => 'Không tìm thấy mã giảm giá.'];
        }

        return $this->storePendingAction(
            'delete',
            'coupon',
            $coupon->id,
            "Xóa mã giảm giá {$coupon->code} (#{$coupon->id})",
            ['Mã' => $coupon->code, 'ID' => "#{$coupon->id}"],
            []
        );
    }

    private function prepareReviewToggleVisibility(array $args): array
    {
        if (! $this->userCanManageTarget('review')) {
            return ['error' => 'Bạn không có quyền kiểm duyệt bình luận.'];
        }

        $review = Review::with(['book', 'user'])->find((int) ($args['review_id'] ?? 0));
        if (! $review) {
            return ['error' => 'Không tìm thấy bình luận theo review_id.'];
        }

        $isVisible = (bool) ($args['is_visible'] ?? false);
        $label = $isVisible ? 'Hiển thị lại' : 'Ẩn khỏi trang sách';

        return $this->storePendingAction(
            'update',
            'review',
            $review->id,
            "{$label} bình luận #{$review->id} của " . ($review->user?->name ?? 'Khách'),
            [
                'Sách' => $review->book?->title ?? 'N/A',
                'Bình luận' => Str::limit($review->comment, 90),
                'Hiển thị' => ($review->is_visible ? 'Đang hiện' : 'Đang ẩn') . ' -> ' . ($isVisible ? 'Hiện' : 'Ẩn'),
            ],
            ['is_visible' => $isVisible]
        );
    }

    private function prepareReviewReply(array $args): array
    {
        if (! $this->userCanManageTarget('review')) {
            return ['error' => 'Bạn không có quyền phản hồi bình luận.'];
        }

        $review = Review::with(['book', 'user'])->find((int) ($args['review_id'] ?? 0));
        $replyText = trim((string) ($args['admin_reply'] ?? ''));

        if (! $review) {
            return ['error' => 'Không tìm thấy bình luận theo review_id.'];
        }
        if (mb_strlen($replyText) < 2) {
            return ['error' => 'Nội dung phản hồi tối thiểu 2 ký tự.'];
        }

        return $this->storePendingAction(
            'update',
            'review',
            $review->id,
            "Phản hồi bình luận #{$review->id} cho sách " . ($review->book?->title ?? 'N/A'),
            [
                'Khách hàng' => $review->user?->name ?? 'N/A',
                'Nội dung gốc' => Str::limit($review->comment, 90),
                'Phản hồi của shop' => $replyText,
            ],
            ['admin_reply' => $replyText]
        );
    }

    private function prepareReviewDelete(array $args): array
    {
        if (! $this->userCanManageTarget('review')) {
            return ['error' => 'Bạn không có quyền xóa bình luận.'];
        }

        $review = Review::with(['book', 'user'])->find((int) ($args['review_id'] ?? 0));
        if (! $review) {
            return ['error' => 'Không tìm thấy bình luận theo review_id.'];
        }

        return $this->storePendingAction(
            'delete',
            'review',
            $review->id,
            "Xóa bình luận #{$review->id}",
            [
                'Sách' => $review->book?->title ?? 'N/A',
                'Khách hàng' => $review->user?->name ?? 'N/A',
                'Nội dung' => Str::limit($review->comment, 90),
            ],
            []
        );
    }

    private function prepareOrderUpdateStatus(array $args): array
    {
        if (! $this->userCanManageTarget('order')) {
            return ['error' => 'Bạn không có quyền cập nhật đơn hàng.'];
        }

        $order = null;
        if (! empty($args['order_id'])) {
            $order = Order::find((int) $args['order_id']);
        } elseif (! empty($args['order_number'])) {
            $order = Order::where('order_number', trim((string) $args['order_number']))->first();
        }

        if (! $order) {
            return ['error' => 'Không tìm thấy đơn hàng theo ID hoặc mã đơn cung cấp.'];
        }

        $newStatus = (string) ($args['status'] ?? '');
        $validStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PACKING,
            Order::STATUS_SHIPPING,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
            Order::STATUS_PAYMENT_FAILED,
        ];

        if (! in_array($newStatus, $validStatuses, true)) {
            return ['error' => 'Trạng thái đơn hàng không hợp lệ.'];
        }

        if ($order->status !== $newStatus) {
            $allowedNext = Order::$allowedTransitions[$order->status] ?? [];
            if (! in_array($newStatus, $allowedNext, true)) {
                return ['error' => "Không thể chuyển đơn #{$order->order_number} từ '{$order->status_label}' sang '{$newStatus}' theo quy trình đơn hàng."];
            }
        }

        $paymentStatus = isset($args['payment_status']) && in_array($args['payment_status'], ['pending', 'paid'], true)
            ? $args['payment_status']
            : $order->payment_status;

        $note = isset($args['note']) ? trim((string) $args['note']) : 'Cập nhật qua Trợ lý AI Admin';

        return $this->storePendingAction(
            'update',
            'order',
            $order->id,
            "Cập nhật đơn {$order->order_number} sang trạng thái {$newStatus}",
            [
                'Mã đơn' => $order->order_number,
                'Trạng thái đơn' => "{$order->status} -> {$newStatus}",
                'Thanh toán' => "{$order->payment_status} -> {$paymentStatus}",
                'Ghi chú' => $note,
            ],
            [
                'status' => $newStatus,
                'payment_status' => $paymentStatus,
                'note' => $note,
            ]
        );
    }

    // ==================== THỰC THI & HOÀN TÁC (WITH SNAPSHOT) ====================

    private function applyPendingAction(array $pending, ?array &$orderNotificationData): AdminAiLog
    {
        $actionType = $pending['action_type'];
        $targetType = $pending['target_type'];
        $targetId = $pending['target_id'];
        $payload = $pending['payload'];

        $beforeData = null;
        $afterData = null;

        if ($targetType === 'book') {
            if ($actionType === 'create') {
                $book = Book::create($payload);
                $targetId = $book->id;
                $afterData = $book->fresh()->getAttributes();
            } elseif ($actionType === 'update') {
                $book = Book::findOrFail($targetId);
                $beforeData = $book->getAttributes();
                $book->update($payload);
                $afterData = $book->fresh()->getAttributes();
            } elseif ($actionType === 'delete') {
                $book = Book::findOrFail($targetId);
                $beforeData = [
                    'record' => $book->getAttributes(),
                    'reviews' => $book->reviews()->get()->map(fn (Review $r) => $r->getAttributes())->all(),
                ];
                $book->delete();
            }
        } elseif ($targetType === 'category') {
            if ($actionType === 'create') {
                $cat = Category::create($payload);
                $targetId = $cat->id;
                $afterData = $cat->fresh()->getAttributes();
            } elseif ($actionType === 'update') {
                $cat = Category::findOrFail($targetId);
                $beforeData = $cat->getAttributes();
                $cat->update($payload);
                $afterData = $cat->fresh()->getAttributes();
            } elseif ($actionType === 'delete') {
                $cat = Category::findOrFail($targetId);
                if ($cat->books()->count() > 0) {
                    throw new \RuntimeException('Danh mục vẫn còn sách, không thể xóa.');
                }
                $beforeData = $cat->getAttributes();
                $cat->delete();
            }
        } elseif ($targetType === 'author') {
            if ($actionType === 'create') {
                $author = Author::create($payload);
                $targetId = $author->id;
                $afterData = $author->fresh()->getAttributes();
            } elseif ($actionType === 'update') {
                $author = Author::findOrFail($targetId);
                $beforeData = $author->getAttributes();
                $author->update($payload);
                $afterData = $author->fresh()->getAttributes();
            } elseif ($actionType === 'delete') {
                $author = Author::findOrFail($targetId);
                if ($author->books()->count() > 0) {
                    throw new \RuntimeException('Tác giả vẫn còn sách, không thể xóa.');
                }
                $beforeData = $author->getAttributes();
                $author->delete();
            }
        } elseif ($targetType === 'coupon') {
            if ($actionType === 'create') {
                $coupon = Coupon::create($payload);
                $targetId = $coupon->id;
                $afterData = $coupon->fresh()->getAttributes();
            } elseif ($actionType === 'update') {
                $coupon = Coupon::findOrFail($targetId);
                $beforeData = $coupon->getAttributes();
                $coupon->update($payload);
                $afterData = $coupon->fresh()->getAttributes();
            } elseif ($actionType === 'delete') {
                $coupon = Coupon::findOrFail($targetId);
                $beforeData = $coupon->getAttributes();
                $coupon->delete();
            }
        } elseif ($targetType === 'review') {
            $review = Review::findOrFail($targetId);
            $beforeData = $review->getAttributes();
            $book = $review->book;

            if ($actionType === 'update') {
                $updateFields = $payload;
                if (array_key_exists('admin_reply', $payload)) {
                    $updateFields['replied_by'] = auth()->id();
                    $updateFields['replied_at'] = now();
                }
                $review->update($updateFields);
                $afterData = $review->fresh()->getAttributes();
            } elseif ($actionType === 'delete') {
                $review->delete();
            }

            $book?->updateRatingStats();
        } elseif ($targetType === 'order') {
            $order = Order::findOrFail($targetId);
            $beforeData = $order->getAttributes();

            $oldStatus = $order->status;
            $newStatus = $payload['status'];

            if ($oldStatus !== $newStatus) {
                $allowedNext = Order::$allowedTransitions[$oldStatus] ?? [];
                if (! in_array($newStatus, $allowedNext, true)) {
                    throw new \RuntimeException('Trạng thái đơn hàng đã thay đổi, không thể chuyển tiếp.');
                }

                $isCancellation = in_array($newStatus, [Order::STATUS_CANCELLED, Order::STATUS_PAYMENT_FAILED], true);
                $wasNotCancelled = ! in_array($oldStatus, [Order::STATUS_CANCELLED, Order::STATUS_PAYMENT_FAILED], true);

                if ($isCancellation && $wasNotCancelled) {
                    $order->releaseStockAndCoupon();
                } elseif ($oldStatus === Order::STATUS_PAYMENT_FAILED && $newStatus === Order::STATUS_PENDING) {
                    $order->reserveStockAndCoupon();
                }
            }

            $order->update([
                'status' => $newStatus,
                'payment_status' => $payload['payment_status'],
            ]);

            if ($oldStatus !== $newStatus) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'changed_by' => auth()->id(),
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'note' => $payload['note'] ?? null,
                ]);

                $orderNotificationData = [
                    'order' => $order->fresh(),
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ];
            }

            $afterData = $order->fresh()->getAttributes();
        }

        return AdminAiLog::create([
            'user_id' => auth()->id(),
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'summary' => $pending['summary'],
            'before_data' => $beforeData,
            'after_data' => $afterData,
            'status' => 'executed',
        ]);
    }

    private function performUndo(AdminAiLog $log): void
    {
        $before = $log->before_data ?? [];
        $targetId = $log->target_id;

        if ($log->target_type === 'book') {
            if ($log->action_type === 'create') {
                $book = Book::withCount('orderItems')->findOrFail($targetId);
                if ($book->order_items_count > 0) {
                    throw new \RuntimeException('Sách mới tạo đã phát sinh đơn hàng, không thể hoàn tác xóa.');
                }
                $book->delete();
            } elseif ($log->action_type === 'update') {
                $book = Book::findOrFail($targetId);
                $book->update($before);
            } elseif ($log->action_type === 'delete') {
                $record = $before['record'] ?? $before;
                Book::create($record);
                foreach ($before['reviews'] ?? [] as $rev) {
                    Review::create($rev);
                }
                Book::find($record['id'])?->updateRatingStats();
            }
            return;
        }

        if ($log->target_type === 'category') {
            if ($log->action_type === 'create') {
                $cat = Category::withCount('books')->findOrFail($targetId);
                if ($cat->books_count > 0) {
                    throw new \RuntimeException('Danh mục đã có sách, không thể hoàn tác xóa.');
                }
                $cat->delete();
            } elseif ($log->action_type === 'update') {
                Category::findOrFail($targetId)->update($before);
            } elseif ($log->action_type === 'delete') {
                Category::create($before);
            }
            return;
        }

        if ($log->target_type === 'author') {
            if ($log->action_type === 'create') {
                $author = Author::withCount('books')->findOrFail($targetId);
                if ($author->books_count > 0) {
                    throw new \RuntimeException('Tác giả đã có sách, không thể hoàn tác xóa.');
                }
                $author->delete();
            } elseif ($log->action_type === 'update') {
                Author::findOrFail($targetId)->update($before);
            } elseif ($log->action_type === 'delete') {
                Author::create($before);
            }
            return;
        }

        if ($log->target_type === 'coupon') {
            if ($log->action_type === 'create') {
                Coupon::findOrFail($targetId)->delete();
            } elseif ($log->action_type === 'update') {
                Coupon::findOrFail($targetId)->update($before);
            } elseif ($log->action_type === 'delete') {
                Coupon::create($before);
            }
            return;
        }

        if ($log->target_type === 'review') {
            if ($log->action_type === 'update') {
                $review = Review::findOrFail($targetId);
                $review->update($before);
                $review->book?->updateRatingStats();
            } elseif ($log->action_type === 'delete') {
                $review = Review::create($before);
                $review->book?->updateRatingStats();
            }
            return;
        }

        if ($log->target_type === 'order' && $log->action_type === 'update') {
            $order = Order::findOrFail($targetId);
            $currentStatus = $order->status;
            $previousStatus = $before['status'];

            $isCurrentlyCancelled = in_array($currentStatus, [Order::STATUS_CANCELLED, Order::STATUS_PAYMENT_FAILED], true);
            $wasBeforeCancelled = in_array($previousStatus, [Order::STATUS_CANCELLED, Order::STATUS_PAYMENT_FAILED], true);

            if ($isCurrentlyCancelled && ! $wasBeforeCancelled) {
                $order->reserveStockAndCoupon(true);
            } elseif (! $isCurrentlyCancelled && $wasBeforeCancelled) {
                $order->releaseStockAndCoupon();
            }

            $order->update([
                'status' => $previousStatus,
                'payment_status' => $before['payment_status'],
            ]);

            if ($currentStatus !== $previousStatus) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'changed_by' => auth()->id(),
                    'from_status' => $currentStatus,
                    'to_status' => $previousStatus,
                    'note' => "Hoàn tác thao tác AI #{$log->id}",
                ]);
            }
        }
    }

    // ==================== FALLBACK ĐỌC KHI API LỖI ====================

    /**
     * Khi Gemini API lỗi hoặc chưa cấu hình: chỉ hỗ trợ truy vấn đọc cơ bản, tuyệt đối không đoán lệnh Thêm/Sửa/Xóa.
     */
    private function handleApiUnavailable(string $message, string $reason): JsonResponse
    {
        $lower = mb_strtolower($message, 'UTF-8');

        // Nhận diện ý định ghi/sửa/xóa -> từ chối an toàn
        $writeKeywords = ['thêm', 'tạo', 'sửa', 'cập nhật', 'đổi', 'xóa', 'ẩn', 'hiện', 'trả lời', 'phản hồi'];
        foreach ($writeKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return response()->json([
                    'reply' => "{$reason} Hệ thống tuyệt đối không tự đoán lệnh Thêm/Sửa/Xóa khi AI ngoại tuyến để đảm bảo an toàn dữ liệu. Vui lòng thử lại hoặc thao tác trên menu quản lý.",
                    'api_error' => true,
                ]);
            }
        }

        // Fallback đọc an toàn cho các câu hỏi thống kê / tra cứu nhanh
        if (str_contains($lower, 'thống kê') || str_contains($lower, 'doanh thu') || str_contains($lower, 'tổng quan')) {
            if ($this->userCanManageTarget('dashboard')) {
                $stats = $this->toolGetDashboardStats();
                $rev = number_format($stats['completed_revenue_vnd'], 0, ',', '.');
                return response()->json([
                    'reply' => "[Chế độ dự phòng nội bộ]\n"
                        . "- Doanh thu đơn hoàn thành: {$rev} đ\n"
                        . "- Tổng đơn hàng: {$stats['orders_total']} (chờ xác nhận: {$stats['orders_pending']})\n"
                        . "- Tổng sách: {$stats['books_total']} (sắp hết <= 5: {$stats['books_low_stock_lte_5']})\n"
                        . "- Bình luận chưa phản hồi: {$stats['reviews_unreplied']}",
                    'api_error' => true,
                ]);
            }
        }

        if (str_contains($lower, 'bình luận') || str_contains($lower, 'đánh giá')) {
            if ($this->userCanManageTarget('review')) {
                $res = $this->toolQueryReviews(['status' => 'unreplied', 'limit' => 5]);
                $lines = ["[Chế độ dự phòng nội bộ] 5 bình luận chưa phản hồi mới nhất:"];
                foreach ($res['reviews'] as $r) {
                    $lines[] = "- #{$r['id']} | {$r['book']} | {$r['rating']} sao | {$r['comment']}";
                }
                return response()->json([
                    'reply' => implode("\n", $lines),
                    'api_error' => true,
                ]);
            }
        }

        return response()->json([
            'reply' => "{$reason} Vui lòng kiểm tra kết nối hoặc bấm Thử lại.",
            'api_error' => true,
        ]);
    }
}
