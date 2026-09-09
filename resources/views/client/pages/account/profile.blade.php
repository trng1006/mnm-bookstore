@extends('client.layouts.master')

@section('title', 'Tài khoản của tôi - MNM Bookstore')

@section('content')
<div class="py-8">
    <div class="mb-8">
        <h1 class="font-display text-3xl font-bold text-brand-text mb-2">Hồ sơ cá nhân & Đơn hàng</h1>
        <p class="font-ui text-sm text-brand-muted">Quản lý thông tin cá nhân và theo dõi tình trạng các đơn hàng đã đặt.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-sm font-ui flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Left Sidebar: Profile Info Card (4 cols) -->
        <div class="lg:col-span-4 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 shadow-sm font-ui">
            <div class="text-center pb-6 border-b border-brand-border">
                <div class="w-20 h-20 bg-brand-accent/15 text-brand-accent rounded-full flex items-center justify-center font-bold text-2xl mx-auto mb-3">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h3 class="font-display text-lg font-bold text-brand-text">{{ $user->name }}</h3>
                <span class="text-xs text-brand-muted">{{ $user->email }}</span>
                <div class="mt-2">
                    <span class="inline-block px-3 py-0.5 rounded-full text-xs font-semibold bg-brand-surface border border-brand-border text-brand-text">
                        Khách hàng thành viên
                    </span>
                </div>
            </div>

            <!-- Profile update form -->
            <form method="POST" action="{{ route('account.profile.update') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-xs font-bold uppercase text-brand-muted mb-1">Họ và tên</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-brand-surface border border-brand-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-accent">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-brand-muted mb-1">Email (Cố định)</label>
                    <input type="email" value="{{ $user->email }}" disabled class="w-full bg-gray-100 border border-brand-border rounded-xl px-3.5 py-2.5 text-sm text-brand-muted cursor-not-allowed">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold uppercase text-brand-muted mb-1">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="0901234567" class="w-full bg-brand-surface border border-brand-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-accent">
                </div>

                <div>
                    <label for="address" class="block text-xs font-bold uppercase text-brand-muted mb-1">Địa chỉ mặc định</label>
                    <textarea id="address" name="address" rows="3" placeholder="Địa chỉ giao hàng thường dùng..." class="w-full bg-brand-surface border border-brand-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-accent">{{ old('address', $user->address) }}</textarea>
                </div>

                <button type="submit" class="w-full font-bold text-white bg-brand-text hover:bg-brand-accent py-2.5 rounded-xl text-sm transition-colors shadow-sm cursor-pointer">
                    Lưu thông tin
                </button>
            </form>
        </div>

        <!-- Right: Order History (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm font-ui">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-brand-border">
                <h2 class="font-display text-xl font-bold text-brand-text flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Lịch sử đơn hàng
                </h2>
                <a href="{{ route('orders.tracking') }}" class="text-xs text-brand-accent hover:underline font-semibold">
                    Tra cứu đơn khác →
                </a>
            </div>

            @if($orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-brand-border text-xs uppercase text-brand-muted pb-3">
                                <th class="pb-3 font-semibold">Mã đơn</th>
                                <th class="pb-3 font-semibold">Ngày đặt</th>
                                <th class="pb-3 font-semibold">Tổng tiền</th>
                                <th class="pb-3 font-semibold text-center">Trạng thái</th>
                                <th class="pb-3 text-right">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border/60">
                            @foreach($orders as $order)
                                <tr class="hover:bg-brand-surface/40 transition-colors">
                                    <td class="py-4 font-mono font-bold text-brand-text">
                                        <a href="{{ route('account.orders.show', $order->order_number) }}" class="hover:text-brand-accent">
                                            {{ $order->order_number }}
                                        </a>
                                        <div class="text-[11px] font-sans font-normal text-brand-muted">
                                            {{ $order->items->count() }} tựa sách
                                        </div>
                                    </td>

                                    <td class="py-4 text-xs text-brand-muted">
                                        {{ $order->created_at->format('d/m/Y H:i') }}
                                    </td>

                                    <td class="py-4 font-display font-semibold text-brand-accent whitespace-nowrap">
                                        {{ number_format($order->total_amount, 0, ',', '.') }} ₫
                                    </td>

                                    <td class="py-4 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold border {{ $order->status_badge_class }}">
                                            {{ $order->status_label }}
                                        </span>
                                    </td>

                                    <td class="py-4 text-right">
                                        <a href="{{ route('account.orders.show', $order->order_number) }}" class="inline-flex items-center gap-1 text-xs font-bold text-brand-text hover:text-brand-accent bg-brand-surface px-3 py-1.5 rounded-lg border border-brand-border transition-colors">
                                            <span>Xem</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-brand-surface rounded-full flex items-center justify-center text-brand-muted mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <h3 class="font-display font-bold text-base text-brand-text mb-1">Bạn chưa có đơn hàng nào</h3>
                    <p class="text-xs text-brand-muted mb-6">Hãy dạo một vòng và tìm cuốn sách bạn yêu thích nhé.</p>
                    <a href="{{ route('books.index') }}" class="inline-flex font-bold text-xs text-white bg-brand-accent hover:bg-brand-accent-hover px-5 py-2.5 rounded-xl transition-colors shadow-sm">
                        Khám phá sách ngay
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

