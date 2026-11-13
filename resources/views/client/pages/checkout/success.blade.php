@extends('client.layouts.master')

@section('title', 'Đặt hàng thành công - MNM Bookstore')

@section('content')
<div class="py-12 max-w-3xl mx-auto">
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 text-red-800 border border-red-200 rounded-xl text-sm font-ui">
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 p-4 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl text-sm font-ui">
            {{ session('warning') }}
        </div>
    @endif

    <!-- Success Banner Card -->
    <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-8 md:p-12 shadow-sm text-center mb-8">
        <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
        </div>

        <h1 class="font-display text-3xl font-bold text-brand-text mb-2">Đặt hàng thành công!</h1>
        <p class="font-ui text-brand-muted mb-6">Cảm ơn bạn đã tin tưởng và mua sắm tại MNM Bookstore. Chúng tôi đã nhận được đơn hàng của bạn.</p>

        <!-- Order Number Badge -->
        <div class="inline-flex items-center gap-3 px-6 py-3 bg-brand-surface rounded-2xl border border-brand-border mb-8">
            <span class="text-xs font-ui text-brand-muted uppercase tracking-wider">Mã đơn hàng:</span>
            <span class="font-mono text-lg font-bold text-brand-accent tracking-wide">{{ $order->order_number }}</span>
        </div>

        <!-- Details Grid -->
        <div class="bg-brand-surface/60 rounded-2xl p-6 text-left font-ui text-sm border border-brand-border/60 mb-8 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4 border-b border-brand-border/60">
                <div>
                    <span class="text-xs text-brand-muted block">Người nhận hàng</span>
                    <strong class="text-brand-text">{{ $order->customer_name }}</strong> ({{ $order->customer_phone }})
                </div>
                <div>
                    <span class="text-xs text-brand-muted block">Phương thức thanh toán</span>
                    <strong class="text-brand-text">{{ $order->payment_method_label }}</strong>
                    <span class="ml-2 inline-block px-2 py-0.5 rounded-full text-xs font-semibold border {{ $order->payment_status_badge_class }}">
                        {{ $order->payment_status_label }}
                    </span>
                </div>
            </div>

            <div class="pb-4 border-b border-brand-border/60">
                <span class="text-xs text-brand-muted block">Địa chỉ giao hàng</span>
                <span class="text-brand-text">{{ $order->shipping_address }}</span>
            </div>

            <div class="flex justify-between items-baseline pt-1">
                <span class="font-bold text-brand-text">Tổng thanh toán:</span>
                <span class="font-display text-2xl font-bold text-brand-accent">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</span>
            </div>
        </div>

        <!-- Ordered Items Preview -->
        <div class="text-left mb-8">
            <h3 class="font-display font-bold text-base text-brand-text mb-4">Các tựa sách đã đặt:</h3>
            <div class="divide-y divide-brand-border/60 border border-brand-border rounded-xl overflow-hidden bg-white">
                @foreach($order->items as $item)
                    <div class="p-3.5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-14 bg-brand-surface rounded overflow-hidden flex-shrink-0 border border-brand-border">
                                @if(!empty($item->book_cover))
                                    <img src="{{ $item->book_cover }}" alt="{{ $item->book_title }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div>
                                <h4 class="font-ui text-xs font-bold text-brand-text">{{ $item->book_title }}</h4>
                                <span class="text-[11px] text-brand-muted font-ui">Số lượng: {{ $item->quantity }} × {{ number_format($item->price, 0, ',', '.') }} ₫</span>
                            </div>
                        </div>
                        <div class="font-display font-semibold text-sm text-brand-text whitespace-nowrap">
                            {{ number_format($item->total, 0, ',', '.') }} ₫
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 font-ui">
            @if($order->payment_method === 'bank_transfer' && $order->payment_status !== 'paid' && $order->status !== \App\Models\Order::STATUS_CANCELLED && $order->total_amount > 0)
                <a href="{{ route('checkout.payos.pay', $order->order_number) }}" class="w-full sm:w-auto inline-flex items-center justify-center font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-6 py-3.5 rounded-xl shadow-soft hover:shadow-hover hover:-translate-y-0.5 transition-all duration-300">
                    Thanh toán ngay qua QR (payOS)
                </a>
            @endif
            <a href="{{ route('account.orders.show', $order->order_number) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 font-bold text-white bg-brand-accent hover:bg-brand-accent-hover px-6 py-3.5 rounded-xl shadow-soft hover:shadow-hover hover:-translate-y-0.5 transition-all duration-300">
                <span>Xem chi tiết & Theo dõi đơn</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="{{ route('books.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center font-bold text-brand-text bg-brand-surface hover:bg-brand-border px-6 py-3.5 rounded-xl transition-colors">
                Tiếp tục mua sách
            </a>
        </div>
    </div>
</div>
@endsection

