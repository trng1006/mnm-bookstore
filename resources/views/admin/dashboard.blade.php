@extends('admin.layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Total Books -->
    <div class="bg-white p-6 rounded-xl border border-brand-border shadow-sm flex items-center justify-between">
        <div>
            <p class="text-sm text-brand-muted font-medium mb-1">Tổng số sách</p>
            <h3 class="text-3xl font-bold text-brand-text">{{ $totalBooks }}</h3>
        </div>
        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
        </div>
    </div>

    <!-- Total Users -->
    <div class="bg-white p-6 rounded-xl border border-brand-border shadow-sm flex items-center justify-between">
        <div>
            <p class="text-sm text-brand-muted font-medium mb-1">Người dùng</p>
            <h3 class="text-3xl font-bold text-brand-text">{{ $totalUsers }}</h3>
        </div>
        <div class="w-12 h-12 bg-green-50 text-green-500 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
    </div>
    
    <!-- Total Orders -->
    <div class="bg-white p-6 rounded-xl border border-brand-border shadow-sm flex items-center justify-between">
        <div>
            <p class="text-sm text-brand-muted font-medium mb-1">Đơn hàng</p>
            <h3 class="text-3xl font-bold text-brand-text">{{ $totalOrders }}</h3>
        </div>
        <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
    </div>
</div>
@endsection
