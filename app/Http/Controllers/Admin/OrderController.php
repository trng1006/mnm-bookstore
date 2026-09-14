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
            'status' => 'required|in:pending,confirmed,shipping,completed,cancelled',
            'payment_status' => 'required|in:pending,paid',
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        $order->update([
            'status' => $newStatus,
            'payment_status' => $validated['payment_status'],
        ]);

        // Nếu trạng thái đơn thay đổi, gửi thông báo cho khách hàng
        if ($oldStatus !== $newStatus && $order->user) {
            try {
                $order->user->notify(new OrderStatusUpdatedNotification($order, $oldStatus, $newStatus));
            } catch (\Exception $e) {
                Log::warning('OrderStatusUpdatedNotification Error: '.$e->getMessage());
            }
        }

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Đã cập nhật trạng thái đơn hàng thành công!');
    }
}
