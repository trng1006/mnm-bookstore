<header id="main-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 bg-brand-bg border-b border-brand-border shadow-sm">
    <div id="header-container" class="mx-auto flex justify-between items-center gap-4 transition-all duration-500 max-w-[1440px] px-4 md:px-8 py-4">
        
        <!-- Left: Logo & Category Dropdown -->
        <div class="flex items-center gap-4 md:gap-8">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="font-display text-2xl md:text-3xl font-bold text-brand-text hover:text-brand-accent transition-colors duration-300">
                MNM Bookstore
            </a>

            <!-- Category Dropdown Button (Mega Menu) -->
            <div class="relative group hidden md:block h-full py-2">
                <button class="flex items-center gap-1 text-brand-text hover:text-brand-accent transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <!-- Mega Menu Dropdown -->
                <div class="absolute top-full left-0 w-[700px] bg-white border border-brand-border shadow-hover rounded-[var(--radius-card)] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 flex z-50 overflow-hidden mt-1">
                    <!-- Left Sidebar (Main Categories) -->
                    <div class="w-1/3 bg-brand-surface p-4 flex flex-col gap-2">
                        <div class="font-display font-bold text-brand-text mb-2 px-3 pb-2 border-b border-brand-border">Danh mục sản phẩm</div>
                        <a href="#" class="px-3 py-2 rounded-xl bg-white font-ui text-brand-text font-bold shadow-sm">Sách Trong Nước</a>
                        <a href="#" class="px-3 py-2 rounded-xl hover:bg-white font-ui text-brand-muted hover:text-brand-text transition-colors">Sách Ngoại Văn</a>
                        <a href="#" class="px-3 py-2 rounded-xl hover:bg-white font-ui text-brand-muted hover:text-brand-text transition-colors">Văn phòng phẩm</a>
                        <a href="#" class="px-3 py-2 rounded-xl hover:bg-white font-ui text-brand-muted hover:text-brand-text transition-colors">Đồ chơi</a>
                    </div>
                    
                    <!-- Right Content (Sub Categories) -->
                    <div class="w-2/3 p-6 grid grid-cols-2 gap-x-8 gap-y-6">
                        <div>
                            <div class="font-ui font-bold text-brand-text mb-3 text-sm uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 bg-brand-accent rounded-full"></span> Văn Học
                            </div>
                            <ul class="space-y-2.5 text-sm font-ui text-brand-muted">
                                <li><a href="#" class="hover:text-brand-accent transition-colors block">Tiểu thuyết</a></li>
                                <li><a href="#" class="hover:text-brand-accent transition-colors block">Truyện ngắn - Tản văn</a></li>
                                <li><a href="#" class="hover:text-brand-accent transition-colors block">Light Novel</a></li>
                                <li><a href="#" class="hover:text-brand-accent transition-colors block">Ngôn tình</a></li>
                                <li><a href="#" class="text-brand-accent font-semibold text-xs mt-1 block">Xem tất cả</a></li>
                            </ul>
                        </div>
                        <div>
                            <div class="font-ui font-bold text-brand-text mb-3 text-sm uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 bg-[#9DB67A] rounded-full"></span> Kinh Tế
                            </div>
                            <ul class="space-y-2.5 text-sm font-ui text-brand-muted">
                                <li><a href="#" class="hover:text-brand-accent transition-colors block">Bài học kinh doanh</a></li>
                                <li><a href="#" class="hover:text-brand-accent transition-colors block">Quản trị - Lãnh đạo</a></li>
                                <li><a href="#" class="hover:text-brand-accent transition-colors block">Marketing - Bán hàng</a></li>
                                <li><a href="#" class="hover:text-brand-accent transition-colors block">Phân tích kinh tế</a></li>
                                <li><a href="#" class="text-brand-accent font-semibold text-xs mt-1 block">Xem tất cả</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Center: Search Bar (AJAX) -->
        <div class="hidden lg:flex flex-1 max-w-2xl mx-8 relative group" id="ajax-search-container">
            <input type="text" id="ajax-search-input" placeholder="Tìm kiếm sách, tác giả..." autocomplete="off" class="w-full bg-white border border-brand-border rounded-full py-2 px-5 pr-12 focus:outline-none focus:border-brand-accent focus:ring-1 focus:ring-brand-accent font-ui text-brand-text transition-colors shadow-sm relative z-10">
            <button aria-label="Tìm kiếm" class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-brand-accent text-white hover:bg-brand-accent-hover transition-colors rounded-full p-1.5 z-10 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>

            <!-- AJAX Results Dropdown -->
            <div id="ajax-search-results" class="absolute top-[100%] left-0 right-0 bg-white border border-brand-border rounded-2xl shadow-lg mt-2 overflow-hidden z-50 hidden max-h-96 overflow-y-auto pt-1 font-ui">
                <!-- Sẽ được fill bằng JS -->
            </div>
        </div>

        <!-- Right: Utility Nav (Icons + Text) -->
        <nav class="flex items-center gap-4 md:gap-7">
            <!-- Tra cứu đơn hàng -->
            <a href="{{ route('orders.tracking') }}" class="hidden sm:flex flex-col items-center justify-center text-brand-text hover:text-brand-accent transition-colors" title="Tra cứu đơn hàng" aria-label="Tra cứu đơn hàng">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span class="text-[11px] font-ui font-semibold">Tra cứu</span>
            </a>

            <!-- Thông báo -->
            @auth
                @php
                    $unreadNotifications = auth()->user()->unreadNotifications;
                    $unreadCount = $unreadNotifications->count();
                @endphp
                <div class="relative group">
                    <a aria-label="Thông báo" href="{{ route('account.notifications.index') }}" class="flex flex-col items-center justify-center text-brand-text hover:text-brand-accent transition-colors relative">
                        <div class="relative mb-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            @if($unreadCount > 0)
                                <span class="absolute -top-1.5 -right-2 bg-brand-accent text-white min-w-4 h-4 px-1 rounded-full text-[10px] flex items-center justify-center font-bold animate-pulse">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[11px] font-ui font-semibold hidden md:block">Thông báo</span>
                    </a>

                    <!-- Notifications Dropdown -->
                    <div class="absolute right-0 mt-2 w-80 bg-white border border-brand-border rounded-xl shadow-hover opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 overflow-hidden font-ui">
                        <div class="p-3 border-b border-brand-border flex items-center justify-between bg-brand-surface">
                            <span class="font-bold text-xs text-brand-text uppercase tracking-wider">Thông báo mới</span>
                            @if($unreadCount > 0)
                                <form method="POST" action="{{ route('account.notifications.readAll') }}">
                                    @csrf
                                    <button type="submit" class="text-[11px] text-brand-accent hover:underline font-semibold cursor-pointer">Đã đọc tất cả</button>
                                </form>
                            @endif
                        </div>

                        <div class="max-h-64 overflow-y-auto divide-y divide-brand-border/60">
                            @forelse($unreadNotifications->take(4) as $notif)
                                <div class="p-3 hover:bg-brand-surface/40 transition-colors text-xs">
                                    <div class="font-bold text-brand-text line-clamp-1">{{ $notif->data['title'] ?? 'Thông báo đơn hàng' }}</div>
                                    <p class="text-brand-muted text-[11px] mt-0.5 line-clamp-2">{{ $notif->data['message'] ?? '' }}</p>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-[10px] text-brand-muted">{{ $notif->created_at->diffForHumans() }}</span>
                                        @if(!empty($notif->data['url']))
                                            <form method="POST" action="{{ route('account.notifications.read', $notif->id) }}">
                                                @csrf
                                                <button type="submit" class="text-[11px] font-semibold text-brand-accent hover:underline cursor-pointer">Xem</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-xs text-brand-muted">
                                    Không có thông báo mới nào.
                                </div>
                            @endforelse
                        </div>

                        <div class="p-2.5 border-t border-brand-border bg-brand-surface text-center">
                            <a href="{{ route('account.notifications.index') }}" class="text-xs font-bold text-brand-accent hover:underline block">
                                Xem tất cả thông báo →
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <a aria-label="Đăng nhập" href="{{ route('login') }}" class="flex flex-col items-center justify-center text-brand-text hover:text-brand-accent transition-colors">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span class="text-[11px] font-ui font-semibold hidden md:block">Thông báo</span>
                </a>
            @endauth
            
            <!-- Giỏ hàng -->
            @php
                $cartCount = array_sum(array_column(session('cart', []), 'quantity'));
            @endphp
            <a aria-label="Giỏ hàng" href="{{ route('cart.index') }}" class="flex flex-col items-center justify-center text-brand-text hover:text-brand-accent transition-colors relative group">
                <div class="relative mb-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span id="cart-badge" class="absolute -top-1.5 -right-2 bg-brand-accent text-white min-w-4 h-4 px-1 rounded-full text-[10px] flex items-center justify-center font-bold {{ $cartCount > 0 ? '' : 'hidden' }}">
                        {{ $cartCount }}
                    </span>
                </div>
                <span class="text-[11px] font-ui font-semibold hidden md:block">Giỏ hàng</span>
            </a>

            <!-- Tài khoản -->
            @auth
                <div class="relative group">
                    <a aria-label="Tài khoản cá nhân" href="{{ route('account.profile') }}" class="flex flex-col items-center justify-center text-brand-text hover:text-brand-accent transition-colors">
                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span class="text-[11px] font-ui font-semibold hidden md:block truncate max-w-[80px]">{{ auth()->user()->name }}</span>
                    </a>
                    <!-- User Dropdown -->
                    <div class="absolute right-0 mt-2 w-48 bg-white border border-brand-border rounded-xl shadow-hover opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 overflow-hidden font-ui">
                        @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 text-sm text-brand-text hover:bg-brand-surface hover:text-brand-accent font-semibold border-b border-brand-border">
                                Trang quản trị
                            </a>
                        @endif
                        <a href="{{ route('account.profile') }}" class="block px-4 py-2 text-sm text-brand-text hover:bg-brand-surface hover:text-brand-accent">Hồ sơ cá nhân</a>
                        <a href="{{ route('account.profile') }}" class="block px-4 py-2 text-sm text-brand-text hover:bg-brand-surface hover:text-brand-accent">Lịch sử đơn hàng</a>
                        <a href="{{ route('account.notifications.index') }}" class="block px-4 py-2 text-sm text-brand-text hover:bg-brand-surface hover:text-brand-accent flex items-center justify-between">
                            <span>Thông báo</span>
                            @if(isset($unreadCount) && $unreadCount > 0)
                                <span class="bg-brand-accent text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <div class="border-t border-brand-border mt-1">
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors cursor-pointer">Đăng xuất</button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a aria-label="Đăng nhập" href="{{ route('login') }}" class="flex flex-col items-center justify-center text-brand-text hover:text-brand-accent transition-colors">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="text-[11px] font-ui font-semibold hidden md:block">Tài khoản</span>
                </a>
            @endauth
        </nav>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('main-header');
        const container = document.getElementById('header-container');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 20) {
                container.classList.remove('max-w-[1440px]', 'md:px-8', 'py-4');
                container.classList.add('max-w-7xl', 'md:px-6', 'lg:px-8', 'py-2');
                header.classList.add('shadow-md', 'bg-brand-bg/95', 'backdrop-blur-md');
                header.classList.remove('bg-brand-bg', 'shadow-sm');
            } else {
                container.classList.remove('max-w-7xl', 'md:px-6', 'lg:px-8', 'py-2');
                container.classList.add('max-w-[1440px]', 'md:px-8', 'py-4');
                header.classList.remove('shadow-md', 'bg-brand-bg/95', 'backdrop-blur-md');
                header.classList.add('bg-brand-bg', 'shadow-sm');
            }
        });

        // AJAX Search implementation
        const searchInput = document.getElementById('ajax-search-input');
        const searchResults = document.getElementById('ajax-search-results');
        let debounceTimer;

        if (searchInput && searchResults) {
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    searchResults.classList.add('hidden');
                    return;
                }
                
                debounceTimer = setTimeout(() => {
                    fetch(`{{ route('books.ajaxSearch') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            searchResults.innerHTML = '';
                            
                            if (data.books.length > 0) {
                                let html = '<div class="flex flex-col">';
                                data.books.forEach(book => {
                                    html += `
                                        <a href="${book.url}" class="flex items-center gap-3 p-3 hover:bg-brand-surface border-b border-brand-border/60 transition-colors last:border-0">
                                            <div class="w-10 h-14 bg-brand-surface rounded overflow-hidden flex-shrink-0 border border-brand-border">
                                                ${book.cover_image ? `<img src="${book.cover_image}" class="w-full h-full object-cover">` : ''}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-bold text-sm text-brand-text truncate">${book.title}</div>
                                                <div class="text-[11px] text-brand-muted mt-0.5">${book.author}</div>
                                            </div>
                                            <div class="font-bold text-brand-accent text-sm whitespace-nowrap pl-2">
                                                ${book.price}
                                            </div>
                                        </a>
                                    `;
                                });
                                html += `<div class="p-2 text-center border-t border-brand-border"><a href="{{ route('books.index') }}?search=${encodeURIComponent(query)}" class="text-xs font-bold text-brand-accent hover:underline">Xem tất cả kết quả &rarr;</a></div></div>`;
                                searchResults.innerHTML = html;
                                searchResults.classList.remove('hidden');
                            } else {
                                searchResults.innerHTML = '<div class="p-4 text-center text-sm text-brand-muted">Không tìm thấy sách nào phù hợp.</div>';
                                searchResults.classList.remove('hidden');
                            }
                        })
                        .catch(err => console.error(err));
                }, 300); // 300ms debounce
            });

            // Close results when clicking outside
            document.addEventListener('click', function(e) {
                if (!document.getElementById('ajax-search-container').contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
            
            // Re-open if input is clicked and has value
            searchInput.addEventListener('click', function() {
                if (this.value.trim().length >= 2 && searchResults.innerHTML !== '') {
                    searchResults.classList.remove('hidden');
                }
            });
        }
    });
</script>
