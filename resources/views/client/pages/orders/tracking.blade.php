@extends('client.layouts.master')

@section('title', 'Tra cứu đơn hàng - MNM Bookstore')

@section('content')
<div class="py-12 max-w-3xl mx-auto font-ui">
    <div class="text-center mb-10">
        <h1 class="font-display text-3xl font-bold text-brand-text mb-2">Tra cứu đơn hàng</h1>
        <p class="text-brand-muted text-sm max-w-md mx-auto">Nhập Mã đơn hàng và Số điện thoại bạn đã dùng khi đặt hàng để kiểm tra trạng thái vận chuyển tức thì.</p>
    </div>

    <!-- Search Form Box -->
    <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm mb-10">
        <form method="GET" action="{{ route('orders.tracking') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="order_number" class="block text-xs font-bold uppercase text-brand-text mb-1.5">Mã đơn hàng *</label>
                    <input type="text" id="order_number" name="order_number" value="{{ request('order_number') }}" required placeholder="Ví dụ: MNM-20260909-XXXXX" class="w-full bg-brand-surface border border-brand-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-accent uppercase font-mono">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold uppercase text-brand-text mb-1.5">Số điện thoại nhận hàng *</label>
                    <input type="tel" id="phone" name="phone" value="{{ request('phone') }}" required placeholder="Ví dụ: 0901234567" class="w-full bg-brand-surface border border-brand-border rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-accent">
                </div>
            </div>

            <button type="submit" class="w-full font-bold text-white bg-brand-accent hover:bg-brand-accent-hover py-3.5 rounded-xl shadow-soft hover:shadow-hover hover:-translate-y-0.5 transition-all duration-300 text-sm cursor-pointer">
                Tra cứu tiến trình
            </button>
        </form>
    </div>

    <!-- Search Results -->
    @if($searched)
        @if($order)
            <div class="bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-brand-border mb-6">
                    <div>
                        <span class="text-xs text-brand-muted block">Mã đơn hàng</span>
                        <h3 class="font-mono text-xl font-bold text-brand-accent">{{ $order->order_number }}</h3>
                        <span class="text-xs text-brand-muted">Đặt ngày: {{ $order->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="inline-block px-3 py-1.5 rounded-full text-xs font-semibold border {{ $order->status_badge_class }}">
                            {{ $order->status_label }}
                        </span>
                    </div>
                </div>

                <!-- Timeline -->
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

                    <div class="mb-8">
                        <div class="relative max-w-xl mx-auto">
                            <!-- Progress Line aligned to center of 40px circles -->
                            <div class="absolute left-[10%] right-[10%] top-5 -translate-y-1/2 h-1 bg-brand-surface z-0">
                                <div class="h-full bg-brand-accent transition-all duration-700" style="width: {{ (($currentWeight - 1) / 4) * 100 }}%"></div>
                            </div>

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
                    <div class="p-4 bg-red-50 text-red-700 border border-red-200 rounded-xl mb-6 text-sm">
                        Đơn hàng này đã bị hủy.
                    </div>
                @endif

                <!-- Items list -->
                <div class="divide-y divide-brand-border/60 mb-6">
                    @foreach($order->items as $item)
                        <div class="py-3 flex justify-between items-center text-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-14 bg-brand-surface rounded overflow-hidden flex-shrink-0 border border-brand-border">
                                    @if(!empty($item->book_cover))
                                        <img src="{{ $item->book_cover }}" alt="{{ $item->book_title }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div>
                                    <h5 class="font-bold text-brand-text text-xs line-clamp-1">{{ $item->book_title }}</h5>
                                    <span class="text-brand-muted text-xs">SL: {{ $item->quantity }} × {{ number_format($item->price, 0, ',', '.') }} ₫</span>
                                </div>
                            </div>
                            <span class="font-display font-bold text-brand-text">{{ number_format($item->total, 0, ',', '.') }} ₫</span>
                        </div>
                    @endforeach
                </div>

                <!-- Total -->
                <div class="pt-4 border-t border-brand-border flex justify-between items-baseline">
                    <span class="font-bold text-brand-text text-sm">Tổng tiền:</span>
                    <span class="font-display font-bold text-xl text-brand-accent">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</span>
                </div>
            </div>
        @else
            <div class="bg-white rounded-[var(--radius-card)] border border-red-200 p-8 text-center text-red-600 shadow-sm">
                <svg class="w-12 h-12 text-red-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <h3 class="font-display text-lg font-bold text-brand-text mb-1">Không tìm thấy đơn hàng</h3>
                <p class="text-xs text-brand-muted max-w-sm mx-auto">Vui lòng kiểm tra lại chính xác Mã đơn hàng (bắt đầu bằng MNM-) và Số điện thoại đặt hàng.</p>
            </div>
        @endif
    @endif
</div>
@endsection

