@extends('admin.layouts.master')
@section('title', 'Dashboard Thống kê')

@section('content')
<div class="px-6 py-6 font-ui">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Tổng quan hệ thống</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard.export') }}" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Xuất Excel
            </a>
            <div class="text-sm text-gray-500 bg-white px-4 py-2 rounded-lg border border-gray-200 shadow-sm">
                Cập nhật lúc: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <!-- 1. KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Doanh thu -->
        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Tổng Doanh Thu</p>
                <h3 class="text-2xl font-bold text-blue-600">{{ number_format($totalRevenue, 0, ',', '.') }} ₫</h3>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Đơn hàng -->
        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Tổng Đơn Hàng</p>
                <h3 class="text-2xl font-bold text-emerald-600">{{ number_format($totalOrders) }}</h3>
            </div>
            <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
        </div>

        <!-- Khách hàng -->
        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Khách Hàng</p>
                <h3 class="text-2xl font-bold text-amber-600">{{ number_format($totalUsers) }}</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>

        <!-- Sách bán ra -->
        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Sách Đã Bán</p>
                <h3 class="text-2xl font-bold text-purple-600">{{ number_format($totalBooksSold) }} <span class="text-xs text-gray-400 font-normal">/ {{ number_format($totalBooks) }}</span></h3>
            </div>
            <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
        </div>
    </div>

    <!-- 2. Chart & Order Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Chart Doanh thu -->
        <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Doanh thu 7 ngày qua</h2>
            <div class="relative h-64 w-full mt-4">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('revenueChart').getContext('2d');
                
                // Gradient for line chart
                let gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)'); // blue-500
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartDates) !!},
                        datasets: [{
                            label: 'Doanh thu (₫)',
                            data: {!! json_encode($chartData) !!},
                            borderColor: '#3b82f6', // blue-500
                            backgroundColor: gradient,
                            borderWidth: 3,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#3b82f6',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f3f4f6', // gray-100
                                    drawBorder: false
                                },
                                ticks: {
                                    callback: function(value, index, values) {
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
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                    }
                });
            });
        </script>
        @endpush
        </div>

        <!-- Order Status -->
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Trạng thái đơn hàng</h2>
            <div class="space-y-4">
                @php
                    $statuses = [
                        'pending' => ['label' => 'Chờ xác nhận', 'color' => 'bg-yellow-500', 'bg' => 'bg-yellow-50'],
                        'confirmed' => ['label' => 'Đã xác nhận', 'color' => 'bg-blue-500', 'bg' => 'bg-blue-50'],
                        'shipping' => ['label' => 'Đang giao', 'color' => 'bg-purple-500', 'bg' => 'bg-purple-50'],
                        'completed' => ['label' => 'Hoàn thành', 'color' => 'bg-emerald-500', 'bg' => 'bg-emerald-50'],
                        'cancelled' => ['label' => 'Đã hủy', 'color' => 'bg-red-500', 'bg' => 'bg-red-50'],
                    ];
                @endphp
                
                @foreach($statuses as $key => $st)
                    @php 
                        $count = $ordersByStatus[$key] ?? 0; 
                        $percent = $totalOrders > 0 ? round(($count / $totalOrders) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <span class="text-sm font-medium text-gray-700">{{ $st['label'] }}</span>
                            <span class="text-sm font-bold text-gray-900">{{ $count }}</span>
                        </div>
                        <div class="w-full h-2 rounded-full {{ $st['bg'] }}">
                            <div class="h-2 rounded-full {{ $st['color'] }}" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 3. Top Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top 5 Sách bán chạy -->
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Top 5 sách bán chạy nhất</h2>
            @if($topBooks->isEmpty())
                <p class="text-sm text-gray-500 text-center py-4">Chưa có dữ liệu bán hàng</p>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($topBooks as $index => $book)
                        <div class="flex items-center gap-4 py-3">
                            <div class="font-bold text-gray-400 w-4">{{ $index + 1 }}</div>
                            <div class="w-10 h-14 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                                @if($book->cover_image)
                                    <img src="{{ $book->cover_image }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-sm text-gray-800 line-clamp-1">{{ $book->title }}</h4>
                            </div>
                            <div class="font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded text-sm">
                                Đã bán: {{ $book->total_sold }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Top 5 Khách VIP -->
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Top khách hàng VIP</h2>
            @if($topCustomers->isEmpty() || $topCustomers->first()->orders_sum_total_amount == 0)
                <p class="text-sm text-gray-500 text-center py-4">Chưa có dữ liệu</p>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($topCustomers as $index => $customer)
                        @if($customer->orders_sum_total_amount > 0)
                        <div class="flex items-center justify-between py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                                    {{ substr($customer->name, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-gray-800">{{ $customer->name }}</h4>
                                    <div class="text-xs text-gray-500">{{ $customer->orders_count }} đơn hàng</div>
                                </div>
                            </div>
                            <div class="font-bold text-blue-600">
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
@endsection
