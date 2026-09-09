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

// Auth Routes
use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'processLogin']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'processRegister']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'processLogout'])->name('logout');
});

// CUSTOMER ROUTES (Requires Auth)
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/profile', function () {
        return "Hồ sơ của tôi (Sẽ làm ở Phase 4)";
    })->name('profile');
});

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\UserController;

Route::middleware(['auth', 'admin_staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    // Product Management
    Route::resource('books', BookController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('authors', AuthorController::class);
    // User Management
    Route::resource('users', UserController::class);
});
