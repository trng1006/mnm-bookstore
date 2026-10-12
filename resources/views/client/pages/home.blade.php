@extends('client.layouts.master')

@section('title', 'MNM Bookstore - Khám Phá Tri Thức')

@section('content')
    <!-- 1. Hero Banner Carousel (5 Banner Nhà Sách Độc Quyền) -->
    <style>
        .banner-carousel-box {
            position: relative;
            width: 100%;
            height: 460px;
            border-radius: 24px;
            overflow: hidden;
        }
        @media (max-width: 1024px) {
            .banner-carousel-box { height: 340px; }
        }
        @media (max-width: 640px) {
            .banner-carousel-box { height: 200px; border-radius: 16px; }
        }
        .banner-slide-item {
            min-width: 100%;
            width: 100%;
            height: 100%;
            flex-shrink: 0;
            display: block;
            position: relative;
        }
        .banner-slide-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Marquee Slide Fade Edges & Calmer Speed */
        @keyframes marquee-slide {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee {
            display: flex;
            width: max-content;
            animation: marquee-slide 60s linear infinite;
            will-change: transform;
        }
        .animate-marquee:hover,
        .animate-marquee:focus-within {
            animation-play-state: paused;
        }
        .marquee-fade-container {
            -webkit-mask-image: linear-gradient(to right, transparent 0%, rgba(0, 0, 0, 1) 140px, rgba(0, 0, 0, 1) calc(100% - 140px), transparent 100%);
            mask-image: linear-gradient(to right, transparent 0%, rgba(0, 0, 0, 1) 140px, rgba(0, 0, 0, 1) calc(100% - 140px), transparent 100%);
        }
        @media (max-width: 640px) {
            .marquee-fade-container {
                -webkit-mask-image: linear-gradient(to right, transparent 0%, rgba(0, 0, 0, 1) 40px, rgba(0, 0, 0, 1) calc(100% - 40px), transparent 100%);
                mask-image: linear-gradient(to right, transparent 0%, rgba(0, 0, 0, 1) 40px, rgba(0, 0, 0, 1) calc(100% - 40px), transparent 100%);
            }
        }
    </style>
    <h1 class="sr-only">MNM Bookstore - Cửa Hàng Sách Trực Tuyến & Khám Phá Tri Thức</h1>

    <section class="mb-8">
        <div id="hero-banner-carousel" class="banner-carousel-box shadow-soft border border-brand-border/40 group bg-brand-surface">
            <div id="banner-slides" class="flex h-full transition-transform duration-700 ease-out" style="width: 100%;">
                @foreach($heroBanners as $banner)
                    <a href="{{ $banner['link'] }}" class="banner-slide-item overflow-hidden">
                        <img src="{{ $banner['image'] }}" 
                             alt="{{ $banner['title'] }}" 
                             width="1200" 
                             height="457"
                             @if($loop->first)
                                 fetchpriority="high"
                                 loading="eager"
                                 decoding="sync"
                             @else
                                 loading="lazy"
                                 decoding="async"
                             @endif
                             class="group-hover:scale-102 transition-transform duration-700">
                    </a>
                @endforeach
            </div>
            <!-- Nút Prev / Next -->
            <button id="banner-prev" type="button" class="absolute left-3 md:left-5 top-1/2 -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 rounded-full bg-white/90 hover:bg-white text-brand-text flex items-center justify-center shadow-lg backdrop-blur transition-all opacity-80 md:opacity-0 group-hover:opacity-100 cursor-pointer z-20 hover:scale-105 active:scale-95" aria-label="Banner trước">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button id="banner-next" type="button" class="absolute right-3 md:right-5 top-1/2 -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 rounded-full bg-white/90 hover:bg-white text-brand-text flex items-center justify-center shadow-lg backdrop-blur transition-all opacity-80 md:opacity-0 group-hover:opacity-100 cursor-pointer z-20 hover:scale-105 active:scale-95" aria-label="Banner tiếp theo">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <!-- Dots Indicator -->
            <div id="banner-dots" class="absolute bottom-3 md:bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-1 z-20">
                @foreach($heroBanners as $index => $banner)
                    <button type="button" class="banner-dot p-2 flex items-center justify-center cursor-pointer min-w-8 min-h-8" data-index="{{ $index }}" aria-label="Slide {{ $index + 1 }}">
                        <span class="dot-pill block h-2.5 rounded-full transition-all {{ $index === 0 ? 'bg-white w-7' : 'bg-white/60 w-2.5 hover:bg-white/90' }}"></span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 2. Danh mục nổi bật (Slide trượt ngang tự động êm dịu) -->
    <section class="py-10 border-t border-brand-border border-opacity-50 overflow-hidden">
        <div class="text-center mb-6">
            <h2 class="font-display text-2xl md:text-3xl font-bold text-brand-text mb-2">Danh mục nổi bật</h2>
            <p class="text-brand-muted font-ui text-sm max-w-md mx-auto">Khám phá các thể loại sách đa dạng và phong phú</p>
        </div>

        <!-- Slider Wrapper with mask-image fade edges -->
        <div class="relative w-full overflow-hidden py-3 select-none marquee-fade-container">
            <!-- Auto-sliding Track -->
            <div class="animate-marquee flex items-center">
                <!-- Group 1 -->
                <div class="flex gap-4 items-center shrink-0 pr-4">
                    @foreach($categories as $category)
                        <a href="{{ route('books.index', ['category' => $category->slug]) }}" 
                           class="inline-flex items-center px-6 py-3 bg-brand-surface hover:bg-brand-accent hover:text-white hover:shadow-soft hover:-translate-y-1 transition-all duration-300 rounded-full font-ui font-semibold text-brand-text border border-brand-border shrink-0 whitespace-nowrap shadow-sm">
                            <span>{{ $category->name }}</span>
                        </a>
                    @endforeach
                    @foreach($categories as $category)
                        <a href="{{ route('books.index', ['category' => $category->slug]) }}" 
                           class="inline-flex items-center px-6 py-3 bg-brand-surface hover:bg-brand-accent hover:text-white hover:shadow-soft hover:-translate-y-1 transition-all duration-300 rounded-full font-ui font-semibold text-brand-text border border-brand-border shrink-0 whitespace-nowrap shadow-sm">
                            <span>{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
                <!-- Group 2 (Clone for infinite seamless loop) -->
                <div class="flex gap-4 items-center shrink-0 pr-4" aria-hidden="true">
                    @foreach($categories as $category)
                        <a href="{{ route('books.index', ['category' => $category->slug]) }}" tabindex="-1"
                           class="inline-flex items-center px-6 py-3 bg-brand-surface hover:bg-brand-accent hover:text-white hover:shadow-soft hover:-translate-y-1 transition-all duration-300 rounded-full font-ui font-semibold text-brand-text border border-brand-border shrink-0 whitespace-nowrap shadow-sm">
                            <span>{{ $category->name }}</span>
                        </a>
                    @endforeach
                    @foreach($categories as $category)
                        <a href="{{ route('books.index', ['category' => $category->slug]) }}" tabindex="-1"
                           class="inline-flex items-center px-6 py-3 bg-brand-surface hover:bg-brand-accent hover:text-white hover:shadow-soft hover:-translate-y-1 transition-all duration-300 rounded-full font-ui font-semibold text-brand-text border border-brand-border shrink-0 whitespace-nowrap shadow-sm">
                            <span>{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Bảng Xếp Hạng Best Sellers (Fahasa Master-Detail Style) -->
    <section class="my-12 bg-white rounded-2xl md:rounded-[var(--radius-card)] p-5 md:p-8 shadow-sm border border-brand-border/60">
        <!-- Header: Icon Ngọn lửa & Best Sellers & Category Tabs -->
        <div class="border-b border-brand-border/50 pb-3 mb-6">
            <div class="flex items-center gap-2.5 mb-4">
                <span class="text-2xl md:text-3xl">🔥</span>
                <h2 class="font-display text-2xl md:text-3xl font-bold text-brand-text tracking-tight">Best Sellers</h2>
            </div>
            
            <!-- Category Tabs Navigation -->
            <div class="flex items-center gap-6 overflow-x-auto no-scrollbar pt-1" id="bestseller-tabs">
                @foreach($bestSellersByCategory as $idx => $catGroup)
                    <button type="button" 
                            class="bestseller-tab whitespace-nowrap text-sm md:text-base transition-all font-semibold pb-2.5 cursor-pointer border-b-2 {{ $idx === 0 ? 'text-red-600 border-red-600 font-bold' : 'text-brand-muted hover:text-red-500 border-transparent' }}"
                            data-cat-id="{{ $catGroup['id'] }}">
                        {{ $catGroup['name'] }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- 2 Columns: Master List (Left) + Detail View (Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Cột Trái (Left Column: Top 1 - Top 5) -->
            <div class="lg:col-span-5 border-b lg:border-b-0 lg:border-r border-brand-border/40 lg:pr-6 pb-6 lg:pb-0">
                @foreach($bestSellersByCategory as $catIdx => $catGroup)
                    <div class="bestseller-book-list flex flex-col gap-2 {{ $catIdx === 0 ? '' : 'hidden' }}" 
                         id="bestseller-list-{{ $catGroup['id'] }}">
                        @foreach($catGroup['books'] as $bookIdx => $book)
                            <div class="bestseller-rank-item group flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all duration-200 border-l-4 {{ $bookIdx === 0 ? 'bg-red-50/70 border-red-500 shadow-sm' : 'border-transparent hover:bg-brand-surface/60' }}"
                                 data-id="{{ $book['id'] }}"
                                 data-rank="{{ $book['rank'] }}"
                                 data-title="{{ e($book['title']) }}"
                                 data-slug="{{ $book['slug'] }}"
                                 data-author="{{ e($book['author']) }}"
                                 data-publisher="{{ e($book['publisher']) }}"
                                 data-price="{{ $book['price_formatted'] }}"
                                 data-original-price="{{ $book['original_price_formatted'] ?? '' }}"
                                 data-discount="{{ $book['discount_percent'] }}"
                                 data-cover="{{ $book['cover'] }}"
                                 data-rating="{{ $book['rating'] }}"
                                 data-reviews="{{ $book['reviews_count'] }}"
                                 data-sold="{{ $book['sold_in_week'] }}"
                                 data-description="{{ e($book['description']) }}">
                                
                                <!-- Số thứ tự Rank -->
                                <div class="w-8 shrink-0 text-center font-black text-2xl md:text-3xl {{ $bookIdx < 3 ? 'text-red-600' : 'text-slate-400' }}">
                                    {{ $book['rank'] }}
                                </div>

                                <!-- Bìa sách nhỏ -->
                                <div class="w-14 h-20 shrink-0 bg-brand-surface rounded-lg overflow-hidden shadow-sm border border-brand-border/30">
                                    <img src="{{ $book['cover'] }}" alt="{{ $book['title'] }}" width="56" height="80" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform" onerror="this.outerHTML='<div class=\'w-full h-full flex items-center justify-center text-xs opacity-50 bg-slate-100\'>Sách</div>'">
                                </div>

                                <!-- Thông tin tóm tắt -->
                                <div class="flex-grow min-w-0">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded {{ $bookIdx < 3 ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700' }}">
                                        🔥 Top {{ $book['rank'] }}
                                    </span>
                                    <h3 class="font-bold text-sm text-brand-text truncate mt-1 group-hover:text-red-600 transition-colors" title="{{ $book['title'] }}">
                                        {{ $book['title'] }}
                                    </h3>
                                    <div class="text-xs text-brand-muted mt-1 flex items-center gap-2">
                                        @if($book['rating'] > 0)
                                             <span class="text-amber-700 font-bold">★ {{ $book['rating'] }}</span>
                                             <span>•</span>
                                        @endif
                                        <span>Đã bán trong tuần {{ $book['sold_in_week'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <!-- Cột Phải (Right Column: Chi tiết cuốn sách Active) -->
            <div class="lg:col-span-7 lg:pl-2" id="bestseller-detail-panel">
                @php
                    $firstBook = $bestSellersByCategory[0]['books'][0] ?? null;
                @endphp
                @if($firstBook)
                    <div class="transition-opacity duration-200" id="bestseller-detail-content">
                        <!-- Nửa trên: Bìa lớn + Thông tin giá & Nút mua -->
                        <div class="flex flex-col sm:flex-row gap-6 items-start">
                            <!-- Bìa lớn -->
                            <div class="w-full sm:w-52 shrink-0 bg-brand-surface/40 p-3 rounded-2xl border border-brand-border/40 shadow-sm flex items-center justify-center">
                                <a id="detail-book-link-cover" href="{{ route('books.show', $firstBook['slug']) }}" class="block aspect-[3/4] w-full max-w-[200px] overflow-hidden rounded-xl shadow-md group">
                                    <img id="detail-book-cover" src="{{ $firstBook['cover'] }}" alt="{{ $firstBook['title'] }}" width="200" height="267" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.outerHTML='<div class=\'w-full h-full flex items-center justify-center text-sm opacity-50 bg-slate-100\'>Hình Bìa</div>'">
                                </a>
                            </div>

                            <!-- Thông tin mua hàng -->
                            <div class="flex-grow flex flex-col justify-between w-full">
                                <div>
                                    <a id="detail-book-link-title" href="{{ route('books.show', $firstBook['slug']) }}">
                                        <h3 id="detail-book-title" class="font-display text-xl md:text-2xl font-bold text-brand-text hover:text-red-600 transition-colors leading-tight mb-2">
                                            {{ $firstBook['title'] }}
                                        </h3>
                                    </a>
                                    
                                    <div id="detail-book-sold" class="text-xs md:text-sm text-brand-muted mb-3">
                                        Đã bán trong tuần {{ $firstBook['sold_in_week'] }}
                                    </div>

                                    <div class="text-xs md:text-sm text-brand-muted flex flex-wrap items-center gap-x-4 gap-y-1 mb-4">
                                        <div>
                                            <span class="opacity-70">👤 Tác giả:</span> 
                                            <strong id="detail-book-author" class="text-brand-text font-semibold">{{ $firstBook['author'] }}</strong>
                                        </div>
                                        <span class="opacity-40">|</span>
                                        <div>
                                            <span class="opacity-70">📖 NXB:</span> 
                                            <strong id="detail-book-publisher" class="text-brand-text font-semibold">{{ $firstBook['publisher'] }}</strong>
                                        </div>
                                    </div>

                                    <!-- Khung Giá -->
                                    <div class="flex items-baseline gap-3 mb-5">
                                        <span id="detail-book-price" class="text-2xl md:text-3xl font-extrabold text-red-600">
                                            {{ $firstBook['price_formatted'] }}
                                        </span>
                                        <span id="detail-book-original-price" class="text-sm text-brand-muted line-through {{ $firstBook['original_price_formatted'] ? '' : 'hidden' }}">
                                            {{ $firstBook['original_price_formatted'] }}
                                        </span>
                                        <span id="detail-book-discount" class="bg-red-600 text-white font-bold text-xs px-2 py-0.5 rounded shadow-sm {{ $firstBook['discount_percent'] > 0 ? '' : 'hidden' }}">
                                            -{{ $firstBook['discount_percent'] }}%
                                        </span>
                                    </div>
                                </div>

                                <!-- Form Thêm vào giỏ hàng -->
                                <form method="POST" action="{{ route('cart.add') }}" class="w-full">
                                    @csrf
                                    <input type="hidden" name="book_id" id="detail-book-id-input" value="{{ $firstBook['id'] }}">
                                    <div class="flex items-center gap-3">
                                        <button type="submit" class="flex-1 py-3.5 px-6 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer text-sm md:text-base">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            Thêm vào giỏ hàng
                                        </button>
                                        <a id="detail-book-link-btn" href="{{ route('books.show', $firstBook['slug']) }}" class="py-3.5 px-5 bg-brand-surface hover:bg-brand-border text-brand-text font-bold rounded-xl transition-colors text-sm md:text-base whitespace-nowrap">
                                            Xem chi tiết
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Nửa dưới: Mô tả sản phẩm & Đánh giá -->
                        <div class="mt-6 pt-5 border-t border-brand-border/40">
                            <p class="font-bold text-sm text-brand-text mb-1">Mô tả sản phẩm</p>
                            <div id="detail-book-heading" class="font-bold text-xs text-brand-text/90 uppercase tracking-wide mb-1">
                                {{ $firstBook['title'] }}
                            </div>
                            <p id="detail-book-description" class="text-sm text-brand-muted leading-relaxed line-clamp-3">
                                {{ $firstBook['description'] ?: 'Tác phẩm đặc sắc thuộc bảng xếp hạng sách bán chạy của MNM Bookstore.' }}
                            </p>

                            <!-- Khung Đánh giá sản phẩm -->
                            <div class="bg-brand-surface/50 rounded-xl p-3.5 mt-4 border border-brand-border/40 flex flex-wrap items-center justify-between gap-3 text-sm">
                                <span class="font-bold text-brand-text">Đánh giá sản phẩm</span>
                                <div class="flex items-center gap-2">
                                    <span id="detail-book-rating" class="font-bold text-base text-brand-text">{{ $firstBook['rating'] }}/5</span>
                                    <span id="detail-book-stars" class="text-amber-500 tracking-widest text-base">
                                        @for($s = 1; $s <= 5; $s++)
                                            {{ $s <= round($firstBook['rating']) ? '★' : '☆' }}
                                        @endfor
                                    </span>
                                </div>
                                <div id="detail-book-reviews" class="text-brand-muted text-xs md:text-sm pl-4 border-l border-brand-border">
                                    {{ $firstBook['reviews_count'] }} đánh giá
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- 4. Sách Mới -->
    <section class="py-12" id="sach-moi">
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-4">
            <div>
                <h2 class="font-display text-3xl font-bold mb-2">Sách mới</h2>
                <p class="text-brand-muted font-ui">Những câu chuyện vừa được đưa lên kệ.</p>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Filter/Sort -->
                <div class="flex items-center gap-2">
                    <label for="sort" class="font-ui text-brand-text font-medium text-sm">Sắp xếp:</label>
                    <select aria-label="Sắp xếp" name="sort" id="sort-new-books" class="bg-white border border-brand-border text-brand-text text-sm rounded-lg focus:ring-brand-accent focus:border-brand-accent block p-2 outline-none font-ui shadow-sm cursor-pointer">
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

    <!-- 5. Banner Khuyến Mãi -->
    <section class="py-14 my-8">
        <div class="bg-brand-text rounded-[28px] py-16 md:py-24 lg:py-28 px-8 md:px-14 lg:px-20 text-center text-white relative overflow-hidden shadow-xl">
            <!-- Decorative circles & ambient lighting -->
            <div class="absolute -top-32 -right-32 w-96 h-96 bg-white opacity-5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-brand-accent opacity-25 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-brand-accent/10 rounded-full blur-[100px] pointer-events-none"></div>
            
            <div class="relative z-10 max-w-4xl mx-auto">
                <span class="inline-block font-ui font-extrabold tracking-widest text-brand-accent mb-5 uppercase text-xs md:text-sm bg-brand-accent/15 px-4 py-1.5 rounded-full border border-brand-accent/30 backdrop-blur-sm">Ưu đãi dành cho người yêu sách</span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight tracking-tight">Giảm đến 20% cho một số tựa sách được chọn</h2>
                <p class="font-ui text-brand-bg/90 mb-10 text-base md:text-xl lg:text-2xl max-w-2xl mx-auto leading-relaxed">Khám phá các tựa sách kinh điển và best-seller với mức giá ưu đãi chưa từng có. Chương trình có hạn.</p>
                <a href="{{ route('books.index') }}" class="inline-flex items-center gap-2 font-ui font-bold text-brand-text bg-white hover:bg-brand-accent hover:text-white transition-all duration-300 px-10 py-4.5 md:px-12 md:py-5 rounded-full shadow-xl hover:shadow-2xl hover:scale-105 active:scale-95 text-base md:text-lg">
                    <span>Xem ưu đãi ngay</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // === 1. HERO BANNER CAROUSEL LOGIC ===
            const bannerTrack = document.getElementById('banner-slides');
            const bannerDots = document.querySelectorAll('.banner-dot');
            const prevBtn = document.getElementById('banner-prev');
            const nextBtn = document.getElementById('banner-next');
            const bannerContainer = document.getElementById('hero-banner-carousel');

            if (bannerTrack && bannerDots.length > 0) {
                let currentSlide = 0;
                const totalSlides = bannerDots.length;
                let bannerInterval = null;

                function goToSlide(index) {
                    currentSlide = (index + totalSlides) % totalSlides;
                    const slideWidth = bannerContainer ? bannerContainer.clientWidth : 0;
                    bannerTrack.style.transform = `translateX(-${currentSlide * slideWidth}px)`;

                    bannerDots.forEach((dot, idx) => {
                        const pill = dot.querySelector('.dot-pill') || dot;
                        if (idx === currentSlide) {
                            pill.classList.remove('bg-white/60', 'w-2.5');
                            pill.classList.add('bg-white', 'w-7');
                        } else {
                            pill.classList.remove('bg-white', 'w-7');
                            pill.classList.add('bg-white/60', 'w-2.5');
                        }
                    });
                }

                function nextSlide() {
                    goToSlide(currentSlide + 1);
                }

                function prevSlide() {
                    goToSlide(currentSlide - 1);
                }

                function startBannerAutoplay() {
                    stopBannerAutoplay();
                    bannerInterval = setInterval(nextSlide, 4500);
                }

                function stopBannerAutoplay() {
                    if (bannerInterval) clearInterval(bannerInterval);
                }

                if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); startBannerAutoplay(); });
                if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); startBannerAutoplay(); });

                bannerDots.forEach(dot => {
                    dot.addEventListener('click', function() {
                        const idx = parseInt(this.getAttribute('data-index'), 10);
                        goToSlide(idx);
                        startBannerAutoplay();
                    });
                });

                if (bannerContainer) {
                    bannerContainer.addEventListener('mouseenter', stopBannerAutoplay);
                    bannerContainer.addEventListener('mouseleave', startBannerAutoplay);
                }

                startBannerAutoplay();
            }

            // === 2. BEST SELLERS INTERACTIVE RANKING LOGIC ===
            const categoryTabs = document.querySelectorAll('.bestseller-tab');
            const bookLists = document.querySelectorAll('.bestseller-book-list');

            // Elements in detail view
            const detailLinkCover = document.getElementById('detail-book-link-cover');
            const detailCover = document.getElementById('detail-book-cover');
            const detailLinkTitle = document.getElementById('detail-book-link-title');
            const detailTitle = document.getElementById('detail-book-title');
            const detailSold = document.getElementById('detail-book-sold');
            const detailAuthor = document.getElementById('detail-book-author');
            const detailPublisher = document.getElementById('detail-book-publisher');
            const detailPrice = document.getElementById('detail-book-price');
            const detailOriginalPrice = document.getElementById('detail-book-original-price');
            const detailDiscount = document.getElementById('detail-book-discount');
            const detailIdInput = document.getElementById('detail-book-id-input');
            const detailLinkBtn = document.getElementById('detail-book-link-btn');
            const detailHeading = document.getElementById('detail-book-heading');
            const detailDesc = document.getElementById('detail-book-description');
            const detailRating = document.getElementById('detail-book-rating');
            const detailStars = document.getElementById('detail-book-stars');
            const detailReviews = document.getElementById('detail-book-reviews');
            const detailContent = document.getElementById('bestseller-detail-content');

            function updateDetailView(item) {
                if (!item || !detailTitle) return;

                const id = item.dataset.id;
                const title = item.dataset.title;
                const slug = item.dataset.slug;
                const author = item.dataset.author;
                const publisher = item.dataset.publisher || 'NXB Trẻ';
                const price = item.dataset.price;
                const originalPrice = item.dataset.originalPrice;
                const discount = parseInt(item.dataset.discount, 10) || 0;
                const cover = item.dataset.cover;
                const rating = parseFloat(item.dataset.rating) || 5.0;
                const reviews = item.dataset.reviews || '0';
                const sold = item.dataset.sold || '50';
                const desc = item.dataset.description;

                // Subtle fade transition
                if (detailContent) {
                    detailContent.style.opacity = '0.5';
                }

                setTimeout(() => {
                    const bookUrl = `/books/${slug}`;
                    if (detailLinkCover) detailLinkCover.href = bookUrl;
                    if (detailCover) detailCover.src = cover;
                    if (detailLinkTitle) detailLinkTitle.href = bookUrl;
                    if (detailTitle) detailTitle.textContent = title;
                    if (detailSold) detailSold.textContent = `Đã bán trong tuần ${sold}`;
                    if (detailAuthor) detailAuthor.textContent = author;
                    if (detailPublisher) detailPublisher.textContent = publisher;
                    if (detailPrice) detailPrice.textContent = price;

                    if (detailOriginalPrice) {
                        if (originalPrice) {
                            detailOriginalPrice.textContent = originalPrice;
                            detailOriginalPrice.classList.remove('hidden');
                        } else {
                            detailOriginalPrice.classList.add('hidden');
                        }
                    }

                    if (detailDiscount) {
                        if (discount > 0) {
                            detailDiscount.textContent = `-${discount}%`;
                            detailDiscount.classList.remove('hidden');
                        } else {
                            detailDiscount.classList.add('hidden');
                        }
                    }

                    if (detailIdInput) detailIdInput.value = id;
                    if (detailLinkBtn) detailLinkBtn.href = bookUrl;
                    if (detailHeading) detailHeading.textContent = title;
                    if (detailDesc) detailDesc.textContent = desc || 'Tác phẩm đặc sắc thuộc bảng xếp hạng sách bán chạy của MNM Bookstore.';
                    if (detailRating) detailRating.textContent = `${rating}/5`;
                    if (detailReviews) detailReviews.textContent = `${reviews} đánh giá`;

                    if (detailStars) {
                        const fullStars = Math.round(rating);
                        let starStr = '';
                        for (let s = 1; s <= 5; s++) {
                            starStr += s <= fullStars ? '★' : '☆';
                        }
                        detailStars.textContent = starStr;
                    }

                    if (detailContent) {
                        detailContent.style.opacity = '1';
                    }
                }, 100);
            }

            // Bind click & hover for each rank item
            function setupRankItems() {
                document.querySelectorAll('.bestseller-rank-item').forEach(item => {
                    const activate = () => {
                        const parent = item.closest('.bestseller-book-list');
                        if (parent) {
                            parent.querySelectorAll('.bestseller-rank-item').forEach(sibling => {
                                sibling.classList.remove('bg-red-50/70', 'border-red-500', 'shadow-sm');
                                sibling.classList.add('border-transparent');
                            });
                        }
                        item.classList.remove('border-transparent');
                        item.classList.add('bg-red-50/70', 'border-red-500', 'shadow-sm');
                        updateDetailView(item);
                    };

                    item.addEventListener('click', activate);
                    item.addEventListener('mouseenter', activate);
                });
            }
            setupRankItems();

            // Category Tab switching
            categoryTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const catId = this.dataset.catId;

                    // Update Tab active styling
                    categoryTabs.forEach(t => {
                        t.classList.remove('text-red-600', 'border-red-600', 'font-bold');
                        t.classList.add('text-brand-muted', 'border-transparent');
                    });
                    this.classList.remove('text-brand-muted', 'border-transparent');
                    this.classList.add('text-red-600', 'border-red-600', 'font-bold');

                    // Show selected list, hide others
                    bookLists.forEach(list => {
                        list.classList.add('hidden');
                    });
                    const targetList = document.getElementById(`bestseller-list-${catId}`);
                    if (targetList) {
                        targetList.classList.remove('hidden');
                        // Auto-activate first item in new category
                        const firstItem = targetList.querySelector('.bestseller-rank-item');
                        if (firstItem) {
                            targetList.querySelectorAll('.bestseller-rank-item').forEach(sibling => {
                                sibling.classList.remove('bg-red-50/70', 'border-red-500', 'shadow-sm');
                                sibling.classList.add('border-transparent');
                            });
                            firstItem.classList.remove('border-transparent');
                            firstItem.classList.add('bg-red-50/70', 'border-red-500', 'shadow-sm');
                            updateDetailView(firstItem);
                        }
                    }
                });
            });

            // === 3. SÁCH MỚI AJAX LOGIC ===
            const container = document.getElementById('new-books-container');
            const sortSelect = document.getElementById('sort-new-books');

            function fetchNewBooks(url) {
                if (!container) return;
                container.style.opacity = '0.5';
                container.style.pointerEvents = 'none';

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
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

            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    const sortValue = this.value;
                    const url = new URL(window.location.href);
                    url.pathname = '/';
                    url.searchParams.set('sort', sortValue);
                    url.searchParams.delete('page');
                    fetchNewBooks(url.toString());
                });
            }

            if (container) {
                container.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    if (link && link.href && link.href.includes('page=')) {
                        e.preventDefault();
                        fetchNewBooks(link.href);
                        document.getElementById('sach-moi')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });

                container.addEventListener('submit', function(e) {
                    const form = e.target.closest('form');
                    if (form && form.page && form.page.value) {
                        e.preventDefault();
                        e.stopPropagation();
                        const url = new URL(window.location.href);
                        url.searchParams.set('page', form.page.value);
                        if (sortSelect) url.searchParams.set('sort', sortSelect.value);
                        fetchNewBooks(url.toString());
                        document.getElementById('sach-moi')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, true);
            }
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
                <label for="newsletter-email" class="sr-only">Địa chỉ email nhận thông tin</label>
                <input id="newsletter-email" type="email" aria-label="Địa chỉ email nhận thông tin ưu đãi" placeholder="Nhập địa chỉ email của bạn..." required class="flex-1 bg-white border border-brand-border rounded-full py-3 px-6 focus:outline-none focus:border-brand-accent font-ui transition-colors shadow-sm">
                <button type="submit" class="font-ui font-bold text-white bg-brand-text hover:bg-brand-accent transition-colors rounded-full px-8 py-3 shadow-sm whitespace-nowrap">Đăng ký</button>
            </form>
        </div>
    </section>
@endsection
