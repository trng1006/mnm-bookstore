<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Hiển thị thông tin hồ sơ và danh sách đơn hàng
     */
    public function showProfile()
    {
        /** @var User $user */
        $user = auth()->user();
        $orders = $user->orders()->with('items')->paginate(8);

        return view('client.pages.account.profile', compact('user', 'orders'));
    }

    /**
     * Cập nhật thông tin tài khoản cá nhân
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|regex:/^[0-9\-\+\s]{9,15}$/',
            'address' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Họ tên không được để trống.',
            'phone.regex' => 'Số điện thoại không đúng định dạng.',
        ]);

        $user->update($validated);

        return back()->with('success', 'Cập nhật thông tin hồ sơ thành công!');
    }

    /**
     * Xem chi tiết một đơn hàng cụ thể
     */
    public function showOrderDetail(string $orderNumber)
    {
        /** @var Order $order */
        $order = Order::with('items.book')
            ->where('order_number', $orderNumber)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('client.pages.account.order_detail', compact('order'));
    }

    /**
     * Hủy đơn hàng (Chỉ khi đơn hàng đang ở trạng thái Chờ xác nhận)
     */
    public function cancelOrder(string $orderNumber)
    {
        /** @var Order $order */
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->status !== Order::STATUS_PENDING) {
            return back()->with('error', 'Đơn hàng này không thể hủy vì đã được xác nhận hoặc đang vận chuyển.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
            $order->update([
                'status' => Order::STATUS_CANCELLED,
            ]);

            // Hoàn trả lại tồn kho khi khách hàng tự hủy đơn
            foreach ($order->items as $item) {
                if ($item->book_id) {
                    $book = \App\Models\Book::where('id', $item->book_id)->lockForUpdate()->first();
                    if ($book) {
                        $book->increment('stock', $item->quantity);
                    }
                }
            }

            // Tạo lịch sử trạng thái
            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'changed_by' => auth()->id(),
                'from_status' => Order::STATUS_PENDING,
                'to_status' => Order::STATUS_CANCELLED,
                'note' => 'Khách hàng tự hủy đơn',
            ]);
        });

        return back()->with('success', 'Hủy đơn hàng thành công.');
    }

    /**
     * Trang tra cứu đơn hàng công khai
     */
    public function showTrackingForm(Request $request)
    {
        $order = null;
        $searched = false;

        if ($request->filled('order_number') && $request->filled('phone')) {
            $searched = true;
            $orderNumber = trim($request->input('order_number'));
            $phone = trim($request->input('phone'));

            /** @var Order|null $order */
            $order = Order::with('items.book')
                ->where('order_number', $orderNumber)
                ->where('customer_phone', $phone)
                ->first();
        }

        return view('client.pages.orders.tracking', compact('order', 'searched'));
    }
}
