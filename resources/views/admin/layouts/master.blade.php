<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="MNM Bookstore Admin Panel">
    <title>Admin Panel - @yield('title')</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-surface font-ui text-brand-text antialiased">
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-brand-border flex flex-col">
            <div class="h-16 flex items-center px-6 border-b border-brand-border">
                <a href="{{ route('admin.dashboard') }}" class="font-display font-bold text-xl text-brand-text hover:text-brand-accent">
                    Admin Panel
                </a>
            </div>
            
            <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.dashboard*') ? 'bg-brand-accent text-white' : 'text-brand-muted hover:bg-brand-bg hover:text-brand-text' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.books.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.books.*') ? 'bg-brand-accent text-white' : 'text-brand-muted hover:bg-brand-bg hover:text-brand-text' }}">
                    Sách
                </a>
                <a href="{{ route('admin.orders.index') }}" class="flex items-center justify-between px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.orders.*') ? 'bg-brand-accent text-white' : 'text-brand-muted hover:bg-brand-bg hover:text-brand-text' }}">
                    <span>Đơn hàng</span>
                    @php $pendingCount = \App\Models\Order::where('status', 'pending')->count(); @endphp
                    @if($pendingCount > 0)
                        <span class="text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('admin.orders.*') ? 'bg-white text-brand-accent' : 'bg-brand-accent text-white' }}">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.categories.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.categories.*') ? 'bg-brand-accent text-white' : 'text-brand-muted hover:bg-brand-bg hover:text-brand-text' }}">
                    Danh mục
                </a>
                <a href="{{ route('admin.authors.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.authors.*') ? 'bg-brand-accent text-white' : 'text-brand-muted hover:bg-brand-bg hover:text-brand-text' }}">
                    Tác giả
                </a>
                @can('manage_orders')
                <a href="{{ route('admin.coupons.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.coupons.*') ? 'bg-brand-accent text-white' : 'text-brand-muted hover:bg-brand-bg hover:text-brand-text' }}">
                    Mã giảm giá
                </a>
                @endcan
                @can('manage_reviews')
                <a href="{{ route('admin.reviews.index') }}" class="flex items-center justify-between px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.reviews.*') ? 'bg-brand-accent text-white' : 'text-brand-muted hover:bg-brand-bg hover:text-brand-text' }}">
                    <span>Bình luận & Đánh giá</span>
                    @php
                        $unrepliedReviewCount = \App\Models\Review::where(function ($q) {
                            $q->whereNull('admin_reply')->orWhere('admin_reply', '');
                        })->count();
                    @endphp
                    @if($unrepliedReviewCount > 0)
                        <span class="text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('admin.reviews.*') ? 'bg-white text-brand-accent' : 'bg-brand-accent text-white' }}">
                            {{ $unrepliedReviewCount }}
                        </span>
                    @endif
                </a>
                @endcan
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-brand-accent text-white' : 'text-brand-muted hover:bg-brand-bg hover:text-brand-text' }}">
                    Quản lý Người dùng
                </a>
                @endif
                <a href="{{ route('admin.ai.logs') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.ai.logs') ? 'bg-brand-accent text-white' : 'text-brand-muted hover:bg-brand-bg hover:text-brand-text' }}">
                    Lịch sử thao tác AI
                </a>
                <div class="pt-4 mt-4 border-t border-brand-border">
                    <a href="{{ route('home') }}" class="flex items-center px-4 py-2 text-sm font-medium text-brand-muted hover:text-brand-text">
                        Về trang khách
                    </a>
                </div>
            </nav>
            
            <div class="p-4 border-t border-brand-border">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs text-brand-accent hover:underline">Đăng xuất</button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white border-b border-brand-border flex items-center justify-between px-6">
                <h1 class="text-xl font-semibold">@yield('title')</h1>
            </header>
            
            <div class="flex-1 overflow-y-auto p-6 bg-brand-surface">
                @if(session('success'))
                    <div class="mb-4 px-4 py-3 bg-green-50 text-green-700 border border-green-200 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 px-4 py-3 bg-red-50 text-red-700 border border-red-200 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif
                
                @yield('content')
            </div>
        </main>

    </div>
    
    @include('admin.partials.ai_assistant')
    @stack('scripts')
</body>
</html>
