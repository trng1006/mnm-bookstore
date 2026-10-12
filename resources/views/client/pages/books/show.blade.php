@extends('client.layouts.master')

@section('title', $book->title . ' - MNM Bookstore')

@section('content')
<div class="py-8">
    <!-- Breadcrumb -->
    <nav class="flex text-sm font-ui text-brand-muted mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-2">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="hover:text-brand-accent transition-colors">Trang chủ</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-brand-muted mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <a href="{{ route('books.index') }}" class="hover:text-brand-accent transition-colors">Tủ sách</a>
                </div>
            </li>
            @if($book->category)
            <li>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-brand-muted mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <span class="hover:text-brand-accent">{{ $book->category->name }}</span>
                </div>
            </li>
            @endif
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-brand-muted mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <span class="text-brand-text font-semibold truncate max-w-xs">{{ $book->title }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Product Info Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 bg-white p-6 md:p-10 rounded-[var(--radius-card)] border border-brand-border shadow-sm mb-12">
        <!-- Left: Book Cover (lg:col-span-5) -->
        <div class="lg:col-span-5 flex flex-col items-center">
            <div class="relative w-full max-w-sm aspect-[3/4] bg-brand-surface rounded-2xl overflow-hidden shadow-soft border border-brand-border flex items-center justify-center">
                @if($book->is_new)
                    <span class="absolute top-4 left-4 px-3 py-1 text-xs font-bold rounded-full z-10 shadow-sm bg-[#9DB67A] text-white">
                        MỚI
                    </span>
                @elseif($book->discount_percent)
                    <span class="absolute top-4 left-4 px-3 py-1 text-xs font-bold rounded-full z-10 shadow-sm bg-brand-accent text-white">
                        -{{ $book->discount_percent }}%
                    </span>
                @endif

                @if($book->cover_image)
                    <img src="{{ $book->cover_image }}" alt="{{ $book->title }}" 
                         class="w-full h-full object-cover"
                         onerror="this.outerHTML='<span class=\'font-ui font-medium opacity-50\'>Hình Bìa Sách</span>'">
                @else
                    <span class="font-ui font-medium opacity-50">Hình Bìa Sách</span>
                @endif
                <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-r from-black/10 to-transparent"></div>
            </div>

            <!-- Policy Chips -->
            <div class="grid grid-cols-2 gap-3 w-full max-w-sm mt-6 text-xs font-ui text-brand-muted">
                <div class="flex items-center gap-2 p-2.5 bg-brand-surface rounded-xl border border-brand-border/60">
                    <svg class="w-4 h-4 text-brand-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Sách thật chính hãng 100%</span>
                </div>
                <div class="flex items-center gap-2 p-2.5 bg-brand-surface rounded-xl border border-brand-border/60">
                    <svg class="w-4 h-4 text-brand-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Đổi trả trong 7 ngày</span>
                </div>
            </div>
        </div>

        <!-- Right: Book Details & Actions (lg:col-span-7) -->
        <div class="lg:col-span-7 flex flex-col justify-between">
            <div>
                <!-- Title & Meta -->
                <h1 class="font-display text-2xl md:text-3xl lg:text-4xl font-bold text-brand-text mb-3 leading-tight">
                    {{ $book->title }}
                </h1>

                <div class="flex flex-wrap items-center gap-4 text-sm font-ui mb-5">
                    <div class="text-brand-muted">
                        Tác giả: <span class="text-brand-text font-semibold">{{ $book->author->name ?? 'Chưa rõ' }}</span>
                    </div>
                    @if($book->category)
                    <div class="text-brand-muted">
                        Thể loại: <span class="text-brand-text font-semibold">{{ $book->category->name }}</span>
                    </div>
                    @endif
                    <!-- Rating Stars -->
                    <div class="flex items-center gap-1.5 pl-4 border-l border-brand-border">
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= round($book->rating) ? 'fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="font-bold text-brand-text">{{ number_format($book->rating, 1) }}</span>
                        <a href="#reviews-section" class="text-brand-muted hover:text-brand-accent underline ml-1">({{ $book->reviews_count }} đánh giá)</a>
                    </div>
                </div>

                <!-- Price Box -->
                <div class="bg-brand-surface p-4 md:p-6 rounded-2xl border border-brand-border/80 mb-6 flex flex-wrap items-baseline gap-4">
                    <span class="font-display text-3xl md:text-4xl font-bold text-brand-accent">
                        {{ number_format($book->price_current, 0, ',', '.') }} ₫
                    </span>
                    @if($book->price_old)
                        <span class="font-ui text-lg text-brand-muted line-through">
                            {{ number_format($book->price_old, 0, ',', '.') }} ₫
                        </span>
                        @if($book->discount_percent)
                            <span class="px-2.5 py-1 text-xs font-bold text-white bg-brand-accent rounded-full">
                                Tiết kiệm {{ $book->discount_percent }}%
                            </span>
                        @endif
                    @endif
                </div>

                <!-- Specs Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-white border border-brand-border mb-6 text-sm font-ui">
                    <div>
                        <span class="text-brand-muted text-xs block mb-1">Nhà xuất bản</span>
                        <span class="font-semibold text-brand-text">{{ $book->publisher ?? 'NXB Văn Học' }}</span>
                    </div>
                    <div>
                        <span class="text-brand-muted text-xs block mb-1">Năm xuất bản</span>
                        <span class="font-semibold text-brand-text">{{ $book->publication_year ?? 2023 }}</span>
                    </div>
                    <div>
                        <span class="text-brand-muted text-xs block mb-1">Số trang</span>
                        <span class="font-semibold text-brand-text">{{ $book->pages ?? '---' }} trang</span>
                    </div>
                    <div>
                        <span class="text-brand-muted text-xs block mb-1">Tình trạng kho</span>
                        @if($book->hasInStock())
                            <span class="font-semibold text-emerald-600 flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Còn hàng ({{ $book->stock }})
                            </span>
                        @else
                            <span class="font-semibold text-red-500 flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span> Tạm hết hàng
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Synopsis / Description -->
                <div class="mb-8">
                    <h3 class="font-display text-lg font-bold text-brand-text mb-2">Tóm tắt nội dung</h3>
                    <div class="text-brand-muted font-ui leading-relaxed text-sm md:text-base prose max-w-none">
                        {!! nl2br(e(strip_tags($book->description ?? 'Đang cập nhật tóm tắt nội dung cho tác phẩm này.'))) !!}
                    </div>
                </div>
            </div>

            <!-- Action Form: Quantity & Add to Cart / Buy Now -->
            @if($book->hasInStock())
                <form method="POST" action="{{ route('cart.add') }}" class="pt-6 border-t border-brand-border">
                    @csrf
                    <input type="hidden" name="book_id" value="{{ $book->id }}">

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                        <!-- Quantity Stepper -->
                        <div class="flex items-center border border-brand-border rounded-xl overflow-hidden bg-white w-36 h-12 flex-shrink-0 shadow-sm">
                            <button type="button" onclick="decrementQty()" class="w-10 h-full flex items-center justify-center text-brand-text hover:bg-brand-surface font-bold text-lg transition-colors cursor-pointer">−</button>
                            <input type="number" id="quantity-input" name="quantity" value="1" min="1" max="{{ $book->stock }}" class="w-16 h-full text-center font-ui font-bold text-brand-text border-none focus:outline-none [-moz-appearance:textfield] [&::-webkit-inner-spin-button]:m-0 [&::-webkit-inner-spin-button]:appearance-none">
                            <button type="button" onclick="incrementQty({{ $book->stock }})" class="w-10 h-full flex items-center justify-center text-brand-text hover:bg-brand-surface font-bold text-lg transition-colors cursor-pointer">+</button>
                        </div>

                        <!-- Add to cart -->
                        <button type="submit" class="flex-1 font-ui font-bold text-brand-text bg-brand-surface hover:bg-brand-border border border-brand-border rounded-xl h-12 px-6 flex items-center justify-center gap-2 transition-all duration-300 shadow-sm hover:shadow">
                            <svg class="w-5 h-5 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Thêm vào giỏ hàng
                        </button>

                        <!-- Buy Now -->
                        <button type="submit" name="buy_now" value="1" class="flex-1 font-ui font-bold text-white bg-brand-accent hover:bg-brand-accent-hover rounded-xl h-12 px-6 flex items-center justify-center gap-2 transition-all duration-300 shadow-soft hover:shadow-hover hover:-translate-y-0.5">
                            Mua ngay
                        </button>
                        
                        <!-- Wishlist Toggle -->
                        @auth
                            @php
                                $isWished = \App\Models\Wishlist::where('user_id', auth()->id())->where('book_id', $book->id)->exists();
                            @endphp
                            <button type="button" id="wishlist-btn" class="w-12 h-12 flex-shrink-0 flex items-center justify-center rounded-xl border border-brand-border {{ $isWished ? 'bg-red-50' : 'bg-white' }} hover:bg-red-50 text-red-500 transition-colors shadow-sm" data-book-id="{{ $book->id }}">
                                <svg class="w-6 h-6 {{ $isWished ? 'fill-current' : 'fill-none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </button>
                        @endauth
                    </div>

                    @guest
                    <p class="text-xs text-brand-muted font-ui mt-2">
                        * Có thể thanh toán không cần đăng nhập (khách vãng lai). <a href="{{ route('login') }}" class="text-brand-accent underline">Đăng nhập</a> để sử dụng tính năng Yêu thích sách.
                    </p>
                    @endguest
                </form>
            @else
                <div class="p-4 bg-red-50 text-red-600 rounded-xl border border-red-200 text-center font-ui font-medium">
                    Sản phẩm hiện đang tạm hết hàng. Quý độc giả vui lòng quay lại sau!
                </div>
            @endif
        </div>
    </div>

    <!-- Reviews & Ratings Section -->
    <section id="reviews-section" class="bg-white p-6 md:p-10 rounded-[var(--radius-card)] border border-brand-border shadow-sm mb-16">
        <h2 class="font-display text-2xl font-bold text-brand-text mb-8">Đánh giá & Bình luận từ độc giả</h2>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10 pb-8 border-b border-brand-border">
            <!-- Overall Score (4 cols) -->
            <div class="lg:col-span-4 flex flex-col items-center justify-center p-6 bg-brand-surface rounded-2xl border border-brand-border/80 text-center">
                <div class="font-display text-5xl font-bold text-brand-accent mb-2">
                    {{ number_format($book->rating, 1) }}<span class="text-2xl text-brand-muted font-normal">/5</span>
                </div>
                <div class="flex text-yellow-400 mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= round($book->rating) ? 'fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <p class="font-ui text-sm text-brand-muted">Dựa trên {{ $book->reviews->count() }} lượt đánh giá</p>
            </div>

            <!-- Star Breakdown (8 cols) -->
            <div class="lg:col-span-8 flex flex-col justify-center space-y-2.5 font-ui">
                @foreach([5, 4, 3, 2, 1] as $star)
                    @php
                        $count = $ratingCounts[$star] ?? 0;
                        $totalReviews = $book->reviews->count();
                        $percent = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-3 text-sm">
                        <span class="w-12 text-brand-muted flex items-center gap-1 font-semibold">{{ $star }} ★</span>
                        <div class="flex-1 h-3 bg-brand-surface rounded-full overflow-hidden border border-brand-border/50">
                            <div class="h-full bg-yellow-400 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                        </div>
                        <span class="w-12 text-right text-brand-muted text-xs">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Write a Review Form -->
        <div class="mb-12 bg-brand-surface p-6 rounded-2xl border border-brand-border/80">
            <h3 class="font-display text-lg font-bold text-brand-text mb-3">
                {{ $userExistingReview ? 'Cập nhật đánh giá của bạn' : 'Viết đánh giá của bạn' }}
            </h3>

            @auth
                @if($userCanReview)
                    <div class="mb-4 inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-full text-xs font-ui font-semibold">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        Xác nhận đã mua hàng tại MNM Bookstore
                    </div>

                    <form method="POST" action="{{ route('books.reviews.store', $book->slug) }}">
                        @csrf
                        
                        <!-- Rating Star Picker -->
                        <div class="mb-4">
                            <label class="block font-ui text-sm font-semibold text-brand-text mb-2">Đánh giá của bạn:</label>
                            <div class="flex items-center gap-3">
                                <div class="flex flex-row-reverse justify-end items-center gap-1 rating-group">
                                    @php
                                        $currentRating = old('rating', $userExistingReview->rating ?? 5);
                                    @endphp
                                    @for($i = 5; $i >= 1; $i--)
                                        <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" class="hidden peer" {{ (int) $currentRating === $i ? 'checked' : '' }}>
                                        <label for="star{{ $i }}" class="cursor-pointer text-gray-300 peer-checked:text-yellow-400 hover:text-yellow-400 peer-hover:text-yellow-400 transition-colors">
                                            <svg class="w-7 h-7 fill-current" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </label>
                                    @endfor
                                </div>
                                <span class="text-xs text-brand-muted font-ui">(Chọn từ 1 đến 5 sao)</span>
                            </div>
                            @error('rating')
                                <p class="text-xs text-red-500 mt-1 font-ui">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Comment textarea -->
                        <div class="mb-4">
                            <label for="comment" class="block font-ui text-sm font-semibold text-brand-text mb-2">Nhận xét chi tiết:</label>
                            <textarea id="comment" name="comment" rows="4" required placeholder="Chia sẻ cảm nhận của bạn về cuốn sách này..." class="w-full bg-white border border-brand-border rounded-xl p-3.5 text-sm font-ui focus:outline-none focus:border-brand-accent focus:ring-1 focus:ring-brand-accent transition-colors shadow-sm">{{ old('comment', $userExistingReview->comment ?? '') }}</textarea>
                            @error('comment')
                                <p class="text-xs text-red-500 mt-1 font-ui">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="font-ui font-bold text-white bg-brand-text hover:bg-brand-accent transition-colors rounded-xl px-6 py-2.5 shadow-sm text-sm">
                            {{ $userExistingReview ? 'Cập nhật nhận xét' : 'Gửi nhận xét' }}
                        </button>
                    </form>
                @else
                    <div class="p-5 bg-amber-50/80 border border-amber-200/90 rounded-2xl flex items-start gap-3.5 text-amber-900 font-ui">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <h4 class="font-bold text-sm text-amber-900">Chỉ độc giả đã mua sản phẩm mới có thể đánh giá</h4>
                            <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                                Để đảm bảo tính xác thực và trải nghiệm thực tế cho cộng đồng, bạn chỉ có thể gửi đánh giá cho những cuốn sách đã được đặt mua và nhận hàng thành công tại MNM Bookstore. Bạn có thể đánh giá từng quyển trực tiếp trong trang <a href="{{ route('account.profile') }}" class="font-bold underline hover:text-amber-950">Chi tiết đơn hàng</a> sau khi nhận sách.
                            </p>
                        </div>
                    </div>
                @endif
            @else
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 bg-white rounded-xl border border-brand-border">
                    <div>
                        <h4 class="font-ui font-bold text-sm text-brand-text">Bạn đã đọc cuốn sách này?</h4>
                        <p class="font-ui text-xs text-brand-muted mt-0.5">Vui lòng đăng nhập tài khoản đã mua sách để gửi nhận xét và đánh giá.</p>
                    </div>
                    <a href="{{ route('login') }}" class="font-ui font-bold text-xs text-white bg-brand-accent hover:bg-brand-accent-hover px-4 py-2.5 rounded-xl transition-colors shadow-sm whitespace-nowrap">
                        Đăng nhập ngay
                    </a>
                </div>
            @endauth
        </div>

        <!-- Review List -->
        <div class="space-y-6">
            @forelse($book->reviews as $review)
                <div class="border-b border-brand-border/60 pb-6 last:border-none last:pb-0">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-brand-accent/20 text-brand-accent flex items-center justify-center font-bold text-sm font-ui">
                                {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="font-ui font-bold text-brand-text text-sm">{{ $review->user->name ?? 'Người dùng' }}</h4>
                                    <span class="inline-flex items-center gap-0.5 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        Đã mua hàng
                                    </span>
                                </div>
                                <span class="text-xs text-brand-muted font-ui">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <!-- Stars -->
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                    </div>

                    <p class="font-ui text-sm text-brand-text leading-relaxed pl-12">
                        {{ $review->comment }}
                    </p>
                </div>
            @empty
                <div class="text-center py-8 text-brand-muted font-ui text-sm">
                    Chưa có đánh giá nào cho cuốn sách này. Hãy là người đầu tiên chia sẻ cảm nhận!
                </div>
            @endforelse
        </div>
    </section>

    <!-- Related Books Section -->
    @if($relatedBooks->count() > 0)
    <section class="py-6">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="font-display text-2xl md:text-3xl font-bold text-brand-text">Có thể bạn sẽ thích</h2>
                <p class="text-brand-muted font-ui text-sm">Những tựa sách cùng thể loại hoặc chủ đề liên quan.</p>
            </div>
            <a href="{{ route('books.index') }}" class="font-ui font-bold text-sm text-brand-accent hover:text-brand-accent-hover transition-colors">
                Xem tất cả →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($relatedBooks as $related)
                <div class="group block bg-white p-4 rounded-[var(--radius-card)] transition-all duration-500 hover:shadow-hover hover:-translate-y-2 border border-brand-border border-opacity-50 flex flex-col h-full">
                    <a href="{{ route('books.show', $related->slug) }}" class="block relative bg-brand-surface aspect-[3/4] mb-4 rounded-2xl flex items-center justify-center text-brand-muted text-sm overflow-hidden">
                        @if($related->is_new)
                            <span class="absolute top-3 left-3 px-2.5 py-0.5 text-xs font-bold rounded-full z-10 shadow-sm bg-[#9DB67A] text-white">
                                MỚI
                            </span>
                        @elseif($related->discount_percent)
                            <span class="absolute top-3 left-3 px-2.5 py-0.5 text-xs font-bold rounded-full z-10 shadow-sm bg-brand-accent text-white">
                                -{{ $related->discount_percent }}%
                            </span>
                        @endif

                        @if($related->cover_image)
                            <img src="{{ $related->cover_image }}" alt="{{ $related->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                                 onerror="this.outerHTML='<span class=\'font-ui font-medium opacity-50\'>Hình Bìa</span>'">
                        @else
                            <span class="font-ui font-medium opacity-50">Hình Bìa</span>
                        @endif
                    </a>

                    <div class="px-1 flex flex-col flex-grow">
                        <a href="{{ route('books.show', $related->slug) }}">
                            <h3 class="font-display text-base font-bold mb-1 group-hover:text-brand-accent transition-colors line-clamp-2" title="{{ $related->title }}">{{ $related->title }}</h3>
                        </a>
                        <div class="text-brand-muted text-xs font-ui mb-2">{{ $related->author->name ?? 'Chưa rõ tác giả' }}</div>

                        <div class="flex items-center gap-2 mt-auto mb-3">
                            <span class="font-bold text-base text-brand-accent">{{ number_format($related->price_current, 0, ',', '.') }} ₫</span>
                            @if($related->price_old)
                                <span class="font-ui text-xs text-brand-muted line-through">{{ number_format($related->price_old, 0, ',', '.') }} ₫</span>
                            @endif
                        </div>

                        <a href="{{ route('books.show', $related->slug) }}" class="text-center font-ui text-xs font-bold text-brand-text bg-brand-surface hover:bg-brand-border rounded-xl py-2 transition-colors">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif
</div>

@push('scripts')
<script>
    function incrementQty(max) {
        const input = document.getElementById('quantity-input');
        let val = parseInt(input.value) || 1;
        if (val < max) {
            input.value = val + 1;
        }
    }

    function decrementQty() {
        const input = document.getElementById('quantity-input');
        let val = parseInt(input.value) || 1;
        if (val > 1) {
            input.value = val - 1;
        }
    }

    const wishlistBtn = document.getElementById('wishlist-btn');
    if (wishlistBtn) {
        wishlistBtn.addEventListener('click', function(e) {
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
                    const svg = this.querySelector('svg');
                    if (data.is_wished) {
                        this.classList.add('bg-red-50');
                        this.classList.remove('bg-white');
                        svg.classList.add('fill-current');
                        svg.classList.remove('fill-none');
                    } else {
                        this.classList.remove('bg-red-50');
                        this.classList.add('bg-white');
                        svg.classList.remove('fill-current');
                        svg.classList.add('fill-none');
                    }
                    // Optional: show a toast notification
                }
            })
            .catch(err => console.error(err));
        });
    }
</script>
@endpush
@endsection

