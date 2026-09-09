@extends('admin.layouts.master')

@section('title', 'Quản lý Đơn hàng')

@section('content')
<div class="space-y-6">
    <!-- Status Tabs -->
    <div class="flex flex-wrap gap-2 pb-2 border-b border-brand-border">
        @php
            $currentStatus = request('status', 'all');
            $tabs = [
                'all' => ['label' => 'Tất cả', 'count' => $statusCounts['all']],
                'pending' => ['label' => 'Chờ xác nhận', 'count' => $statusCounts['pending']],
                'confirmed' => ['label' => 'Đã xác nhận', 'count' => $statusCounts['confirmed']],
                'shipping' => ['label' => 'Đang giao', 'count' => $statusCounts['shipping']],
                'completed' => ['label' => 'Hoàn thành', 'count' => $statusCounts['completed']],
                'cancelled' => ['label' => 'Đã hủy', 'count' => $statusCounts['cancelled']],
            ];
        @endphp

        @foreach($tabs as $key => $tab)
            <a href="{{ route('admin.orders.index', array_merge(request()->except('status', 'page'), ['status' => $key])) }}" 
               class="px-4 py-2 text-sm font-semibold rounded-xl transition-all duration-200 flex items-center gap-2 {{ $currentStatus === $key ? 'bg-brand-accent text-white shadow-sm' : 'bg-white text-brand-muted hover:bg-brand-bg hover:text-brand-text border border-brand-border' }}">
                <span>{{ $tab['label'] }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $currentStatus === $key ? 'bg-white/20 text-white' : 'bg-brand-surface text-brand-muted' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-brand-border shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex-1 w-full flex flex-col sm:flex-row items-center gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo mã đơn, họ tên, số điện thoại, email..." class="w-full bg-brand-surface border border-brand-border rounded-lg pl-10 pr-4 py-2 text-sm focus:outline-none focus:border-brand-accent">
                <svg class="w-4 h-4 text-brand-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-brand-text text-white rounded-lg text-sm font-semibold hover:bg-brand-accent transition-colors">
                Tìm kiếm
            </button>

            @if(request('search') || (request('status') && request('status') !== 'all'))
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-brand-muted hover:text-brand-accent underline whitespace-nowrap">
                    Xóa bộ lọc
                </a>
            @endif
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl border border-brand-border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-brand-surface text-brand-muted text-xs uppercase font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Mã đơn hàng</th>
                        <th class="py-3.5 px-4">Khách hàng</th>
                        <th class="py-3.5 px-4">Tổng thanh toán</th>
                        <th class="py-3.5 px-4">Thanh toán</th>
                        <th class="py-3.5 px-4 text-center">Trạng thái đơn</th>
                        <th class="py-3.5 px-4">Ngày tạo</th>
                        <th class="py-3.5 px-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($orders as $order)
                        <tr class="hover:bg-brand-surface/40 transition-colors">
                            <td class="py-4 px-4 font-mono font-bold text-brand-text">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="hover:text-brand-accent">
                                    {{ $order->order_number }}
                                </a>
                                <div class="text-xs font-sans font-normal text-brand-muted">
                                    {{ $order->items->count() }} món
                                </div>
                            </td>

                            <td class="py-4 px-4">
                                <div class="font-semibold text-brand-text">{{ $order->customer_name }}</div>
                                <div class="text-xs text-brand-muted">{{ $order->customer_phone }}</div>
                            </td>

                            <td class="py-4 px-4 font-semibold text-brand-accent whitespace-nowrap">
                                {{ number_format($order->total_amount, 0, ',', '.') }} ₫
                            </td>

                            <td class="py-4 px-4">
                                <div class="text-xs text-brand-text">{{ $order->payment_method === 'cod' ? 'COD' : 'Chuyển khoản' }}</div>
                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold border {{ $order->payment_status_badge_class }}">
                                    {{ $order->payment_status_label }}
                                </span>
                            </td>

                            <td class="py-4 px-4 text-center">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold border {{ $order->status_badge_class }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>

                            <td class="py-4 px-4 text-xs text-brand-muted whitespace-nowrap">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center gap-1 text-xs font-bold text-white bg-brand-text hover:bg-brand-accent px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                                    <span>Chi tiết</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-brand-muted">
                                Không tìm thấy đơn hàng nào phù hợp với bộ lọc.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-brand-border">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection

