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
        <form method="GET" action="{{ route('admin.orders.index') }}" id="order-search-form" class="flex-1 w-full flex flex-col sm:flex-row items-center gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}" id="current-filter-status">
            @endif

            <div class="relative flex-1 w-full" id="admin-order-search-container">
                <input type="text" 
                       id="admin-order-search-input" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Tìm nhanh theo mã đơn, họ tên, số điện thoại, email..." 
                       autocomplete="off"
                       class="w-full bg-brand-surface border border-brand-border rounded-lg pl-10 pr-10 py-2.5 text-sm focus:outline-none focus:border-brand-accent focus:ring-1 focus:ring-brand-accent transition-all">
                
                <!-- Search Icon (Left) -->
                <svg class="w-4 h-4 text-brand-muted absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>

                <!-- Loading Spinner (Right) -->
                <div id="admin-order-search-spinner" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                    <svg class="animate-spin h-4 w-4 text-brand-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>

                <!-- Clear Button [x] (Right) -->
                <button type="button" 
                        id="admin-order-search-clear" 
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-brand-muted hover:text-brand-text p-0.5 rounded cursor-pointer {{ request('search') ? '' : 'hidden' }}" 
                        title="Xóa tìm kiếm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                <!-- Quick Search Results Dropdown (Giống tìm kiếm sách) -->
                <div id="admin-order-search-results" class="absolute top-[calc(100%+6px)] left-0 right-0 bg-white border border-brand-border rounded-xl shadow-2xl overflow-hidden z-50 hidden max-h-96 overflow-y-auto font-ui">
                    <!-- Dynamic HTML via JS -->
                </div>
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-brand-text text-white rounded-lg text-sm font-semibold hover:bg-brand-accent transition-colors cursor-pointer">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchContainer = document.getElementById('admin-order-search-container');
    const searchInput = document.getElementById('admin-order-search-input');
    const searchResults = document.getElementById('admin-order-search-results');
    const searchSpinner = document.getElementById('admin-order-search-spinner');
    const searchClear = document.getElementById('admin-order-search-clear');
    const searchForm = document.getElementById('order-search-form');
    const currentStatus = document.getElementById('current-filter-status') ? document.getElementById('current-filter-status').value : 'all';
    
    let debounceTimer = null;

    if (!searchInput || !searchResults) return;

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function toggleClearBtn() {
        if (searchInput.value.trim().length > 0) {
            searchClear.classList.remove('hidden');
        } else {
            searchClear.classList.add('hidden');
        }
    }

    // Xử lý khi gõ tìm kiếm (debounce 300ms) - Tự động tìm không cần nhấn nút
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        toggleClearBtn();

        if (query.length < 2) {
            searchResults.innerHTML = '';
            searchResults.classList.add('hidden');
            searchSpinner.classList.add('hidden');
            return;
        }

        // Hiện spinner và ẩn nút clear khi đang gửi request
        searchSpinner.classList.remove('hidden');
        searchClear.classList.add('hidden');

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('admin.orders.ajaxSearch') }}?q=${encodeURIComponent(query)}&status=${encodeURIComponent(currentStatus)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                searchSpinner.classList.add('hidden');
                toggleClearBtn();
                
                if (data.orders && data.orders.length > 0) {
                    let html = '<div class="divide-y divide-brand-border/60">';
                    html += `
                        <div class="px-4 py-2.5 bg-brand-surface/70 text-xs font-bold text-brand-muted uppercase tracking-wider flex items-center justify-between border-b border-brand-border/50">
                            <span class="flex items-center gap-1.5 text-brand-text">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Tìm thấy ${data.orders.length} đơn hàng gợi ý
                            </span>
                            <span class="text-[10px] lowercase text-brand-muted font-normal">Nhấn để xem chi tiết</span>
                        </div>
                    `;

                    data.orders.forEach(order => {
                        html += `
                            <a href="${order.url}" class="flex items-center justify-between p-3.5 hover:bg-brand-surface/80 transition-colors group">
                                <div class="min-w-0 pr-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-brand-accent text-sm group-hover:underline">#${escapeHtml(order.order_number)}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border ${order.status_badge_class}">
                                            ${escapeHtml(order.status_label)}
                                        </span>
                                    </div>
                                    <div class="text-xs font-semibold text-brand-text mt-1 flex items-center gap-2">
                                        <span>${escapeHtml(order.customer_name)}</span>
                                        <span class="text-brand-muted font-normal">• ${escapeHtml(order.customer_phone)}</span>
                                    </div>
                                    <div class="text-[11px] text-brand-muted mt-0.5">
                                        ${order.items_count} món • Đặt lúc ${order.created_at}
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="font-bold text-brand-accent text-sm">${order.total_amount}</div>
                                    <span class="text-[11px] text-blue-600 font-semibold group-hover:underline inline-flex items-center gap-0.5 mt-1">
                                        Xem chi tiết &rarr;
                                    </span>
                                </div>
                            </a>
                        `;
                    });

                    html += `
                        <div class="p-2.5 text-center bg-gray-50 border-t border-brand-border">
                            <button type="submit" form="order-search-form" class="text-xs font-bold text-brand-accent hover:underline cursor-pointer">
                                Xem tất cả kết quả cho "${escapeHtml(query)}" trong bảng &rarr;
                            </button>
                        </div>
                    </div>`;

                    searchResults.innerHTML = html;
                    searchResults.classList.remove('hidden');
                } else {
                    searchResults.innerHTML = `
                        <div class="p-6 text-center text-sm text-brand-muted font-ui">
                            <svg class="w-8 h-8 text-brand-muted/40 mx-auto mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Không tìm thấy đơn hàng nào khớp với "<strong>${escapeHtml(query)}</strong>".
                        </div>
                    `;
                    searchResults.classList.remove('hidden');
                }
            })
            .catch(err => {
                console.error('Lỗi tìm kiếm đơn hàng:', err);
                searchSpinner.classList.add('hidden');
                toggleClearBtn();
            });
        }, 300);
    });

    // Nút Clear xóa ô input
    searchClear.addEventListener('click', function() {
        searchInput.value = '';
        toggleClearBtn();
        searchResults.innerHTML = '';
        searchResults.classList.add('hidden');
        searchInput.focus();

        @if(request('search'))
            searchForm.submit();
        @endif
    });

    // Click vào input để mở lại dropdown nếu có kết quả
    searchInput.addEventListener('click', function() {
        if (this.value.trim().length >= 2 && searchResults.innerHTML !== '') {
            searchResults.classList.remove('hidden');
        }
    });

    // Đóng dropdown khi click ra ngoài
    document.addEventListener('click', function(e) {
        if (!searchContainer.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });

    // Phím Escape để đóng dropdown
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            searchResults.classList.add('hidden');
        }
    });
});
</script>
@endpush

