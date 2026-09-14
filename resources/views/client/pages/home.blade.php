@extends('client.layouts.master')

@section('title', 'MNM Bookstore - Khám Phá Tri Thức')

@section('content')
    <!-- 1. Hero Section -->
    <section class="py-16 md:py-24 text-center">
        <h1 class="font-display text-4xl md:text-5xl lg:text-6xl mb-6 font-bold text-brand-text leading-tight max-w-4xl mx-auto">
            Nuôi dưỡng tâm hồn qua từng trang sách.
        </h1>
        <p class="text-brand-muted max-w-2xl mx-auto text-lg md:text-xl font-ui font-medium leading-relaxed mb-10">
            Khám phá những câu chuyện đáng nhớ, những tác phẩm kinh điển và những cuốn sách mới được tuyển chọn dành riêng cho bạn.
        </p>
        <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
            <a href="{{ route('books.index') }}" class="inline-flex items-center justify-center w-full sm:w-auto font-ui font-bold text-white bg-brand-text hover:bg-brand-accent transition-all duration-300 px-8 py-4 rounded-[var(--radius-button)] shadow-soft hover:shadow-hover hover:-translate-y-1">
                Khám phá sách
            </a>
            <a href="#" class="inline-flex items-center justify-center w-full sm:w-auto font-ui font-bold text-brand-text bg-brand-surface hover:bg-brand-border transition-all duration-300 px-8 py-4 rounded-[var(--radius-button)]">
                Xem sách bán chạy
            </a>
        </div>
    </section>

    <!-- 2. Danh mục nổi bật -->
    <section class="py-12 border-t border-brand-border border-opacity-50">
        <h2 class="font-display text-2xl font-bold mb-8 text-center">Danh mục nổi bật</h2>
        <div class="flex flex-wrap justify-center gap-3 md:gap-4">
            @foreach($categories as $category)
                <a href="#" class="px-6 py-3 bg-brand-surface hover:bg-brand-accent hover:text-white hover:shadow-soft transition-all duration-300 rounded-full font-ui font-semibold text-brand-text border border-brand-border">
                    {{ $category }}
                </a>
            @endforeach
        </div>
    </section>

    <!-- 3. Sách bán chạy (Best Sellers) -->
    <section class="py-16">
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-4">
            <div>
                <h2 class="font-display text-3xl font-bold mb-2">Sách bán chạy</h2>
                <p class="text-brand-muted font-ui">Những tác phẩm được độc giả yêu thích nhất.</p>
            </div>
            <a href="{{ route('books.index') }}" class="font-ui font-bold text-brand-accent hover:text-brand-accent-hover transition-colors flex items-center gap-1">
                Xem tất cả 
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            @foreach($bestSellers as $book)
                <div class="group block bg-white p-4 rounded-[var(--radius-card)] transition-all duration-500 hover:shadow-hover hover:-translate-y-2 border border-brand-border border-opacity-50 flex flex-col h-full">
                    
                    <a href="{{ route('books.show', $book->slug) }}" class="block relative bg-brand-surface aspect-[3/4] mb-5 rounded-2xl flex items-center justify-center text-brand-muted text-sm overflow-hidden">
                        @if($book->hasDiscount)
                            <span class="absolute top-3 left-3 px-3 py-1 text-xs font-bold rounded-full z-10 shadow-sm {{ $book->tagColor }}">
                                {{ $book->tagText }}
                            </span>
                        @endif
                        @if($book->cover)
                            <img src="{{ $book->cover }}" alt="{{ $book->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                                 onerror="this.outerHTML='<span class=\'font-ui font-medium opacity-50 group-hover:scale-105 transition-transform duration-700\'>Hình Bìa</span>'">
                        @else
                            <span class="font-ui font-medium opacity-50 group-hover:scale-105 transition-transform duration-700">Hình Bìa</span>
                        @endif
                        <div class="absolute inset-y-0 left-0 w-1 bg-gradient-to-r from-black/5 to-transparent"></div>
                    </a>
                    
                    <div class="px-2 flex flex-col flex-grow">
                        <a href="{{ route('books.show', $book->slug) }}">
                            <h3 class="font-display text-lg font-bold mb-1 group-hover:text-brand-accent transition-colors line-clamp-2" title="{{ $book->title }}">{{ $book->title }}</h3>
                        </a>
                        <div class="text-brand-muted text-sm font-ui mb-2">{{ $book->author }}</div>
                        
                        <!-- Rating -->
                        <div class="flex items-center gap-1 mb-3">
                            <span class="text-yellow-500 text-sm">★</span>
                            <span class="font-ui text-sm font-bold text-brand-text">{{ $book->rating }}</span>
                        </div>

                        <!-- Price -->
                        <div class="flex items-center gap-3 mt-auto mb-4">
                            <span class="font-bold text-lg text-brand-accent">{{ number_format($book->price, 0, ',', '.') }} ₫</span>
                            @if($book->originalPrice)
                                <span class="font-ui text-sm text-brand-muted line-through">{{ number_format($book->originalPrice, 0, ',', '.') }} ₫</span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <form method="POST" action="{{ route('cart.add') }}" class="grid grid-cols-2 gap-2 mt-auto">
                            @csrf
                            <input type="hidden" name="book_id" value="{{ $book->id }}">
                            <button type="submit" class="font-ui text-xs font-bold text-brand-text bg-brand-surface hover:bg-brand-border rounded-xl py-2.5 transition-colors cursor-pointer">Thêm vào giỏ</button>
                            <button type="submit" name="buy_now" value="1" class="font-ui text-xs font-bold text-white bg-brand-text hover:bg-brand-accent rounded-xl py-2.5 transition-colors shadow-sm cursor-pointer">Mua ngay</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- 4. Banner Khuyến Mãi -->
    <section class="py-12 my-8">
        <div class="bg-brand-text rounded-[var(--radius-card)] p-8 md:p-12 lg:p-16 text-center text-white relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-white opacity-5 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-brand-accent opacity-20 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 max-w-2xl mx-auto">
                <span class="font-ui font-bold tracking-widest text-brand-accent mb-4 block uppercase text-sm">Ưu đãi dành cho người yêu sách</span>
                <h2 class="font-display text-3xl md:text-5xl font-bold mb-6 leading-tight">Giảm đến 20% cho một số tựa sách được chọn</h2>
                <p class="font-ui text-brand-bg opacity-80 mb-8 text-lg">Khám phá các tựa sách kinh điển và best-seller với mức giá ưu đãi chưa từng có. Chương trình có hạn.</p>
                <a href="#" class="inline-block font-ui font-bold text-brand-text bg-white hover:bg-brand-accent hover:text-white transition-all duration-300 px-8 py-4 rounded-full shadow-lg">
                    Xem ưu đãi ngay
                </a>
            </div>
        </div>
    </section>

    <!-- 5. Sách Mới -->
    <section class="py-16" id="sach-moi">
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-4">
            <div>
                <h2 class="font-display text-3xl font-bold mb-2">Sách mới</h2>
                <p class="text-brand-muted font-ui">Những câu chuyện vừa được đưa lên kệ.</p>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Filter/Sort -->
                <div class="flex items-center gap-2">
                    <label for="sort" class="font-ui text-brand-text font-medium text-sm">Sắp xếp:</label>
                    <select name="sort" id="sort-new-books" class="bg-white border border-brand-border text-brand-text text-sm rounded-lg focus:ring-brand-accent focus:border-brand-accent block p-2 outline-none font-ui shadow-sm cursor-pointer">
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Phổ biến nhất</option>
                        <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Giá: Thấp đến Cao</option>
                        <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Giá: Cao đến Thấp</option>
                        <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Tên: A - Z</option>
                        <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Tên: Z - A</option>
                    </select>
                </div>

                <a href="{{ route('books.index') }}" class="font-ui font-bold text-brand-accent hover:text-brand-accent-hover transition-colors flex items-center gap-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent rounded-sm">
                    Xem tất cả 
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <div id="new-books-container" class="relative min-h-[400px]">
            @include('client.pages.partials.new_books_grid')
        </div>
    </section>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('new-books-container');
            const sortSelect = document.getElementById('sort-new-books');

            function fetchNewBooks(url) {
                container.style.opacity = '0.5';
                container.style.pointerEvents = 'none';

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';
                })
                .catch(error => {
                    console.error('Error fetching books:', error);
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';
                });
            }

            // Handle sort change
            sortSelect.addEventListener('change', function() {
                const sortValue = this.value;
                const url = new URL(window.location.href);
                url.pathname = '/';
                url.searchParams.set('sort', sortValue);
                url.searchParams.delete('page'); // Reset to page 1 on sort
                fetchNewBooks(url.toString());
            });

            // Handle pagination clicks using Event Delegation
            container.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                // Only intercept pagination links (they usually have a page parameter)
                if (link && link.href && link.href.includes('page=')) {
                    e.preventDefault();
                    fetchNewBooks(link.href);
                    
                    // Optional: Scroll to section smoothly if desired, or let it stay in place
                    document.getElementById('sach-moi').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
    @endpush

    <!-- 6. Vì sao chọn MNM? -->
    <section class="py-20 border-t border-brand-border">
        <h2 class="font-display text-3xl font-bold mb-12 text-center">Vì sao chọn MNM Bookstore?</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($features as $feature)
                <div class="text-center px-4">
                    <div class="w-16 h-16 mx-auto bg-brand-surface text-brand-accent rounded-2xl flex items-center justify-center mb-6 shadow-sm rotate-3 hover:rotate-0 transition-transform duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"></path>
                        </svg>
                    </div>
                    <h3 class="font-display text-xl font-bold mb-3">{{ $feature['title'] }}</h3>
                    <p class="font-ui text-brand-muted">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- 7. Newsletter -->
    <section class="py-16 mb-8">
        <div class="bg-brand-surface rounded-[var(--radius-card)] p-8 md:p-12 text-center max-w-4xl mx-auto border border-brand-border">
            <h2 class="font-display text-3xl font-bold mb-4">Nhận thông tin ưu đãi</h2>
            <p class="font-ui text-brand-muted mb-8 max-w-lg mx-auto">Đăng ký bản tin để không bỏ lỡ những tựa sách mới và ưu đãi đặc biệt từ MNM Bookstore.</p>
            <form class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
                <input type="email" placeholder="Nhập địa chỉ email của bạn..." required class="flex-1 bg-white border border-brand-border rounded-full py-3 px-6 focus:outline-none focus:border-brand-accent font-ui transition-colors shadow-sm">
                <button type="submit" class="font-ui font-bold text-white bg-brand-text hover:bg-brand-accent transition-colors rounded-full px-8 py-3 shadow-sm whitespace-nowrap">Đăng ký</button>
            </form>
        </div>
    </section>
@endsection
