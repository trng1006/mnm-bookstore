@extends('client.layouts.master')

@section('title', 'Tủ sách - MNM Bookstore')

@section('content')
<div class="py-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold text-brand-text mb-2">Tủ sách của chúng tôi</h1>
            <p class="text-brand-muted font-ui">Khám phá {{ $books->total() }} tựa sách thú vị</p>
        </div>

        <!-- Filter/Sort -->
        <form method="GET" action="{{ route('books.index') }}" class="flex items-center gap-3">
            <label for="sort" class="font-ui text-brand-text font-medium">Sắp xếp theo:</label>
            <select name="sort" id="sort" onchange="this.form.submit()" class="bg-white border border-brand-border text-brand-text text-sm rounded-lg focus:ring-brand-accent focus:border-brand-accent block p-2.5 outline-none font-ui shadow-sm cursor-pointer">
                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Phổ biến nhất</option>
                <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Giá: Thấp đến Cao</option>
                <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Giá: Cao đến Thấp</option>
                <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Tên: A - Z</option>
                <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Tên: Z - A</option>
            </select>
        </form>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8 mb-12">
        @forelse($books as $book)
            <a href="{{ route('books.show', $book->slug) }}" class="group block bg-white p-4 rounded-[var(--radius-card)] transition-all duration-500 hover:shadow-hover hover:-translate-y-2 border border-brand-border border-opacity-50 flex flex-col h-full">
                
                <!-- Image -->
                <div class="relative bg-brand-surface aspect-[3/4] mb-5 rounded-2xl flex items-center justify-center text-brand-muted text-sm overflow-hidden">
                    @if($book->is_new)
                        <span class="absolute top-3 left-3 px-3 py-1 text-xs font-bold rounded-full z-10 shadow-sm bg-[#9DB67A] text-white">
                            MỚI
                        </span>
                    @elseif($book->discount_percent)
                        <span class="absolute top-3 left-3 px-3 py-1 text-xs font-bold rounded-full z-10 shadow-sm bg-brand-accent text-white">
                            -{{ $book->discount_percent }}%
                        </span>
                    @endif
                    
                    @if($book->cover_image)
                        <img src="{{ $book->cover_image }}" alt="{{ $book->title }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                             onerror="this.outerHTML='<span class=\'font-ui font-medium opacity-50 group-hover:scale-105 transition-transform duration-700\'>Hình Bìa</span>'">
                    @else
                        <span class="font-ui font-medium opacity-50 group-hover:scale-105 transition-transform duration-700">Hình Bìa</span>
                    @endif
                    <div class="absolute inset-y-0 left-0 w-1 bg-gradient-to-r from-black/5 to-transparent"></div>
                </div>
                
                <!-- Info -->
                <div class="px-2 flex flex-col flex-grow">
                    <h3 class="font-display text-lg font-bold mb-1 group-hover:text-brand-accent transition-colors line-clamp-2" title="{{ $book->title }}">{{ $book->title }}</h3>
                    <div class="text-brand-muted text-sm font-ui mb-2">{{ $book->author->name ?? 'Chưa rõ tác giả' }}</div>
                    
                    <!-- Rating -->
                    <div class="flex items-center gap-1 mb-3">
                        <span class="text-yellow-500 text-sm">★</span>
                        <span class="font-ui text-sm font-bold text-brand-text">{{ $book->rating ?? '0.0' }}</span>
                        <span class="font-ui text-xs text-brand-muted">({{ $book->reviews_count ?? 0 }})</span>
                    </div>

                    <!-- Price -->
                    <div class="flex items-center gap-3 mt-auto mb-4">
                        <span class="font-bold text-lg text-brand-accent">{{ number_format($book->price_current, 0, ',', '.') }} ₫</span>
                        @if($book->price_old)
                            <span class="font-ui text-sm text-brand-muted line-through">{{ number_format($book->price_old, 0, ',', '.') }} ₫</span>
                        @endif
                    </div>

                    <!-- Action -->
                    <div class="grid grid-cols-2 gap-2 mt-auto">
                        <button class="font-ui text-sm font-bold text-brand-text bg-brand-surface hover:bg-brand-border rounded-xl py-2.5 transition-colors">Giỏ hàng</button>
                        <button class="font-ui text-sm font-bold text-white bg-brand-text hover:bg-brand-accent rounded-xl py-2.5 transition-colors shadow-sm">Mua ngay</button>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full py-12 text-center text-brand-muted font-ui">
                Không tìm thấy cuốn sách nào.
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="flex justify-center mt-8">
        {{ $books->links() }}
    </div>
</div>
@endsection
