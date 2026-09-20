<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Hiển thị giao diện giỏ hàng
     */
    public function showCart()
    {
        $cart = session()->get('cart', []);
        $cartData = $this->calculateCartTotals($cart);

        return view('client.pages.cart.index', array_merge(['cart' => $cart], $cartData));
    }

    /**
     * Thêm sách vào giỏ hàng
     */
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'nullable|integer|min:1',
            'buy_now' => 'nullable|boolean',
        ]);

        /** @var Book $book */
        $book = Book::with('author')->findOrFail($validated['book_id']);
        $quantity = (int) ($validated['quantity'] ?? 1);

        // Kiểm tra tồn kho
        if ($book->stock < $quantity) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rất tiếc, sách chỉ còn '.$book->stock.' cuốn trong kho.',
                ], 422);
            }

            return back()->with('error', 'Rất tiếc, sách chỉ còn '.$book->stock.' cuốn trong kho.');
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$book->id])) {
            $newQuantity = $cart[$book->id]['quantity'] + $quantity;
            if ($newQuantity > $book->stock) {
                $newQuantity = $book->stock;
            }
            $cart[$book->id]['quantity'] = $newQuantity;
        } else {
            $cart[$book->id] = [
                'id' => $book->id,
                'title' => $book->title,
                'slug' => $book->slug,
                'cover_image' => $book->cover_image,
                'author' => $book->author->name ?? 'Chưa rõ tác giả',
                'price' => $book->price_current,
                'stock' => $book->stock,
                'quantity' => $quantity,
            ];
        }

        session()->put('cart', $cart);

        // Cập nhật lại mã giảm giá nếu có
        $this->refreshCouponDiscount($cart);

        $totalItems = array_sum(array_column($cart, 'quantity'));

        // Trường hợp Mua ngay (Buy Now)
        if (! empty($validated['buy_now'])) {
            return redirect()->route('checkout.index');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm "'.$book->title.'" vào giỏ hàng!',
                'totalItems' => $totalItems,
            ]);
        }

        return back()->with('success', 'Đã thêm "'.$book->title.'" vào giỏ hàng thành công!');
    }

    /**
     * Cập nhật số lượng sách trong giỏ
     */
    public function updateCart(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
        ]);

        /** @var Book $book */
        $book = Book::findOrFail($validated['book_id']);
        $quantity = (int) $validated['quantity'];
        $cart = session()->get('cart', []);

        if (isset($cart[$book->id])) {
            if ($quantity > $book->stock) {
                $quantity = $book->stock;
                $message = 'Số lượng đã được giới hạn ở mức tối đa còn trong kho ('.$book->stock.').';
            } else {
                $message = 'Đã cập nhật số lượng sách!';
            }

            $cart[$book->id]['quantity'] = $quantity;
            session()->put('cart', $cart);
            $this->refreshCouponDiscount($cart);
        }

        if ($request->wantsJson()) {
            $cartTotals = $this->calculateCartTotals($cart);

            return response()->json([
                'success' => true,
                'message' => $message ?? 'Đã cập nhật giỏ hàng!',
                'cartTotals' => $cartTotals,
                'itemTotal' => number_format($cart[$book->id]['price'] * $quantity, 0, ',', '.').' ₫',
            ]);
        }

        return back()->with('success', $message ?? 'Đã cập nhật giỏ hàng!');
    }

    /**
     * Xóa 1 sản phẩm khỏi giỏ hàng
     */
    public function removeFromCart($bookId)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$bookId])) {
            unset($cart[$bookId]);
            session()->put('cart', $cart);

            if (empty($cart)) {
                session()->forget('coupon');
            } else {
                $this->refreshCouponDiscount($cart);
            }
        }

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    /**
     * Xóa sạch toàn bộ giỏ hàng
     */
    public function clearCart()
    {
        session()->forget(['cart', 'coupon']);

        return back()->with('success', 'Đã làm trống giỏ hàng.');
    }

    /**
     * Áp dụng mã giảm giá
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ], [
            'coupon_code.required' => 'Vui lòng nhập mã giảm giá.',
        ]);

        $code = strtoupper(trim($request->input('coupon_code')));
        /** @var Coupon|null $coupon */
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return back()->with('error', 'Mã giảm giá không tồn tại hoặc không hợp lệ.');
        }

        $cart = session()->get('cart', []);
        $subtotal = array_sum(array_map(fn ($item) => $item['price'] * $item['quantity'], $cart));

        if ($subtotal < $coupon->min_order_value) {
            return back()->with('error', 'Mã '.$coupon->code.' yêu cầu đơn hàng tối thiểu '.number_format($coupon->min_order_value, 0, ',', '.').' ₫.');
        }

        if (! $coupon->isValid($subtotal)) {
            return back()->with('error', 'Mã giảm giá đã hết hạn hoặc tạm khóa.');
        }

        $discount = $coupon->calculateDiscount($subtotal);

        session()->put('coupon', [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'discount' => $discount,
        ]);

        return back()->with('success', 'Áp dụng mã giảm giá '.$coupon->code.' thành công!');
    }

    /**
     * Hủy mã giảm giá
     */
    public function removeCoupon()
    {
        session()->forget('coupon');

        return back()->with('success', 'Đã hủy áp dụng mã giảm giá.');
    }

    /**
     * Tính toán tổng tiền
     */
    private function calculateCartTotals(array $cart): array
    {
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // Miễn phí ship cho đơn từ 300.000 ₫, bình thường 30.000 ₫
        $shippingFee = ($subtotal >= 300000 || $subtotal === 0) ? 0 : 30000;

        $coupon = session()->get('coupon', null);
        $discountAmount = 0;

        if ($coupon && $subtotal > 0) {
            /** @var Coupon|null $dbCoupon */
            $dbCoupon = Coupon::where('code', $coupon['code'])->first();
            if ($dbCoupon && $dbCoupon->isValid($subtotal)) {
                $discountAmount = $dbCoupon->calculateDiscount($subtotal);
            } else {
                session()->forget('coupon');
                $coupon = null;
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

    /**
     * Tính lại discount nếu số lượng sản phẩm thay đổi
     */
    private function refreshCouponDiscount(array $cart): void
    {
        $coupon = session()->get('coupon');
        if (! $coupon) {
            return;
        }

        $subtotal = array_sum(array_map(fn ($item) => $item['price'] * $item['quantity'], $cart));
        /** @var Coupon|null $dbCoupon */
        $dbCoupon = Coupon::where('code', $coupon['code'])->first();

        if ($dbCoupon && $dbCoupon->isValid($subtotal)) {
            $coupon['discount'] = $dbCoupon->calculateDiscount($subtotal);
            session()->put('coupon', $coupon);
        } else {
            session()->forget('coupon');
        }
    }
}
