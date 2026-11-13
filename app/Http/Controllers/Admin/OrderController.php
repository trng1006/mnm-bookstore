<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng trong Admin Panel
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items'])->latest();

        // Lọc theo trạng thái
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Lọc theo trạng thái thanh toán
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Tìm kiếm theo mã đơn, họ tên, sđt
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(12)->withQueryString();

        $statusCounts = [
            'all' => Order::count(),
            'pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'confirmed' => Order::where('status', Order::STATUS_CONFIRMED)->count(),
            'shipping' => Order::where('status', Order::STATUS_SHIPPING)->count(),
            'completed' => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'cancelled' => Order::where('status', Order::STATUS_CANCELLED)->count(),
        ];

        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }

    /**
     * Chi tiết đơn hàng
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.book']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái đơn hàng và gửi thông báo
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,packing,shipping,completed,cancelled,payment_failed',
            'payment_status' => 'required|in:pending,paid',
            'note' => 'nullable|string|max:1000'
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        if ($oldStatus !== $newStatus) {
            // Kiểm tra transition hợp lệ
            $allowedNext = Order::$allowedTransitions[$oldStatus] ?? [];
            if (!in_array($newStatus, $allowedNext)) {
                return redirect()->back()->with('error', "Không thể chuyển trạng thái từ '{$order->status_label}' sang '" . (new Order(['status' => $newStatus]))->status_label . "'.");
            }
        }
            
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order, $oldStatus, $newStatus, $validated) {
                if ($oldStatus !== $newStatus) {
                    // Tồn kho đã được trừ ngay tại thời điểm khách đặt hàng (Checkout) để chống tranh chấp.
                    // Hoàn lại tồn kho nếu đơn hàng bị hủy hoặc thanh toán thất bại (chỉ hoàn khi trạng thái cũ chưa bị hủy)
                    $isCancellation = in_array($newStatus, [Order::STATUS_CANCELLED, Order::STATUS_PAYMENT_FAILED]);
                    $wasNotCancelled = !in_array($oldStatus, [Order::STATUS_CANCELLED, Order::STATUS_PAYMENT_FAILED]);

                    if ($isCancellation && $wasNotCancelled) {
                        $order->releaseStockAndCoupon();
                    } elseif ($oldStatus === Order::STATUS_PAYMENT_FAILED && $newStatus === Order::STATUS_PENDING) {
                        $order->reserveStockAndCoupon();
                    }
                }

                $order->update([
                    'status' => $newStatus,
                    'payment_status' => $validated['payment_status'],
                ]);

                if ($oldStatus !== $newStatus) {
                    // Tạo lịch sử trạng thái
                    \App\Models\OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'changed_by' => auth()->id(),
                        'from_status' => $oldStatus,
                        'to_status' => $newStatus,
                        'note' => $validated['note'] ?? null,
                    ]);
                }
            });

            // Gửi thông báo (ngoài transaction để không bị rollback nếu gửi mail lỗi hoặc block transaction quá lâu)
            if ($oldStatus !== $newStatus && $order->user) {
                try {
                    $order->user->notify(new OrderStatusUpdatedNotification($order, $oldStatus, $newStatus));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('OrderStatusUpdatedNotification Error: '.$e->getMessage());
                }
            }
            
            return redirect()->back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Update Order Status Error: ' . $e->getMessage());
            
            if (str_starts_with($e->getMessage(), 'InsufficientStock:')) {
                $errorMsg = str_replace('InsufficientStock: ', '', $e->getMessage());
                return redirect()->back()->with('error', $errorMsg);
            }
            
            return redirect()->back()->with('error', 'Đã xảy ra lỗi trong quá trình xử lý đơn hàng. Vui lòng thử lại.');
        }
    }

    public function exportPdf(Order $order)
    {
        $order->load(['user', 'items.book']);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.orders.pdf', compact('order'));
        // Dùng font mặc định có hỗ trợ tiếng Việt nếu được cấu hình, nếu không dùng DejaVu Sans
        $pdf->setOption(['defaultFont' => 'DejaVu Sans']);
        
        return $pdf->download('HoaDon_' . $order->order_number . '.pdf');
    }

    /**
     * Tìm kiếm nhanh đơn hàng qua AJAX (Autocomplete / Live Search)
     */
    public function ajaxSearch(Request $request)
    {
        $keyword = trim($request->get('q') ?? $request->get('search') ?? '');
        $status = $request->get('status', 'all');

        if (empty($keyword) || mb_strlen($keyword) < 2) {
            return response()->json(['orders' => []]);
        }

        $query = Order::with('items')->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $query->where(function ($q) use ($keyword) {
            $q->where('order_number', 'like', "%{$keyword}%")
                ->orWhere('customer_name', 'like', "%{$keyword}%")
                ->orWhere('customer_phone', 'like', "%{$keyword}%")
                ->orWhere('customer_email', 'like', "%{$keyword}%");
        });

        $orders = $query->take(6)->get();

        $formattedOrders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->customer_email,
                'total_amount' => number_format($order->total_amount, 0, ',', '.') . ' ₫',
                'items_count' => $order->items->count(),
                'status' => $order->status,
                'status_label' => $order->status_label,
                'status_badge_class' => $order->status_badge_class,
                'payment_status_label' => $order->payment_status_label,
                'payment_status_badge_class' => $order->payment_status_badge_class,
                'created_at' => $order->created_at->format('d/m/Y H:i'),
                'url' => route('admin.orders.show', $order->id),
            ];
        });

        return response()->json(['orders' => $formattedOrders]);
    }
}
