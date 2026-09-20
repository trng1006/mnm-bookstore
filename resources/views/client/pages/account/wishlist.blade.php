@extends('client.layouts.master')
@section('title', 'Sách yêu thích')
@section('content')
<div class="py-8 font-ui">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left: Sidebar Profile Info -->
        <div class="lg:col-span-4 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 md:p-8 shadow-sm h-fit">
            <div class="text-center pb-6 border-b border-brand-border">
                <div class="w-20 h-20 bg-brand-surface rounded-full flex items-center justify-center text-2xl font-display font-bold text-brand-text mx-auto mb-4 border border-brand-border shadow-inner">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <h3 class="font-display text-lg font-bold text-brand-text">{{ auth()->user()->name }}</h3>
                <span class="text-xs text-brand-muted">{{ auth()->user()->email }}</span>
                <div class="mt-2">
                    <span class="inline-block px-3 py-0.5 rounded-full text-xs font-semibold bg-brand-surface border border-brand-border text-brand-text">
                        Khách hàng thành viên
                    </span>
                </div>
            </div>
            
            <!-- Menu links -->
            <div class="mt-6 flex flex-col gap-2">
                <a href="{{ route('account.profile') }}" class="w-full px-4 py-2 text-sm font-semibold text-brand-text border border-brand-border rounded-lg text-center hover:bg-brand-surface">Hồ sơ & Đơn hàng</a>
                <a href="{{ route('account.wishlist.index') }}" class="w-full px-4 py-2 text-sm font-semibold text-brand-accent bg-brand-surface border border-brand-accent rounded-lg text-center">Danh sách Yêu thích</a>
                <a href="{{ route('account.notifications.index') }}" class="w-full px-4 py-2 text-sm font-semibold text-brand-text border border-brand-border rounded-lg text-center hover:bg-brand-surface">Thông báo</a>
            </div>
        </div>

        <!-- Right: Main Content -->
        <div class="lg:col-span-8 bg-white rounded-[var(--radius-card)] border border-brand-border p-6 shadow-sm">
            <h2 class="text-xl font-display font-bold text-brand-text mb-6">Sách yêu thích của tôi</h2>

            @if($wishlists->isEmpty())
                <div class="text-center py-10 text-brand-muted">
                    <svg class="w-16 h-16 mx-auto mb-4 text-brand-muted/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <p>Danh sách yêu thích của bạn đang trống.</p>
                    <a href="{{ route('books.index') }}" class="inline-block mt-4 text-brand-accent font-semibold hover:underline">Tiếp tục mua sắm</a>
                </div>
            @else
                <div class="grid grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($wishlists as $item)
                        @php $book = $item->book; @endphp
                        @if($book)
                            <div class="bg-white rounded-xl border border-brand-border overflow-hidden hover:shadow-hover transition-all duration-300 relative group">
                                <button class="wishlist-btn absolute top-3 right-3 z-10 w-8 h-8 flex items-center justify-center rounded-full bg-white text-red-500 shadow-sm hover:scale-110 transition-transform cursor-pointer" data-book-id="{{ $book->id }}" title="Xóa khỏi yêu thích">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </button>

                                <a href="{{ route('books.show', $book->slug) }}" class="block aspect-[2/3] relative overflow-hidden bg-brand-surface">
                                    @if($book->cover_image)
                                        <img src="{{ $book->cover_image }}" alt="{{ $book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-brand-muted text-sm">Chưa có bìa</div>
                                    @endif
                                </a>

                                <div class="p-4">
                                    <h3 class="font-display font-bold text-sm text-brand-text mb-1 line-clamp-2 min-h-[40px]">
                                        <a href="{{ route('books.show', $book->slug) }}" class="hover:text-brand-accent transition-colors">{{ $book->title }}</a>
                                    </h3>
                                    <div class="text-brand-accent font-bold text-base mt-2">
                                        {{ number_format($book->price_current, 0, ',', '.') }} ₫
                                    </div>
                                </div>
                                
                                <div class="px-4 pb-4">
                                    <form method="POST" action="{{ route('cart.add') }}">
                                        @csrf
                                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="w-full py-2 bg-brand-surface text-brand-text font-bold text-sm rounded-lg hover:bg-brand-accent hover:text-white transition-colors cursor-pointer">
                                            Thêm vào giỏ
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                
                <div class="mt-8">
                    {{ $wishlists->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.wishlist-btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const bookId = this.getAttribute('data-book-id');
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch("{{ route('account.wishlist.toggle') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                    "Accept": "application/json"
                },
                body: JSON.stringify({ book_id: bookId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(err => console.error(err));
        });
    });
});
</script>
@endsection
