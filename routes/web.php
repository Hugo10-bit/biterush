<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - BiteRush Food & Restaurant Ordering System
|--------------------------------------------------------------------------
*/

// Main Menu & Catalog (Home)
Route::get('/', [MenuController::class, 'index'])->name('home');
Route::get('/menu', [MenuController::class, 'index'])->name('menu');
Route::get('/product/{id}', [MenuController::class, 'show'])->name('product.show');

// Cart Operations (AJAX & Sessions)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/update', [CartController::class, 'update'])->name('update');
    Route::post('/remove', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    Route::post('/coupon', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::post('/coupon/remove', [CartController::class, 'removeCoupon'])->name('coupon.remove');
});

// Checkout & Order Placement
Route::get('/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
Route::post('/checkout', [OrderController::class, 'store'])->name('orders.store');

// Live Order Tracking & Invoices
Route::get('/orders/{order_number}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/orders/{order_number}/pay', [OrderController::class, 'pay'])->name('orders.pay');
Route::post('/orders/{order_number}/review', [OrderController::class, 'storeReview'])->name('orders.review');

// Customer Past Orders
Route::get('/my-orders', [OrderController::class, 'myOrders'])->name('orders.index');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Google OAuth Routes
Route::get('/auth/google', [AuthController::class, 'googleRedirect'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback'])->name('auth.google.callback');
Route::match(['get', 'post'], '/auth/google/instant', [AuthController::class, 'googleInstantLogin'])->name('auth.google.instant');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin() || auth()->user()->isStaff()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('menu');
    })->name('dashboard');

    // POS Screen for Staff & Admin (Protected)
    Route::middleware('admin')->group(function () {
        Route::get('/pos', [AdminController::class, 'pos'])->name('admin.pos');
        Route::post('/pos/checkout', [AdminController::class, 'posCheckout'])->name('admin.pos.checkout');

        // Admin Panel
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
            
            // Kitchen & Order Management
            Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
            Route::post('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.status');

            // Product Management
            Route::get('/products', [AdminController::class, 'products'])->name('products');
            Route::post('/products/store', [AdminController::class, 'storeProduct'])->name('products.store');
            Route::post('/products/{id}/update', [AdminController::class, 'updateProduct'])->name('products.update');
            Route::post('/products/{id}/toggle', [AdminController::class, 'toggleProduct'])->name('products.toggle');
            Route::delete('/products/{id}', [AdminController::class, 'deleteProduct'])->name('products.delete');

            // Table Management
            Route::get('/tables', [AdminController::class, 'tables'])->name('tables');
            Route::post('/tables/{id}/status', [AdminController::class, 'updateTableStatus'])->name('tables.status');

            // Coupons Management
            Route::get('/coupons', [AdminController::class, 'coupons'])->name('coupons');
            Route::post('/coupons/store', [AdminController::class, 'storeCoupon'])->name('coupons.store');
            Route::post('/coupons/{id}/toggle', [AdminController::class, 'toggleCoupon'])->name('coupons.toggle');
        });
    });
});
