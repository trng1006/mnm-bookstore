@extends('client.layouts.master')

@section('title', 'Thanh toán đơn hàng - MNM Bookstore')

@section('content')
<div class="py-8">
    <!-- Steps Header -->
    <div class="max-w-4xl mx-auto mb-10">
        <div class="flex items-center justify-center gap-4 text-sm font-ui">
            <a href="{{ route('cart.index') }}" class="flex items-center gap-2 text-brand-muted hover:text-brand-accent">
                <span class="w-7 h-7 rounded-full bg-brand-surface text-brand-muted flex items-center justify-center font-bold text-xs border border-brand-border">1</span>
                <span>Giỏ hàng</span>
            </a>
            <span class="w-12 h-0.5 bg-brand-accent"></span>
            <div class="flex items-center gap-2 text-brand-accent font-bold">
                <span class="w-7 h-7 rounded-full bg-brand-accent text-white flex items-center justify-center text-xs shadow-sm">2</span>
                <span>Thanh toán & Giao hàng</span>
            </div>
            <span class="w-12 h-0.5 bg-brand-border"></span>
            <div class="flex items-center gap-2 text-brand-muted">
                <span class="w-7 h-7 rounded-full bg-brand-surface text-brand-muted flex items-center justify-center font-bold text-xs border border-brand-border">3</span>
                <span>Hoàn tất</span>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 text-red-800 border border-red-200 rounded-xl text-sm font-ui max-w-5xl mx-auto">
            <div class="font-bold mb-1">Vui lòng kiểm tra lại thông tin:</div>
            <ul class="list-disc list-inside space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.process') }}" class="max-w-5xl mx-auto">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left: Shipping & Payment Information (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Section 1: Customer & Shipping Details -->
                <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm">
                    <h2 class="font-display text-xl font-bold text-brand-text mb-6 flex items-center gap-2">
                        <span class="w-2 h-6 bg-brand-accent rounded-full"></span>
                        Thông tin giao hàng
                    </h2>

                    <div class="space-y-4 font-ui">
                        <div>
                            <label for="customer_name" class="block text-xs font-bold uppercase tracking-wider text-brand-text mb-1.5">Họ và tên người nhận *</label>
                            <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name', $user->name ?? '') }}" required placeholder="Ví dụ: Nguyễn Văn A" class="w-full bg-brand-surface border border-brand-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-accent focus:bg-white transition-colors">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="customer_phone" class="block text-xs font-bold uppercase tracking-wider text-brand-text mb-1.5">Số điện thoại *</label>
                                <input type="tel" id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $user->phone ?? '') }}" required placeholder="Ví dụ: 0901234567" class="w-full bg-brand-surface border border-brand-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-accent focus:bg-white transition-colors">
                            </div>
                            <div>
                                <label for="customer_email" class="block text-xs font-bold uppercase tracking-wider text-brand-text mb-1.5">Địa chỉ Email *</label>
                                <input type="email" id="customer_email" name="customer_email" value="{{ old('customer_email', $user->email ?? '') }}" required placeholder="email@example.com" class="w-full bg-brand-surface border border-brand-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-accent focus:bg-white transition-colors">
                            </div>
                        </div>

                        <div>
                            <label for="shipping_address" class="block text-xs font-bold uppercase tracking-wider text-brand-text mb-1.5">Địa chỉ nhận hàng chi tiết *</label>
                            <textarea id="shipping_address" name="shipping_address" rows="3" required placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..." class="w-full bg-brand-surface border border-brand-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-accent focus:bg-white transition-colors">{{ old('shipping_address', $user->address ?? '') }}</textarea>
                        </div>

                        <div>
                            <label for="shipping_notes" class="block text-xs font-bold uppercase tracking-wider text-brand-text mb-1.5">Ghi chú cho đơn vị vận chuyển (tùy chọn)</label>
                            <input type="text" id="shipping_notes" name="shipping_notes" value="{{ old('shipping_notes') }}" placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi đến..." class="w-full bg-brand-surface border border-brand-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-accent focus:bg-white transition-colors">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Payment Method -->
                <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm">
                    <h2 class="font-display text-xl font-bold text-brand-text mb-6 flex items-center gap-2">
                        <span class="w-2 h-6 bg-brand-accent rounded-full"></span>
                        Phương thức thanh toán
                    </h2>

                    <div class="space-y-3 font-ui">
                        <!-- COD -->
                        <label class="flex items-start gap-4 p-4 rounded-xl border border-brand-border cursor-pointer hover:border-brand-accent transition-colors bg-brand-surface/40 has-[:checked]:border-brand-accent has-[:checked]:bg-brand-accent/5">
                            <input type="radio" name="payment_method" value="cod" checked class="mt-1 text-brand-accent focus:ring-brand-accent">
                            <div class="flex-1">
                                <div class="font-bold text-sm text-brand-text flex items-center justify-between">
                                    <span>Thanh toán khi nhận hàng (COD)</span>
                                    <span class="text-xs font-normal text-brand-muted">Tiền mặt</span>
                                </div>
                                <p class="text-xs text-brand-muted mt-1">Bạn sẽ thanh toán tiền mặt trực tiếp cho nhân viên giao hàng khi nhận được sách.</p>
                            </div>
                        </label>

                        <!-- Bank Transfer -->
                        <label class="flex items-start gap-4 p-4 rounded-xl border border-brand-border cursor-pointer hover:border-brand-accent transition-colors bg-brand-surface/40 has-[:checked]:border-brand-accent has-[:checked]:bg-brand-accent/5">
                            <input type="radio" name="payment_method" value="bank_transfer" class="mt-1 text-brand-accent focus:ring-brand-accent">
                            <div class="flex-1">
                                <div class="font-bold text-sm text-brand-text flex items-center justify-between">
                                    <span>Chuyển khoản ngân hàng (QR Pay / Banking)</span>
                                    <span class="text-xs font-semibold text-brand-accent">Khuyên dùng</span>
                                </div>
                                <p class="text-xs text-brand-muted mt-1">Chuyển khoản qua số tài khoản hoặc quét mã VietQR nhanh chóng.</p>
                                
                                <div class="mt-3 p-3 bg-white rounded-lg border border-brand-border text-xs space-y-1">
                                    <div class="font-semibold text-brand-text">Ngân hàng MB Bank - Chi nhánh TP.HCM</div>
                                    <div>Số tài khoản: <strong class="text-brand-accent font-mono text-sm">8888 6666 9999</strong></div>
                                    <div>Chủ tài khoản: <strong>CÔNG TY CỔ PHẦN SÁCH MNM</strong></div>
                                    <div class="text-[11px] text-brand-muted">* Nội dung CK: Số điện thoại đặt hàng của bạn</div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Right: Order Items & Submit (5 cols) -->
            <div class="lg:col-span-5 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm font-ui sticky top-28">
                <h3 class="font-display text-lg font-bold text-brand-text mb-4 pb-3 border-b border-brand-border flex items-center justify-between">
                    <span>Đơn hàng của bạn</span>
                    <span class="text-xs font-normal text-brand-muted font-ui">({{ count($cart) }} sản phẩm)</span>
                </h3>

                <!-- Mini Items List -->
                <div class="max-h-60 overflow-y-auto divide-y divide-brand-border/60 mb-6 pr-1">
                    @foreach($cart as $item)
                        <div class="py-3 flex items-center gap-3">
                            <div class="w-12 h-16 bg-brand-surface rounded overflow-hidden flex-shrink-0 border border-brand-border">
                                @if(!empty($item['cover_image']))
                                    <img src="{{ $item['cover_image'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-display text-xs font-bold text-brand-text truncate">{{ $item['title'] }}</h4>
                                <div class="text-[11px] text-brand-muted mt-0.5">Số lượng: {{ $item['quantity'] }}</div>
                            </div>
                            <div class="text-right font-display font-semibold text-sm text-brand-text whitespace-nowrap">
                                {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} ₫
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Totals Breakdown -->
                <div class="space-y-3 text-sm text-brand-muted border-t border-brand-border pt-4 mb-6">
                    <div class="flex justify-between">
                        <span>Tạm tính</span>
                        <span class="font-semibold text-brand-text">{{ number_format($subtotal, 0, ',', '.') }} ₫</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span>Phí giao hàng</span>
                        @if($shippingFee == 0)
                            <span class="font-semibold text-emerald-600">Miễn phí</span>
                        @else
                            <span class="font-semibold text-brand-text">{{ number_format($shippingFee, 0, ',', '.') }} ₫</span>
                        @endif
                    </div>

                    @if($discountAmount > 0)
                        <div class="flex justify-between text-brand-accent">
                            <span>Giảm giá ({{ $coupon['code'] }})</span>
                            <span class="font-bold">-{{ number_format($discountAmount, 0, ',', '.') }} ₫</span>
                        </div>
                    @endif

                    <div class="pt-4 border-t border-brand-border flex justify-between items-baseline">
                        <span class="font-bold text-base text-brand-text">Tổng thanh toán</span>
                        <span class="font-display font-bold text-2xl text-brand-accent">{{ number_format($grandTotal, 0, ',', '.') }} ₫</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full font-ui font-bold text-white bg-brand-accent hover:bg-brand-accent-hover rounded-xl py-4 shadow-soft hover:shadow-hover hover:-translate-y-0.5 transition-all duration-300 text-center text-base cursor-pointer">
                    Xác nhận đặt hàng
                </button>

                <p class="text-[11px] text-center text-brand-muted mt-3 leading-relaxed">
                    Bằng việc bấm đặt hàng, bạn đồng ý với các Điều khoản & Chính sách mua sắm của MNM Bookstore.
                </p>
            </div>
        </div>
    </form>
</div>
@endsection

