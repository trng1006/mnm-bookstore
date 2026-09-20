@extends('client.layouts.master')

@section('title', 'Giỏ hàng - MNM Bookstore')

@section('content')
<div class="py-8">
    <div class="mb-8">
        <h1 class="font-display text-3xl font-bold text-brand-text mb-2">Giỏ hàng của bạn</h1>
        <p class="font-ui text-sm text-brand-muted">
            @if(!empty($cart))
                Bạn đang có <span class="font-semibold text-brand-text">{{ count($cart) }}</span> tựa sách trong giỏ hàng.
            @else
                Khám phá thêm nhiều tựa sách hấp dẫn tại MNM Bookstore.
            @endif
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-sm font-ui flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 text-red-800 border border-red-200 rounded-xl text-sm font-ui flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 p-4 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl text-sm font-ui flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    @if(!empty($cart) && count($cart) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left: Cart Items Table (8 cols) -->
            <div class="lg:col-span-8 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left font-ui">
                        <thead>
                            <tr class="border-b border-brand-border text-xs uppercase tracking-wider text-brand-muted pb-4">
                                <th class="pb-4 font-semibold">Sản phẩm</th>
                                <th class="pb-4 font-semibold text-center">Đơn giá</th>
                                <th class="pb-4 font-semibold text-center">Số lượng</th>
                                <th class="pb-4 font-semibold text-right">Thành tiền</th>
                                <th class="pb-4 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border/60">
                            @foreach($cart as $id => $item)
                                <tr class="group">
                                    <!-- Book info -->
                                    <td class="py-5 pr-4">
                                        <div class="flex items-center gap-4">
                                            <a href="{{ route('books.show', $item['slug']) }}" class="w-16 h-22 bg-brand-surface rounded-lg overflow-hidden flex-shrink-0 border border-brand-border flex items-center justify-center">
                                                @if(!empty($item['cover_image']))
                                                    <img src="{{ $item['cover_image'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="text-[10px] text-brand-muted font-ui">Bìa</span>
                                                @endif
                                            </a>
                                            <div>
                                                <a href="{{ route('books.show', $item['slug']) }}" class="font-display font-bold text-brand-text hover:text-brand-accent transition-colors line-clamp-1">
                                                    {{ $item['title'] }}
                                                </a>
                                                <div class="text-xs text-brand-muted mt-0.5">{{ $item['author'] }}</div>
                                                @if(isset($item['stock']) && $item['stock'] <= 5)
                                                    <span class="text-[11px] text-amber-600 font-medium mt-1 block">Chỉ còn {{ $item['stock'] }} cuốn</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Price -->
                                    <td class="py-5 text-center text-sm font-semibold text-brand-text whitespace-nowrap">
                                        {{ number_format($item['price'], 0, ',', '.') }} ₫
                                    </td>

                                    <!-- Quantity Stepper -->
                                    <td class="py-5 text-center">
                                        <form method="POST" action="{{ route('cart.update') }}" class="inline-flex items-center border border-brand-border rounded-lg bg-white overflow-hidden shadow-sm">
                                            @csrf
                                            <input type="hidden" name="book_id" value="{{ $item['id'] }}">
                                            <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}" class="w-8 h-8 flex items-center justify-center text-brand-text hover:bg-brand-surface transition-colors cursor-pointer {{ $item['quantity'] <= 1 ? 'opacity-40 cursor-not-allowed pointer-events-none' : '' }}">−</button>
                                            <span class="w-10 text-center text-sm font-bold text-brand-text">{{ $item['quantity'] }}</span>
                                            <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" class="w-8 h-8 flex items-center justify-center text-brand-text hover:bg-brand-surface transition-colors cursor-pointer {{ (isset($item['stock']) && $item['quantity'] >= $item['stock']) ? 'opacity-40 cursor-not-allowed pointer-events-none' : '' }}">+</button>
                                        </form>
                                    </td>

                                    <!-- Subtotal -->
                                    <td class="py-5 text-right font-display font-bold text-brand-accent whitespace-nowrap text-base">
                                        {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} ₫
                                    </td>

                                    <!-- Delete -->
                                    <td class="py-5 pl-4 text-right">
                                        <form method="POST" action="{{ route('cart.remove', $item['id']) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Bạn có chắc muốn bỏ cuốn sách này khỏi giỏ?')" class="text-brand-muted hover:text-red-500 transition-colors p-1" title="Xóa khỏi giỏ">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Bottom Actions -->
                <div class="flex flex-wrap items-center justify-between gap-4 mt-6 pt-6 border-t border-brand-border">
                    <a href="{{ route('books.index') }}" class="font-ui text-sm font-semibold text-brand-text hover:text-brand-accent flex items-center gap-1.5 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Tiếp tục chọn sách
                    </a>

                    <form method="POST" action="{{ route('cart.clear') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Bạn có chắc muốn làm trống toàn bộ giỏ hàng?')" class="font-ui text-xs text-red-500 hover:text-red-700 font-semibold underline transition-colors cursor-pointer">
                            Làm trống giỏ hàng
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right: Order Summary Sidebar (4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Promo Code Box -->
                <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-6 shadow-sm font-ui">
                    <h3 class="font-display text-base font-bold text-brand-text mb-3">Mã giảm giá</h3>

                    @if(!empty($coupon))
                        <div class="flex items-center justify-between p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm mb-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                <span>Mã: <strong class="uppercase">{{ $coupon['code'] }}</strong></span>
                            </div>
                            <form method="POST" action="{{ route('cart.coupon.remove') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline font-semibold cursor-pointer">Gỡ bỏ</button>
                            </form>
                        </div>
                        <p class="text-xs text-brand-muted">Đã áp dụng giảm {{ number_format($discountAmount, 0, ',', '.') }} ₫ cho đơn hàng này.</p>
                    @else
                        <form method="POST" action="{{ route('cart.coupon.apply') }}" class="flex gap-2">
                            @csrf
                            <input type="text" name="coupon_code" placeholder="Nhập mã (vd: GIAM10)" required class="flex-1 bg-brand-surface border border-brand-border rounded-xl px-3.5 py-2.5 text-sm uppercase font-semibold focus:outline-none focus:border-brand-accent transition-colors">
                            <button type="submit" class="font-bold text-white bg-brand-text hover:bg-brand-accent px-4 py-2.5 rounded-xl text-sm transition-colors shadow-sm whitespace-nowrap cursor-pointer">
                                Áp dụng
                            </button>
                        </form>
                        <div class="mt-3 flex flex-wrap gap-1.5 text-[11px] text-brand-muted">
                            <span>Gợi ý mã:</span>
                            <span class="bg-brand-surface px-2 py-0.5 rounded border border-brand-border text-brand-text font-bold">GIAM10</span>
                            <span class="bg-brand-surface px-2 py-0.5 rounded border border-brand-border text-brand-text font-bold">FREESHIP</span>
                        </div>
                    @endif
                </div>

                <!-- Order Totals Box -->
                <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-6 shadow-sm font-ui">
                    <h3 class="font-display text-lg font-bold text-brand-text mb-4 pb-3 border-b border-brand-border">
                        Tóm tắt đơn hàng
                    </h3>

                    <div class="space-y-3 text-sm text-brand-muted mb-6">
                        <div class="flex justify-between">
                            <span>Tạm tính</span>
                            <span class="font-semibold text-brand-text">{{ number_format($subtotal, 0, ',', '.') }} ₫</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span>Phí vận chuyển</span>
                            @if($shippingFee == 0)
                                <span class="font-semibold text-emerald-600">Miễn phí</span>
                            @else
                                <span class="font-semibold text-brand-text">{{ number_format($shippingFee, 0, ',', '.') }} ₫</span>
                            @endif
                        </div>

                        @if($discountAmount > 0)
                            <div class="flex justify-between text-brand-accent">
                                <span>Giảm giá</span>
                                <span class="font-bold">-{{ number_format($discountAmount, 0, ',', '.') }} ₫</span>
                            </div>
                        @endif

                        <div class="pt-4 border-t border-brand-border flex justify-between items-baseline">
                            <span class="font-bold text-base text-brand-text">Tổng thanh toán</span>
                            <span class="font-display font-bold text-2xl text-brand-accent">{{ number_format($grandTotal, 0, ',', '.') }} ₫</span>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <a href="{{ route('checkout.index') }}" class="w-full flex items-center justify-center gap-2 font-ui font-bold text-white bg-brand-accent hover:bg-brand-accent-hover rounded-xl py-3.5 shadow-soft hover:shadow-hover hover:-translate-y-0.5 transition-all duration-300 text-center">
                        <span>Tiến hành thanh toán</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    @guest
                    <p class="text-[11px] text-center text-brand-muted mt-2">
                        * Khách vãng lai bắt buộc phải thanh toán trực tuyến qua VNPay/MoMo.
                    </p>
                    @endguest

                    <div class="mt-6 pt-6 border-t border-brand-border space-y-2 text-xs text-brand-muted">
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>Miễn phí vận chuyển cho đơn từ 300.000 ₫</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>Giao hàng toàn quốc từ 2 - 4 ngày làm việc</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty Cart State -->
        <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-12 text-center max-w-2xl mx-auto shadow-sm my-8">
            <div class="w-24 h-24 mx-auto mb-6 bg-brand-surface rounded-full flex items-center justify-center text-brand-muted">
                <svg class="w-12 h-12 stroke-current" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h2 class="font-display text-2xl font-bold text-brand-text mb-3">Giỏ hàng của bạn đang trống</h2>
            <p class="font-ui text-brand-muted mb-8 max-w-md mx-auto">Có vẻ như bạn chưa chọn cuốn sách nào. Hãy khám phá kho tàng tri thức phong phú của chúng tôi nhé!</p>
            <a href="{{ route('books.index') }}" class="inline-flex items-center gap-2 font-ui font-bold text-white bg-brand-accent hover:bg-brand-accent-hover px-8 py-3.5 rounded-[var(--radius-button)] shadow-soft hover:shadow-hover hover:-translate-y-0.5 transition-all duration-300">
                <span>Khám phá sách ngay</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>
    @endif
</div>
@endsection

