@extends('client.layouts.master')

@section('title', 'Tủ sách - MNM Bookstore')

@section('content')
<div class="py-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold text-brand-text mb-2">
                @if(request('search'))
                    Kết quả tìm kiếm cho "{{ request('search') }}"
                @elseif(request('category'))
                    Danh mục: {{ $categories->firstWhere('slug', request('category'))->name ?? request('category') }}
                @else
                    Tủ sách của chúng tôi
                @endif
            </h1>
            <div class="flex items-center gap-3">
                <p class="text-brand-muted font-ui">Tìm thấy {{ $books->total() }} tựa sách</p>
                @if(request('search') || request('category'))
                    <a href="{{ route('books.index') }}" class="text-xs font-ui text-brand-accent hover:underline font-semibold flex items-center gap-1">
                        ✕ Xóa bộ lọc
                    </a>
                @endif
            </div>
        </div>

        <!-- Filter/Sort -->
        <form method="GET" action="{{ route('books.index') }}" class="flex flex-wrap items-center gap-3">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

            <!-- Category Dropdown -->
            <div class="flex items-center gap-2">
                <label for="category" class="font-ui text-brand-text font-medium text-sm hidden sm:inline">Danh mục:</label>
                <select aria-label="Danh mục sách" name="category" id="category" onchange="this.form.submit()" class="bg-white border border-brand-border text-brand-text text-sm rounded-lg focus:ring-brand-accent focus:border-brand-accent block p-2.5 outline-none font-ui shadow-sm cursor-pointer">
                    <option value="">Tất cả danh mục ({{ $categories->sum('books_count') }})</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" {{ ($selectedCategory ?? '') === $cat->slug ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ $cat->books_count }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sort Dropdown -->
            <div class="flex items-center gap-2">
                <label for="sort" class="font-ui text-brand-text font-medium text-sm hidden sm:inline">Sắp xếp:</label>
                <select aria-label="Sắp xếp" name="sort" id="sort" onchange="this.form.submit()" class="bg-white border border-brand-border text-brand-text text-sm rounded-lg focus:ring-brand-accent focus:border-brand-accent block p-2.5 outline-none font-ui shadow-sm cursor-pointer">
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Phổ biến nhất</option>
                    <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Giá: Thấp đến Cao</option>
                    <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Giá: Cao đến Thấp</option>
                    <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Tên: A - Z</option>
                    <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Tên: Z - A</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8 mb-12">
        @forelse($books as $book)
            <div class="group block bg-white p-4 rounded-[var(--radius-card)] transition-all duration-500 hover:shadow-hover hover:-translate-y-2 border border-brand-border border-opacity-50 flex flex-col h-full">
                
                <!-- Image -->
                <div class="relative mb-5">
                    <a href="{{ route('books.show', $book->slug) }}" class="block relative bg-brand-surface aspect-[3/4] rounded-2xl flex items-center justify-center text-brand-muted text-sm overflow-hidden">
                        @if($book->is_new)
                            <span class="absolute top-3 left-3 px-3 py-1 text-xs font-bold rounded-full z-10 shadow-sm bg-[#4A6B22] text-white">
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
                                 onerror="this.outerHTML='<span class=\'font-ui font-medium text-brand-text/80 group-hover:scale-105 transition-transform duration-700\'>Hình Bìa</span>'">
                        @else
                            <span class="font-ui font-medium text-brand-text/80 group-hover:scale-105 transition-transform duration-700">Hình Bìa</span>
                        @endif
                        <div class="absolute inset-y-0 left-0 w-1 bg-gradient-to-r from-black/5 to-transparent"></div>
                    </a>
                    <!-- Nút Yêu Thích -->
                    <form method="POST" action="{{ route('account.wishlist.toggle') }}" class="absolute top-3 right-3 z-20 ajax-wishlist-form">
                        @csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <button type="submit" class="w-8 h-8 flex items-center justify-center bg-white/90 backdrop-blur rounded-full text-brand-muted hover:text-red-500 hover:bg-white transition-all shadow-sm" aria-label="Thêm vào yêu thích">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </button>
                    </form>
                </div>
                
                <!-- Info -->
                <div class="px-2 flex flex-col flex-grow">
                    <a href="{{ route('books.show', $book->slug) }}">
                        <h2 class="font-display text-lg font-bold mb-1 group-hover:text-brand-accent transition-colors line-clamp-2" title="{{ $book->title }}">{{ $book->title }}</h2>
                    </a>
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

                    <!-- Actions -->
                    <form method="POST" action="{{ route('cart.add') }}" class="grid grid-cols-2 gap-2 mt-auto">
                        @csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <button type="submit" class="font-ui text-xs font-bold text-brand-text bg-brand-surface hover:bg-brand-border rounded-xl py-2.5 transition-colors cursor-pointer" title="Thêm vào giỏ hàng">
                            Giỏ hàng
                        </button>
                        <button type="submit" name="buy_now" value="1" class="font-ui text-xs font-bold text-white bg-brand-text hover:bg-brand-accent rounded-xl py-2.5 transition-colors shadow-sm cursor-pointer" title="Mua ngay">
                            Mua ngay
                        </button>
                    </form>
                </div>
            </div>
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
