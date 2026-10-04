<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'MNM Bookstore - Hệ thống cửa hàng sách trực tuyến hàng đầu.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MNM Bookstore')</title>

    <!-- Google Fonts: Lora (Display) & Nunito (UI) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Styles and Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-bg text-brand-text font-ui antialiased min-h-screen flex flex-col selection:bg-brand-accent selection:text-white">

    <!-- Header Partial -->
    @include('client.partials.header')

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 md:px-6 lg:px-8 max-w-7xl pt-28">
        @yield('content')
    </main>

    <!-- Footer Partial -->
    @include('client.partials.footer')

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"></div>

    <script>
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.action && form.action.includes('/cart/add')) {
                // Check if user clicked "Mua ngay"
                if (e.submitter && e.submitter.name === 'buy_now') {
                    return; // Let standard submission happen for redirect to checkout
                }

                e.preventDefault();
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Update cart counter
                        const badge = document.getElementById('cart-badge');
                        if (badge) {
                            badge.textContent = data.totalItems;
                            if (data.totalItems > 0) {
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message || 'Có lỗi xảy ra!', 'error');
                    }
                })
                .catch(err => {
                    showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
                });
            } 
            // Intercept Wishlist forms
            else if (form.action && form.action.includes('/wishlist/toggle')) {
                e.preventDefault();
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => {
                    if(res.status === 401) {
                        window.location.href = '/login';
                        return null;
                    }
                    return res.json();
                })
                .then(data => {
                    if (data && data.success) {
                        showToast(data.message, 'success');
                        // Toggle heart icon color locally if needed
                        const icon = form.querySelector('svg');
                        if (icon) {
                            if (data.status === 'added') {
                                icon.classList.add('text-red-500', 'fill-current');
                            } else {
                                icon.classList.remove('text-red-500', 'fill-current');
                            }
                        }
                    } else if (data) {
                        showToast(data.message || 'Có lỗi xảy ra!', 'error');
                    }
                })
                .catch(err => {
                    showToast('Vui lòng đăng nhập để thêm vào yêu thích.', 'error');
                });
            }
        });

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            // Warm Humanist style
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
            toast.className = `flex items-center px-4 py-3 rounded-xl shadow-soft text-white ${bgColor} font-ui text-sm transform transition-all duration-300 translate-y-full opacity-0`;
            toast.innerHTML = message;
            
            container.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-y-full', 'opacity-0');
            }, 10);
            
            // Remove after 3s
            setTimeout(() => {
                toast.classList.add('translate-y-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>

    @stack('scripts')
</body>
</html>
