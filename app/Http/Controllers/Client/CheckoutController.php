<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    /**
     * Hiển thị giao diện thanh toán
     */
    public function showCheckoutForm()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.show')->with('warning', 'Giỏ hàng của bạn đang trống. Vui lòng chọn sách trước khi thanh toán.');
        }

        /** @var User|null $user */
        $user = auth()->user();
        $cartData = $this->calculateTotals($cart);

        return view('client.pages.checkout.index', array_merge([
            'cart' => $cart,
            'user' => $user,
        ], $cartData));
    }

    /**
     * Xử lý tạo đơn hàng
     */
    public function processCheckout(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.show')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $rules = [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|regex:/^[0-9\-\+\s]{9,15}$/',
            'customer_email' => 'required|email|max:255',
            'shipping_address' => 'required|string|max:500',
            'shipping_notes' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cod,bank_transfer',
        ];

        // Nếu là khách (không đăng nhập), bắt buộc phải chọn bank_transfer (thanh toán online)
        if (!auth()->check()) {
            $rules['payment_method'] = 'required|in:bank_transfer';
        }

        $messages = [
            'customer_name.required' => 'Vui lòng nhập họ và tên người nhận.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại giao hàng.',
            'customer_phone.regex' => 'Số điện thoại không hợp lệ.',
            'customer_email.required' => 'Vui lòng nhập email.',
            'shipping_address.required' => 'Vui lòng nhập địa chỉ giao hàng chi tiết.',
            'payment_method.required' => 'Vui lòng chọn hình thức thanh toán.',
            'payment_method.in' => !auth()->check() ? 'Khách vãng lai bắt buộc phải thanh toán trực tuyến.' : 'Hình thức thanh toán không hợp lệ.',
        ];

        $validated = $request->validate($rules, $messages);

        /** @var User|null $user */
        $user = auth()->user();

        // Lưu cập nhật số điện thoại và địa chỉ mặc định cho user nếu chưa có
        if ($user && (!$user->phone || !$user->address)) {
            $user->update([
                'phone' => $user->phone ?: $validated['customer_phone'],
                'address' => $user->address ?: $validated['shipping_address'],
            ]);
        }

        $totals = $this->calculateTotals($cart);

        DB::beginTransaction();
        try {
            // 1. Tạo đơn hàng
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user ? $user->id : null,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_notes' => $validated['shipping_notes'] ?? null,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'status' => Order::STATUS_PENDING,
                'subtotal' => $totals['subtotal'],
                'shipping_fee' => $totals['shippingFee'],
                'discount_amount' => $totals['discountAmount'],
                'total_amount' => $totals['grandTotal'],
                'coupon_code' => $totals['coupon']['code'] ?? null,
            ]);

            // Increment coupon usage count
            if (!empty($totals['coupon']['code'])) {
                $coupon = Coupon::where('code', $totals['coupon']['code'])->first();
                if ($coupon) {
                    $coupon->increment('used_count');
                }
            }

            // 2. Tạo chi tiết đơn hàng, verify kho & trừ kho
            foreach ($cart as $item) {
                // Lấy sách và lock dòng để tránh race condition
                $book = \App\Models\Book::where('id', $item['id'])->lockForUpdate()->first();
                if (!$book || $book->stock < $item['quantity']) {
                    DB::rollBack();
                    return redirect()->route('cart.show')->with('error', 'Sách "' . $item['title'] . '" không đủ số lượng trong kho. Vui lòng kiểm tra lại!');
                }

                // Trừ tồn kho ngay lúc tạo đơn để tránh khách khác mua trùng
                $book->decrement('stock', $item['quantity']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $item['id'],
                    'book_title' => $item['title'],
                    'book_cover' => $item['cover_image'] ?? null,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['price'] * $item['quantity'],
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: '.$e->getMessage());

            return back()->with('error', 'Có lỗi xảy ra khi tạo đơn hàng. Vui lòng thử lại!')->withInput();
        }

        // 3. (Đã tắt tính năng gửi Email để tối ưu tốc độ đặt hàng cho đồ án)

        // 4. Xóa session giỏ hàng
        session()->forget(['cart', 'coupon']);

        // Nếu thanh toán online, thực tế ta sẽ redirect qua route thanh toán (VNPay), ở đây tạm thời redirect qua success.
        // TODO: Redirect sang VNPay nếu payment_method == 'bank_transfer'
        
        return redirect()->route('checkout.success', $order->order_number)->with('success', 'Đặt hàng thành công!');
    }

    /**
     * Trang hoàn thành đặt hàng
     */
    public function showOrderSuccess(string $orderNumber)
    {
        $query = Order::with('items.book')->where('order_number', $orderNumber);
        
        // Nếu đã đăng nhập, chỉ cho xem đơn của mình. Khách vãng lai thì xem qua URL (order_number)
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->whereNull('user_id');
        }

        $order = $query->firstOrFail();

        return view('client.pages.checkout.success', compact('order'));
    }

    private function calculateTotals(array $cart): array
    {
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shippingFee = ($subtotal >= 300000 || $subtotal === 0) ? 0 : 30000;
        $coupon = session()->get('coupon', null);
        $discountAmount = 0;

        if ($coupon && $subtotal > 0) {
            /** @var Coupon|null $dbCoupon */
            $dbCoupon = Coupon::where('code', $coupon['code'])->first();
            if ($dbCoupon && $dbCoupon->isValid($subtotal)) {
                $discountAmount = $dbCoupon->calculateDiscount($subtotal);
            }
        }

        $grandTotal = max(0, $subtotal + $shippingFee - $discountAmount);

        return [
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'discountAmount' => $discountAmount,
            'grandTotal' => $grandTotal,
            'coupon' => $coupon,
        ];
    }
}
