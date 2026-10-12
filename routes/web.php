<?php

use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\BookController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\ChatController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - MNM Bookstore
|--------------------------------------------------------------------------
*/

// --- 1. PUBLIC ROUTES (Khách xem sản phẩm) ---
Route::get('/', [HomeController::class, 'showHomePage'])->name('home');
Route::post('/chat/respond', [ChatController::class, 'respond'])->name('chat.respond');

Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/search/ajax', [BookController::class, 'ajaxSearch'])->name('books.ajaxSearch');
Route::get('/books/{slug}', [BookController::class, 'showBookDetails'])->name('books.show');
Route::post('/books/{slug}/reviews', [BookController::class, 'storeReview'])->middleware('auth')->name('books.reviews.store');

// --- 2. SHOPPING CART ROUTES (Giỏ hàng) ---
Route::get('/cart', [CartController::class, 'showCart'])->name('cart.index');
Route::get('/cart/view', [CartController::class, 'showCart'])->name('cart.show');
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'updateCart'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove');
Route::delete('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');
Route::post('/cart/coupon/apply', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/cart/coupon/remove', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

// --- 3. PUBLIC ORDER TRACKING (Tra cứu đơn hàng) ---
Route::get('/orders/tracking', [AccountController::class, 'showTrackingForm'])->name('orders.tracking');

// --- 4. AUTHENTICATION ROUTES ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'processLogin']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'processRegister']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'processLogout'])->name('logout');
});

// --- 5. CHECKOUT ROUTES (Hỗ trợ khách vãng lai) ---
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'showCheckoutForm'])->name('index');
    Route::post('/', [CheckoutController::class, 'processCheckout'])->name('process');
    Route::get('/success/{order_number}', [CheckoutController::class, 'showOrderSuccess'])->name('success');
});

// --- 6. CUSTOMER ACCOUNT & NOTIFICATIONS ROUTES ---
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    // Hồ sơ cá nhân & Lịch sử đơn hàng
    Route::get('/profile', [AccountController::class, 'showProfile'])->name('profile');
    Route::post('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/orders/{order_number}', [AccountController::class, 'showOrderDetail'])->name('orders.show');
    Route::post('/orders/{order_number}/cancel', [AccountController::class, 'cancelOrder'])->name('orders.cancel');

    // Wishlist
    Route::get('/wishlist', [\App\Http\Controllers\Client\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [\App\Http\Controllers\Client\WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Hệ thống thông báo
    Route::get('/notifications', [NotificationController::class, 'listNotifications'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
});

// --- 7. ADMIN / STAFF ROUTES ---
// Sử dụng Spatie Role/Permission thay cho admin_staff thủ công
Route::middleware(['auth', 'role:admin|staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->middleware('permission:view_dashboard')->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:view_dashboard')->name('dashboard.index');
    Route::get('/dashboard/export', [DashboardController::class, 'exportRevenueCsv'])->middleware('permission:view_dashboard')->name('dashboard.export');

    // Quản lý sản phẩm & danh mục
    Route::middleware('permission:manage_books')->group(function() {
        Route::post('books/import', [AdminBookController::class, 'importCsv'])->name('books.import');
        Route::resource('books', AdminBookController::class);
        Route::resource('authors', AuthorController::class);
    });
    
    Route::middleware('permission:manage_categories')->group(function() {
        Route::resource('categories', CategoryController::class);
    });

    // Quản lý đơn hàng
    Route::middleware('permission:manage_orders')->group(function() {
        Route::get('orders/search/ajax', [AdminOrderController::class, 'ajaxSearch'])->name('orders.ajaxSearch');
        Route::get('orders/{order}/pdf', [AdminOrderController::class, 'exportPdf'])->name('orders.pdf');
        Route::resource('orders', AdminOrderController::class)->only(['index', 'show']);
        Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    });

    // Quản lý người dùng (Chỉ Admin)
    Route::middleware('permission:manage_users')->group(function() {
        Route::resource('users', UserController::class);
    });
});

