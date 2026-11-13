@extends('admin.layouts.master')
@section('title', 'Dashboard Thống kê')

@section('content')
{{-- Nạp font Lora & Nunito theo DESIGN.md --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

@php
    // Chuẩn C³: Các biến kiểm tra điều kiện sử dụng tiền tố is/has
    $hasOrders = $totalOrders > 0;
    $hasBooksInventory = $totalBooks > 0;
    $hasTopBooks = !$topBooks->isEmpty();
    $hasTopCustomers = !$topCustomers->isEmpty() && $topCustomers->first()->orders_sum_total_amount > 0;

    // Tính chỉ số phụ trợ từ dữ liệu sẵn có
    $averageOrderValue = $hasOrders ? round($totalRevenue / $totalOrders) : 0;
    $completedOrdersCount = $ordersByStatus['completed'] ?? 0;
    $completionRate = $hasOrders ? round(($completedOrdersCount / $totalOrders) * 100) : 0;
    $bookSoldRatio = $hasBooksInventory ? min(100, round(($totalBooksSold / $totalBooks) * 100)) : 0;

    // Tính tổng và trung bình doanh thu 7 ngày qua bằng foreach cơ bản
    $weeklyTotalRevenue = 0;
    $weeklyPeakRevenue = 0;
    $chartDaysCount = count($chartData);

    foreach ($chartData as $dailyAmount) {
        $weeklyTotalRevenue += $dailyAmount;
        if ($dailyAmount > $weeklyPeakRevenue) {
            $weeklyPeakRevenue = $dailyAmount;
        }
    }
    $weeklyAvgRevenue = $chartDaysCount > 0 ? round($weeklyTotalRevenue / $chartDaysCount) : 0;

    // Danh sách trạng thái đơn hàng
    $statuses = [
        'pending'   => ['label' => 'Chờ xác nhận', 'color' => 'bg-amber-500',   'text' => 'text-amber-700',   'bg' => 'bg-amber-50'],
        'confirmed' => ['label' => 'Đã xác nhận',  'color' => 'bg-[#E27D60]',   'text' => 'text-[#C4674C]',   'bg' => 'bg-[#E27D60]/10'],
        'packing'   => ['label' => 'Đang đóng gói', 'color' => 'bg-indigo-600',  'text' => 'text-indigo-700',  'bg' => 'bg-indigo-50'],
        'shipping'  => ['label' => 'Đang giao',    'color' => 'bg-sky-600',     'text' => 'text-sky-700',     'bg' => 'bg-sky-50'],
        'completed' => ['label' => 'Hoàn thành',   'color' => 'bg-emerald-600', 'text' => 'text-emerald-700', 'bg' => 'bg-emerald-50'],
        'cancelled' => ['label' => 'Đã hủy',       'color' => 'bg-rose-600',    'text' => 'text-rose-700',    'bg' => 'bg-rose-50'],
    ];
@endphp

<div class="space-y-6 text-[#2D3748]" style="font-family: 'Nunito', sans-serif;">
    
    <!-- ================= HEADER ================= -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 pb-5 border-b border-[#E2E8F0]">
        <div>
            <h2 class="text-2xl font-bold text-[#2D3748]" style="font-family: 'Lora', serif;">
                Tổng quan hệ thống
            </h2>
            <p class="text-sm text-[#718096] mt-0.5">
                Số liệu vận hành và doanh thu của MNM Bookstore
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-xs font-medium text-[#718096] bg-white px-3.5 py-2 rounded border border-[#E2E8F0]">
                Cập nhật lúc: <strong class="text-[#2D3748]">{{ now()->format('d/m/Y H:i') }}</strong>
            </div>

            <a href="{{ route('admin.dashboard.export') }}" 
               class="inline-flex items-center gap-2 bg-[#E27D60] hover:bg-[#C4674C] text-white px-4 py-2 rounded font-semibold text-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Xuất Excel
            </a>
        </div>
    </div>

    <!-- ================= 1. KPI STRIP (4 CỘT PHẲNG, VIỀN TRÁI SẮC NÉT) ================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        
        <!-- Tổng Doanh Thu -->
        <div class="bg-white p-5 rounded border border-[#E2E8F0] border-l-4 border-l-[#E27D60] flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-[#718096]">Tổng Doanh Thu</p>
                    <h3 class="text-2xl font-bold text-[#2D3748] mt-1.5" style="font-family: 'Lora', serif;">
                        {{ number_format($totalRevenue, 0, ',', '.') }} ₫
                    </h3>
                </div>
                <div class="p-2 bg-[#F7FAFC] text-[#E27D60] rounded border border-[#E2E8F0]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#E2E8F0] flex justify-between text-xs text-[#718096]">
                <span>Trung bình đơn:</span>
                <strong class="text-[#2D3748]">{{ number_format($averageOrderValue, 0, ',', '.') }} ₫</strong>
            </div>
        </div>

        <!-- Tổng Đơn Hàng -->
        <div class="bg-white p-5 rounded border border-[#E2E8F0] border-l-4 border-l-emerald-600 flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-[#718096]">Tổng Đơn Hàng</p>
                    <h3 class="text-2xl font-bold text-[#2D3748] mt-1.5" style="font-family: 'Lora', serif;">
                        {{ number_format($totalOrders) }}
                    </h3>
                </div>
                <div class="p-2 bg-[#F7FAFC] text-emerald-600 rounded border border-[#E2E8F0]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#E2E8F0] flex justify-between text-xs text-[#718096]">
                <span>Tỷ lệ hoàn tất:</span>
                <strong class="text-emerald-700">{{ $completionRate }}% ({{ $completedOrdersCount }} đơn)</strong>
            </div>
        </div>

        <!-- Khách Hàng -->
        <div class="bg-white p-5 rounded border border-[#E2E8F0] border-l-4 border-l-amber-500 flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-[#718096]">Khách Hàng</p>
                    <h3 class="text-2xl font-bold text-[#2D3748] mt-1.5" style="font-family: 'Lora', serif;">
                        {{ number_format($totalUsers) }}
                    </h3>
                </div>
                <div class="p-2 bg-[#F7FAFC] text-amber-600 rounded border border-[#E2E8F0]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#E2E8F0] flex justify-between text-xs text-[#718096]">
                <span>Trạng thái:</span>
                <strong class="text-[#2D3748]">Tài khoản hoạt động</strong>
            </div>
        </div>

        <!-- Sách Đã Bán -->
        <div class="bg-white p-5 rounded border border-[#E2E8F0] border-l-4 border-l-[#2D3748] flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-[#718096]">Sách Đã Bán</p>
                    <h3 class="text-2xl font-bold text-[#2D3748] mt-1.5" style="font-family: 'Lora', serif;">
                        {{ number_format($totalBooksSold) }}
                        <span class="text-xs font-normal text-[#718096]" style="font-family: 'Nunito', sans-serif;">
                            / {{ number_format($totalBooks) }}
                        </span>
                    </h3>
                </div>
                <div class="p-2 bg-[#F7FAFC] text-[#2D3748] rounded border border-[#E2E8F0]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#E2E8F0] flex justify-between text-xs text-[#718096]">
                <span>Quy mô danh mục:</span>
                <strong class="text-[#2D3748]">{{ number_format($totalBooks) }} đầu sách</strong>
            </div>
        </div>

    </div>

    <!-- ================= 2. BIỂU ĐỒ DOANH THU & TRẠNG THÁI ĐƠN HÀNG ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        
        <!-- Biểu đồ Doanh thu (8/12 cột) -->
        <div class="lg:col-span-8 bg-white rounded border border-[#E2E8F0]">
            <!-- Header của khung biểu đồ -->
            <div class="px-6 py-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-[#2D3748]" style="font-family: 'Lora', serif;">
                        Doanh thu 7 ngày qua
                    </h2>
                    <p class="text-xs text-[#718096]">Biểu đồ theo dõi doanh thu thực tế theo ngày</p>
                </div>

                <!-- Nút chuyển đổi Đường / Cột gọn phẳng -->
                <div class="inline-flex border border-[#E2E8F0] rounded overflow-hidden text-xs font-semibold">
                    <button type="button" id="btnLineChart" class="px-3 py-1.5 bg-[#2D3748] text-white cursor-pointer transition-colors">
                        Đường
                    </button>
                    <button type="button" id="btnBarChart" class="px-3 py-1.5 bg-white text-[#718096] hover:text-[#2D3748] cursor-pointer transition-colors">
                        Cột
                    </button>
                </div>
            </div>

            <!-- Dải tóm tắt số liệu 7 ngày -->
            <div class="grid grid-cols-3 divide-x divide-[#E2E8F0] bg-[#F7FAFC] border-b border-[#E2E8F0]">
                <div class="px-6 py-3">
                    <span class="text-[11px] font-semibold uppercase text-[#718096] block">Tổng 7 ngày</span>
                    <span class="text-sm font-bold text-[#2D3748]">{{ number_format($weeklyTotalRevenue, 0, ',', '.') }} ₫</span>
                </div>
                <div class="px-6 py-3">
                    <span class="text-[11px] font-semibold uppercase text-[#718096] block">Trung bình / ngày</span>
                    <span class="text-sm font-bold text-[#2D3748]">{{ number_format($weeklyAvgRevenue, 0, ',', '.') }} ₫</span>
                </div>
                <div class="px-6 py-3">
                    <span class="text-[11px] font-semibold uppercase text-[#718096] block">Cao nhất ngày</span>
                    <span class="text-sm font-bold text-[#E27D60]">{{ number_format($weeklyPeakRevenue, 0, ',', '.') }} ₫</span>
                </div>
            </div>

            <!-- Vùng vẽ biểu đồ -->
            <div class="p-6">
                <div class="relative h-64 w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Trạng thái đơn hàng (4/12 cột) -->
        <div class="lg:col-span-4 bg-white rounded border border-[#E2E8F0] flex flex-col">
            <div class="px-6 py-4 border-b border-[#E2E8F0] flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#2D3748]" style="font-family: 'Lora', serif;">
                        Trạng thái đơn hàng
                    </h2>
                    <p class="text-xs text-[#718096]">Phân bổ trên tổng số {{ number_format($totalOrders) }} đơn</p>
                </div>
            </div>

            <div class="p-6 flex-1 flex flex-col justify-between space-y-5">
                @foreach($statuses as $key => $st)
                    @php 
                        $count = $ordersByStatus[$key] ?? 0; 
                        $percent = $hasOrders ? round(($count / $totalOrders) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between items-center text-sm mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 {{ $st['color'] }}"></span>
                                <span class="font-medium text-[#2D3748]">{{ $st['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-[#2D3748]">{{ number_format($count) }}</span>
                                <span class="text-xs text-[#718096] w-9 text-right">({{ $percent }}%)</span>
                            </div>
                        </div>
                        <div class="w-full h-1.5 bg-[#F7FAFC] border border-[#E2E8F0] overflow-hidden">
                            <div class="h-full {{ $st['color'] }}" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- ================= 3. BẢNG XẾP HẠNG DỮ LIỆU (2 CỘT ĐỐI XỨNG GỌN GÀNG) ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Bảng 1: Top 5 Sách bán chạy nhất (Thumbnail nhỏ gọn w-9) -->
        <div class="bg-white rounded border border-[#E2E8F0] overflow-hidden">
            <div class="px-6 py-4 border-b border-[#E2E8F0] flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#2D3748]" style="font-family: 'Lora', serif;">
                        Top 5 sách bán chạy nhất
                    </h2>
                    <p class="text-xs text-[#718096]">Xếp hạng theo số lượng bản in đã bán</p>
                </div>
                <span class="text-xs font-semibold text-[#718096] bg-[#F7FAFC] px-2.5 py-1 rounded border border-[#E2E8F0]">
                    Top 5
                </span>
            </div>

            @if(!$hasTopBooks)
                <p class="text-sm text-[#718096] text-center py-10">Chưa có dữ liệu bán hàng</p>
            @else
                @php
                    // Lấy số lượng bán cao nhất để vẽ thanh tỷ lệ nhỏ gọn
                    $maxBookSold = $topBooks->first()->total_sold > 0 ? $topBooks->first()->total_sold : 1;
                @endphp
                <div class="divide-y divide-[#E2E8F0]">
                    <!-- Tiêu đề cột -->
                    <div class="grid grid-cols-12 px-6 py-2.5 bg-[#F7FAFC] text-[11px] font-bold uppercase tracking-wider text-[#718096]">
                        <div class="col-span-1">#</div>
                        <div class="col-span-8">Đầu sách</div>
                        <div class="col-span-3 text-right">Đã bán</div>
                    </div>

                    @foreach($topBooks as $index => $book)
                        @php
                            $hasCoverImage = !empty($book->cover_image);
                            $isTopRank = ($index === 0);
                            $soldPercent = round(($book->total_sold / $maxBookSold) * 100);
                        @endphp
                        <div class="grid grid-cols-12 items-center px-6 py-3 hover:bg-[#F7FAFC] transition-colors">
                            <!-- Cột Hạng -->
                            <div class="col-span-1">
                                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold rounded {{ $isTopRank ? 'bg-[#E27D60] text-white' : 'text-[#718096]' }}">
                                    {{ $index + 1 }}
                                </span>
                            </div>

                            <!-- Cột Thông tin sách + Thumbnail nhỏ gọn w-9 aspect-[3/4] -->
                            <div class="col-span-8 flex items-center gap-3 pr-3 min-w-0">
                                <div class="w-9 aspect-[3/4] bg-[#F7FAFC] border border-[#E2E8F0] overflow-hidden flex-shrink-0">
                                    @if($hasCoverImage)
                                        <img src="{{ $book->cover_image }}" 
                                             alt="{{ $book->title }}" 
                                             class="w-full h-full object-cover"
                                             onerror="this.onerror=null; this.src='https://placehold.co/72x96/F7FAFC/718096?text=Book';">
                                    @else
                                        <img src="https://placehold.co/72x96/F7FAFC/718096?text=Book" 
                                             alt="Book Cover" 
                                             class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-sm text-[#2D3748] truncate" title="{{ $book->title }}">
                                        {{ $book->title }}
                                    </h4>
                                    <div class="w-28 h-1 bg-[#E2E8F0] mt-1.5 overflow-hidden">
                                        <div class="h-full bg-[#E27D60]" style="width: {{ $soldPercent }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cột Số lượng bán -->
                            <div class="col-span-3 text-right">
                                <span class="font-bold text-sm text-[#2D3748]">{{ number_format($book->total_sold) }}</span>
                                <span class="text-xs text-[#718096] block">cuốn</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Bảng 2: Top 5 Khách hàng VIP -->
        <div class="bg-white rounded border border-[#E2E8F0] overflow-hidden">
            <div class="px-6 py-4 border-b border-[#E2E8F0] flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#2D3748]" style="font-family: 'Lora', serif;">
                        Top khách hàng VIP
                    </h2>
                    <p class="text-xs text-[#718096]">Khách hàng có tổng chi tiêu cao nhất</p>
                </div>
                <span class="text-xs font-semibold text-[#718096] bg-[#F7FAFC] px-2.5 py-1 rounded border border-[#E2E8F0]">
                    VIP
                </span>
            </div>

            @if(!$hasTopCustomers)
                <p class="text-sm text-[#718096] text-center py-10">Chưa có dữ liệu</p>
            @else
                <div class="divide-y divide-[#E2E8F0]">
                    <!-- Tiêu đề cột -->
                    <div class="grid grid-cols-12 px-6 py-2.5 bg-[#F7FAFC] text-[11px] font-bold uppercase tracking-wider text-[#718096]">
                        <div class="col-span-1">#</div>
                        <div class="col-span-6">Khách hàng</div>
                        <div class="col-span-2 text-center">Số đơn</div>
                        <div class="col-span-3 text-right">Tổng chi tiêu</div>
                    </div>

                    @foreach($topCustomers as $index => $customer)
                        @php
                            $isValidVipCustomer = $customer->orders_sum_total_amount > 0;
                            $isTopVip = ($index === 0);
                            $firstLetter = mb_strtoupper(mb_substr($customer->name, 0, 1, 'UTF-8'), 'UTF-8');
                        @endphp

                        @if($isValidVipCustomer)
                            <div class="grid grid-cols-12 items-center px-6 py-3.5 hover:bg-[#F7FAFC] transition-colors">
                                <!-- Cột Hạng -->
                                <div class="col-span-1">
                                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold rounded {{ $isTopVip ? 'bg-[#E27D60] text-white' : 'text-[#718096]' }}">
                                        {{ $index + 1 }}
                                    </span>
                                </div>

                                <!-- Cột Tên khách hàng -->
                                <div class="col-span-6 flex items-center gap-3 pr-2 min-w-0">
                                    <div class="w-8 h-8 rounded bg-[#F7FAFC] border border-[#E2E8F0] text-[#2D3748] flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        {{ $firstLetter }}
                                    </div>
                                    <h4 class="font-bold text-sm text-[#2D3748] truncate">
                                        {{ $customer->name }}
                                    </h4>
                                </div>

                                <!-- Cột Số đơn -->
                                <div class="col-span-2 text-center">
                                    <span class="text-xs font-semibold text-[#718096] bg-[#F7FAFC] px-2 py-1 rounded border border-[#E2E8F0]">
                                        {{ $customer->orders_count }} đơn
                                    </span>
                                </div>

                                <!-- Cột Tổng tiền -->
                                <div class="col-span-3 text-right font-bold text-sm text-[#E27D60]">
                                    {{ number_format($customer->orders_sum_total_amount, 0, ',', '.') }} ₫
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Khởi tạo và chuyển đổi biểu đồ bằng Vanilla JS thuần
    document.addEventListener('DOMContentLoaded', function() {
        const canvasElement = document.getElementById('revenueChart');
        if (!canvasElement) return;

        const ctx = canvasElement.getContext('2d');
        const chartLabels = {!! json_encode($chartDates) !!};
        const chartValues = {!! json_encode($chartData) !!};

        const lineGradient = ctx.createLinearGradient(0, 0, 0, 260);
        lineGradient.addColorStop(0, 'rgba(226, 125, 96, 0.25)');
        lineGradient.addColorStop(1, 'rgba(226, 125, 96, 0.0)');

        let currentChart = null;

        function renderChart(type) {
            if (currentChart) {
                currentChart.destroy();
            }

            const isLine = (type === 'line');

            currentChart = new Chart(ctx, {
                type: type,
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Doanh thu (₫)',
                        data: chartValues,
                        borderColor: '#E27D60',
                        backgroundColor: isLine ? lineGradient : '#E27D60',
                        hoverBackgroundColor: '#C4674C',
                        borderWidth: isLine ? 2.5 : 0,
                        borderRadius: isLine ? 0 : 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#E27D60',
                        pointBorderWidth: 2,
                        pointRadius: 3.5,
                        pointHoverRadius: 5,
                        fill: isLine,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#2D3748',
                            padding: 10,
                            cornerRadius: 4,
                            callbacks: {
                                label: function(context) {
                                    if (context.parsed.y !== null) {
                                        return ' Doanh thu: ' + new Intl.NumberFormat('vi-VN', {
                                            style: 'currency',
                                            currency: 'VND'
                                        }).format(context.parsed.y);
                                    }
                                    return '';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#E2E8F0',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#718096',
                                callback: function(value) {
                                    if (value >= 1000000) return (value / 1000000) + ' Tr';
                                    if (value >= 1000) return (value / 1000) + ' K';
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#718096'
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    }
                }
            });
        }

        renderChart('line');

        const btnLine = document.getElementById('btnLineChart');
        const btnBar = document.getElementById('btnBarChart');

        if (btnLine && btnBar) {
            btnLine.addEventListener('click', function() {
                renderChart('line');
                btnLine.className = 'px-3 py-1.5 bg-[#2D3748] text-white cursor-pointer transition-colors';
                btnBar.className = 'px-3 py-1.5 bg-white text-[#718096] hover:text-[#2D3748] cursor-pointer transition-colors';
            });

            btnBar.addEventListener('click', function() {
                renderChart('bar');
                btnBar.className = 'px-3 py-1.5 bg-[#2D3748] text-white cursor-pointer transition-colors';
                btnLine.className = 'px-3 py-1.5 bg-white text-[#718096] hover:text-[#2D3748] cursor-pointer transition-colors';
            });
        }
    });
</script>
@endpush
@endsection