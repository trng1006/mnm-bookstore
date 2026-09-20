@extends('client.layouts.master')

@section('title', 'Chi tiết đơn hàng #' . $order->order_number . ' - MNM Bookstore')

@section('content')
<div class="py-8 max-w-5xl mx-auto">
    <!-- Top bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('account.profile') }}" class="text-brand-muted hover:text-brand-accent transition-colors p-1" title="Quay lại">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="font-display text-2xl md:text-3xl font-bold text-brand-text">
                    Đơn hàng #{{ $order->order_number }}
                </h1>
                <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $order->status_badge_class }}">
                    {{ $order->status_label }}
                </span>
            </div>
            <p class="font-ui text-xs text-brand-muted mt-1 ml-8">
                Đặt vào lúc {{ $order->created_at->format('d/m/Y H:i') }}
            </p>
        </div>

        @if($order->status === \App\Models\Order::STATUS_PENDING)
            <form method="POST" action="{{ route('account.orders.cancel', $order->order_number) }}">
                @csrf
                <button type="submit" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')" class="font-ui text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-xl px-4 py-2 transition-colors cursor-pointer">
                    Hủy đơn hàng này
                </button>
            </form>
        @endif
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

    <!-- Status Timeline / Stepper -->
    @if($order->status !== \App\Models\Order::STATUS_CANCELLED && $order->status !== \App\Models\Order::STATUS_PAYMENT_FAILED)
        @php
            $steps = [
                'pending' => ['label' => 'Chờ xác nhận', 'icon' => '1'],
                'confirmed' => ['label' => 'Đã xác nhận', 'icon' => '2'],
                'packing' => ['label' => 'Đang đóng gói', 'icon' => '3'],
                'shipping' => ['label' => 'Đang giao hàng', 'icon' => '4'],
                'completed' => ['label' => 'Hoàn thành', 'icon' => '✓'],
            ];
            $orderStatusWeights = [
                'pending' => 1,
                'confirmed' => 2,
                'packing' => 3,
                'shipping' => 4,
                'completed' => 5,
            ];
            $currentWeight = $orderStatusWeights[$order->status] ?? 1;
        @endphp

        <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm mb-8 font-ui">
            <h3 class="text-xs uppercase tracking-wider text-brand-muted font-bold mb-6">Tiến trình vận chuyển</h3>
            <div class="relative max-w-4xl mx-auto">
                <!-- Line aligned to dead-center of 40px circles (top-5 = 20px) -->
                <div class="absolute left-[10%] right-[10%] top-5 -translate-y-1/2 h-1 bg-brand-surface z-0">
                    <div class="h-full bg-brand-accent transition-all duration-700" style="width: {{ (($currentWeight - 1) / 4) * 100 }}%"></div>
                </div>

                <!-- 5 Steps in Equal Columns -->
                <div class="relative z-10 grid grid-cols-5">
                    @foreach($steps as $key => $step)
                        @php
                            $stepWeight = $orderStatusWeights[$key];
                            $isPastOrCurrent = $currentWeight >= $stepWeight;
                            $isCurrent = $currentWeight === $stepWeight;
                        @endphp
                        <div class="flex flex-col items-center text-center px-1">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-300 {{ $isPastOrCurrent ? 'bg-brand-accent text-white shadow-soft' : 'bg-brand-surface border border-brand-border text-brand-muted' }} {{ $isCurrent ? 'ring-4 ring-brand-accent/20 scale-105' : '' }}">
                                {{ $step['icon'] }}
                            </div>
                            <span class="text-xs font-semibold mt-2.5 leading-tight {{ $isCurrent ? 'text-brand-accent' : ($isPastOrCurrent ? 'text-brand-text' : 'text-brand-muted') }}">
                                {{ $step['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="p-6 bg-red-50 border border-red-200 rounded-[var(--radius-card)] mb-8 font-ui flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold text-lg flex-shrink-0">✕</div>
            <div>
                <h4 class="font-bold text-red-800 text-sm">Đơn hàng này đã bị hủy</h4>
                <p class="text-xs text-red-600 mt-0.5">Số lượng sách đã được hoàn trả về kho. Nếu có thắc mắc, vui lòng liên hệ bộ phận chăm sóc khách hàng.</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start font-ui">
        <!-- Left: Order Items (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm">
            <h3 class="font-display text-lg font-bold text-brand-text mb-4 pb-3 border-b border-brand-border">
                Danh sách sách đã mua
            </h3>

            <div class="divide-y divide-brand-border/60">
                @foreach($order->items as $item)
                    <div class="py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-20 bg-brand-surface rounded-lg overflow-hidden flex-shrink-0 border border-brand-border flex items-center justify-center">
                                @if(!empty($item->book_cover))
                                    <img src="{{ $item->book_cover }}" alt="{{ $item->book_title }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-[10px] text-brand-muted">Bìa</span>
                                @endif
                            </div>
                            <div>
                                <h4 class="font-display font-bold text-sm text-brand-text">{{ $item->book_title }}</h4>
                                <div class="text-xs text-brand-muted mt-1">
                                    {{ number_format($item->price, 0, ',', '.') }} ₫ × {{ $item->quantity }}
                                </div>
                            </div>
                        </div>

                        <div class="font-display font-bold text-sm text-brand-accent whitespace-nowrap">
                            {{ number_format($item->total, 0, ',', '.') }} ₫
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Financials -->
            <div class="border-t border-brand-border pt-4 mt-4 space-y-2.5 text-sm text-brand-muted">
                <div class="flex justify-between">
                    <span>Tạm tính</span>
                    <span class="font-semibold text-brand-text">{{ number_format($order->subtotal, 0, ',', '.') }} ₫</span>
                </div>
                <div class="flex justify-between">
                    <span>Phí vận chuyển</span>
                    <span class="font-semibold text-brand-text">{{ $order->shipping_fee == 0 ? 'Miễn phí' : number_format($order->shipping_fee, 0, ',', '.') . ' ₫' }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-brand-accent">
                        <span>Giảm giá @if($order->coupon_code) (Mã: {{ $order->coupon_code }}) @endif</span>
                        <span class="font-bold">-{{ number_format($order->discount_amount, 0, ',', '.') }} ₫</span>
                    </div>
                @endif
                <div class="pt-3 border-t border-brand-border flex justify-between items-baseline">
                    <span class="font-bold text-base text-brand-text">Tổng tiền</span>
                    <span class="font-display font-bold text-2xl text-brand-accent">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</span>
                </div>
            </div>
        </div>

        <!-- Right: Shipping & Payment Info (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Delivery Info -->
            <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-6 shadow-sm">
                <h3 class="font-display text-base font-bold text-brand-text mb-4 pb-3 border-b border-brand-border flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Địa chỉ nhận hàng
                </h3>
                <div class="text-sm space-y-2 text-brand-text">
                    <div class="font-bold">{{ $order->customer_name }}</div>
                    <div class="text-xs text-brand-muted">SĐT: {{ $order->customer_phone }}</div>
                    <div class="text-xs text-brand-muted">Email: {{ $order->customer_email }}</div>
                    <div class="text-xs text-brand-text pt-2 leading-relaxed">{{ $order->shipping_address }}</div>
                    @if($order->shipping_notes)
                        <div class="mt-2 p-2.5 bg-brand-surface rounded-lg text-xs text-brand-muted border border-brand-border/60">
                            <strong>Ghi chú:</strong> {{ $order->shipping_notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment Info -->
            <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-6 shadow-sm">
                <h3 class="font-display text-base font-bold text-brand-text mb-4 pb-3 border-b border-brand-border flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Phương thức thanh toán
                </h3>
                <div class="text-sm space-y-2">
                    <div class="font-semibold text-brand-text">{{ $order->payment_method_label }}</div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-brand-muted">Trạng thái:</span>
                        <span class="px-2 py-0.5 rounded-full font-semibold border {{ $order->payment_status_badge_class }}">
                            {{ $order->payment_status_label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

