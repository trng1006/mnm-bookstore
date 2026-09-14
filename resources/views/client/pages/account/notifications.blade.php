@extends('client.layouts.master')

@section('title', 'Thông báo của bạn - MNM Bookstore')

@section('content')
<div class="py-8 max-w-4xl mx-auto font-ui">
    <div class="flex items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-brand-text mb-1">Thông báo hệ thống</h1>
            <p class="text-xs text-brand-muted">Cập nhật về đơn hàng và tin nhắn ưu đãi dành cho bạn.</p>
        </div>

        @if(auth()->user()->unreadNotifications->count() > 0)
            <form method="POST" action="{{ route('account.notifications.readAll') }}">
                @csrf
                <button type="submit" class="text-xs font-bold text-brand-accent hover:underline cursor-pointer">
                    Đánh dấu tất cả đã đọc
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-[var(--radius-card)] border border-brand-border shadow-sm overflow-hidden divide-y divide-brand-border/60">
        @forelse($notifications as $notification)
            @php
                $isUnread = is_null($notification->read_at);
                $data = $notification->data;
            @endphp
            <div class="p-5 flex items-start gap-4 transition-colors {{ $isUnread ? 'bg-brand-accent/5' : 'hover:bg-brand-surface/40' }}">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isUnread ? 'bg-brand-accent text-white shadow-soft' : 'bg-brand-surface text-brand-muted' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <h4 class="font-bold text-sm text-brand-text {{ $isUnread ? 'text-brand-accent font-display' : '' }}">
                            {{ $data['title'] ?? 'Thông báo đơn hàng' }}
                        </h4>
                        <span class="text-[11px] text-brand-muted whitespace-nowrap">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <p class="text-xs text-brand-muted leading-relaxed mb-2">
                        {{ $data['message'] ?? '' }}
                    </p>

                    @if(!empty($data['url']))
                        <form method="POST" action="{{ route('account.notifications.read', $notification->id) }}" class="inline-block">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-brand-accent hover:underline flex items-center gap-1 cursor-pointer">
                                <span>Xem chi tiết</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </form>
                    @endif
                </div>

                @if($isUnread)
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-accent flex-shrink-0 mt-2" title="Chưa đọc"></span>
                @endif
            </div>
        @empty
            <div class="p-12 text-center text-brand-muted">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                <p class="text-sm font-semibold text-brand-text mb-1">Bạn chưa có thông báo nào</p>
                <p class="text-xs">Mọi cập nhật trạng thái đơn hàng sẽ xuất hiện tại đây.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</div>
@endsection

