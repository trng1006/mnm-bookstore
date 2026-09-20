@extends('admin.layouts.master')

@section('title', 'Chi tiết Đơn hàng #' . $order->order_number)

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" class="p-2 rounded-lg bg-white border border-brand-border text-brand-muted hover:text-brand-text hover:bg-brand-surface transition-colors" title="Quay lại">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold font-display text-brand-text">Đơn hàng #{{ $order->order_number }}</h1>
                <span class="text-xs text-brand-muted">Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $order->status_badge_class }}">
                {{ $order->status_label }}
            </span>
            <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $order->payment_status_badge_class }}">
                {{ $order->payment_status_label }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Order Items & Pricing (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Items Table -->
            <div class="bg-white rounded-xl border border-brand-border p-6 shadow-sm">
                <h3 class="font-display font-bold text-base text-brand-text mb-4 pb-3 border-b border-brand-border">
                    Danh sách sản phẩm ({{ $order->items->count() }})
                </h3>

                <div class="divide-y divide-brand-border/60">
                    @foreach($order->items as $item)
                        <div class="py-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-16 bg-brand-surface rounded overflow-hidden flex-shrink-0 border border-brand-border flex items-center justify-center">
                                    @if(!empty($item->book_cover))
                                        <img src="{{ $item->book_cover }}" alt="{{ $item->book_title }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-[10px] text-brand-muted">Bìa</span>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-brand-text">{{ $item->book_title }}</h4>
                                    <div class="text-xs text-brand-muted mt-0.5">
                                        {{ number_format($item->price, 0, ',', '.') }} ₫ × {{ $item->quantity }}
                                    </div>
                                </div>
                            </div>

                            <div class="font-bold text-sm text-brand-accent whitespace-nowrap">
                                {{ number_format($item->total, 0, ',', '.') }} ₫
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Financial Breakdown -->
                <div class="pt-4 border-t border-brand-border space-y-2 text-sm text-brand-muted">
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
                            <span>Giảm giá (Mã: {{ $order->coupon_code }})</span>
                            <span class="font-bold">-{{ number_format($order->discount_amount, 0, ',', '.') }} ₫</span>
                        </div>
                    @endif
                    <div class="pt-3 border-t border-brand-border flex justify-between items-baseline">
                        <span class="font-bold text-base text-brand-text">Tổng thanh toán</span>
                        <span class="font-display font-bold text-2xl text-brand-accent">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Status Update Form & Customer Info (4 cols) -->
        <div class="lg:col-span-4 space-y-6 font-ui">
            <!-- Order Management Form -->
            <div class="bg-white rounded-xl border border-brand-border p-6 shadow-sm">
                <h3 class="font-display font-bold text-base text-brand-text mb-4 pb-3 border-b border-brand-border">
                    Xử lý trạng thái
                </h3>

                <form method="POST" action="{{ route('admin.orders.updateStatus', $order->id) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    
                    @php
                        $allowedTransitions = \App\Models\Order::$allowedTransitions[$order->status] ?? [];
                    @endphp

                    @if(empty($allowedTransitions))
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-500 text-center">
                            Đơn hàng đã ở trạng thái cuối cùng, không thể thay đổi.
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-bold uppercase text-brand-muted mb-1.5">Ghi chú xử lý (Tùy chọn)</label>
                            <textarea name="note" rows="2" class="w-full bg-brand-surface border border-brand-border rounded-lg p-2.5 text-sm focus:outline-none focus:border-brand-accent" placeholder="Nhập ghi chú cho lần cập nhật này..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold uppercase text-brand-muted mb-2">Chuyển trạng thái sang</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($allowedTransitions as $nextStatus)
                                    @php
                                        $tempOrder = new \App\Models\Order(['status' => $nextStatus]);
                                        $btnClass = match($nextStatus) {
                                            'cancelled', 'payment_failed' => 'bg-white text-red-600 border border-red-200 hover:bg-red-50',
                                            'completed' => 'bg-emerald-600 text-white hover:bg-emerald-700',
                                            default => 'bg-brand-accent text-white hover:bg-brand-accent-hover',
                                        };
                                    @endphp
                                    <button type="submit" name="status" value="{{ $nextStatus }}" class="py-2 px-3 rounded-lg text-sm font-semibold transition-colors shadow-sm {{ $btnClass }}">
                                        {{ $tempOrder->status_label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <hr class="border-brand-border my-4">

                    <div>
                        <label for="payment_status" class="block text-xs font-bold uppercase text-brand-muted mb-1.5">Cập nhật thanh toán (nếu cần)</label>
                        <div class="flex gap-2">
                            <select name="payment_status" id="payment_status" class="flex-1 bg-brand-surface border border-brand-border rounded-lg p-2.5 text-sm focus:outline-none focus:border-brand-accent">
                                <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Chưa thanh toán</option>
                                <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                            </select>
                            @if(empty($allowedTransitions))
                                <button type="submit" name="status" value="{{ $order->status }}" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-bold hover:bg-gray-900">
                                    Lưu
                                </button>
                            @endif
                        </div>
                    </div>

                    <p class="text-[11px] text-brand-muted text-center mt-3">
                        * Thay đổi trạng thái sẽ lưu vào lịch sử và gửi thông báo.
                    </p>
                </form>
            </div>

            <!-- Customer & Delivery Info -->
            <div class="bg-white rounded-xl border border-brand-border p-6 shadow-sm">
                <h3 class="font-display font-bold text-base text-brand-text mb-4 pb-3 border-b border-brand-border">
                    Thông tin giao nhận
                </h3>

                <div class="space-y-3 text-sm text-brand-text">
                    <div>
                        <span class="text-xs text-brand-muted block">Người nhận</span>
                        <strong>{{ $order->customer_name }}</strong>
                    </div>
                    <div>
                        <span class="text-xs text-brand-muted block">Số điện thoại</span>
                        <a href="tel:{{ $order->customer_phone }}" class="text-brand-accent font-semibold hover:underline">
                            {{ $order->customer_phone }}
                        </a>
                    </div>
                    <div>
                        <span class="text-xs text-brand-muted block">Email</span>
                        <a href="mailto:{{ $order->customer_email }}" class="text-brand-accent hover:underline">
                            {{ $order->customer_email }}
                        </a>
                    </div>
                    <div>
                        <span class="text-xs text-brand-muted block">Địa chỉ nhận hàng</span>
                        <span class="text-xs leading-relaxed">{{ $order->shipping_address }}</span>
                    </div>
                    @if($order->shipping_notes)
                        <div class="p-3 bg-brand-surface rounded-lg border border-brand-border text-xs">
                            <strong class="text-brand-muted block mb-1">Ghi chú từ khách:</strong>
                            {{ $order->shipping_notes }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

