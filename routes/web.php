<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\Client\BookController;

// PUBLIC ROUTES (GUEST)
Route::get('/', [HomeController::class, 'showHomePage'])->name('home');

Route::get('/books', [BookController::class, 'index'])->name('books.index');

Route::get('/books/{slug}', function ($slug) {
    return "Chi tiết sách: " . $slug . " (Sẽ làm ở Phase 2)";
})->name('books.show');

Route::get('/cart', function () {
    return "Giỏ hàng (Sẽ làm ở Phase 3)";
})->name('cart.index');

// Dummy Auth Routes
Route::get('/login', function () {
    return "Trang đăng nhập (Sẽ làm ở Phase 4)";
})->name('login');

Route::post('/logout', function () {
    return redirect()->route('home');
})->name('logout');

// CUSTOMER ROUTES (Requires Auth)
Route::prefix('account')->name('account.')->group(function () {
    Route::get('/profile', function () {
        return "Hồ sơ của tôi (Sẽ làm ở Phase 4)";
    })->name('profile');
});

// STAFF/ADMIN ROUTES
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return "Admin Dashboard (Sẽ làm ở Phase 5)";
    })->name('dashboard');
});
