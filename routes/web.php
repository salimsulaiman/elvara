<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Customer\CatalogController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\DiscountController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// Customer Public Routes
// ==========================================
Route::get('/', [CatalogController::class, 'home'])->name('customer.home');
Route::get('/catalog', [CatalogController::class, 'catalog'])->name('customer.catalog');
Route::get('/product/{slug}', [CatalogController::class, 'productShow'])->name('customer.product.show');
Route::get('/api/search-predictive', [CatalogController::class, 'searchPredictive']);

// ==========================================
// Auth Routes
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
    Route::post('/register', [LoginController::class, 'register']);
    
    // Google OAuth (Simulated Local Login)
    Route::get('/auth/google/simulate', [LoginController::class, 'simulateGoogleLogin'])->name('auth.google.simulate');

    /*
    // KODE REAL INTEGRASI GOOGLE SOCIALITE (DITEKAN/DI-COMMENT)
    Route::get('/auth/google', [LoginController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback']);
    */

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp'])->name('password.email');
    Route::get('/reset-password', [PasswordResetController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ==========================================
// Customer Protected Routes (Auth Required)
// ==========================================
Route::middleware('auth')->group(function () {
    // Wishlist
    Route::get('/wishlist', [CatalogController::class, 'wishlistIndex'])->name('customer.wishlist.index');
    Route::post('/wishlist/{productId}', [CatalogController::class, 'wishlistToggle'])->name('customer.wishlist.toggle');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('customer.cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('customer.cart.store');
    Route::patch('/cart/{id}', [CartController::class, 'update'])->name('customer.cart.update');
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('customer.cart.destroy');

    // Checkout & RajaOngkir API
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('customer.checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('customer.checkout.store');
    Route::post('/checkout/apply-voucher', [CheckoutController::class, 'applyVoucher'])->name('customer.checkout.apply-voucher');

    // Dynamic shipping lookup
    Route::get('/api/provinces', [CheckoutController::class, 'getProvinces']);
    Route::get('/api/cities/{provinceId}', [CheckoutController::class, 'getCities']);
    Route::get('/api/subdistricts/{cityId}', [CheckoutController::class, 'getSubdistricts']);
    Route::post('/api/shipping-cost', [CheckoutController::class, 'calculateShipping']);

    // Orders & Tracking
    Route::get('/orders', [OrderController::class, 'index'])->name('customer.order.index');
    Route::get('/orders/{reference}', [OrderController::class, 'tracking'])->name('customer.order.tracking');
});

// ==========================================
// Admin Protected Routes (Auth & Admin role)
// ==========================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/orders/{id}/waybill', [DashboardController::class, 'updateWaybill'])->name('orders.waybill');

    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('products', ProductController::class);
    
    // Products variant visual and sku matrix
    Route::post('products/{product}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
    Route::delete('variants/{variant}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');
    Route::post('variants/{variant}/skus', [ProductController::class, 'storeSku'])->name('products.variants.skus.store');
    Route::delete('skus/{sku}', [ProductController::class, 'destroySku'])->name('products.variants.skus.destroy');
    Route::post('skus/{sku}/discount', [ProductController::class, 'updateSkuDiscount'])->name('products.skus.discount');

    Route::resource('vouchers', VoucherController::class)->except(['show']);
    Route::resource('discounts', DiscountController::class)->except(['show']);
    Route::resource('banners', BannerController::class)->except(['show']);
});

// ==========================================
// Xendit Local Payment Simulator (Mock)
// ==========================================
Route::get('/mock-xendit-payment/{reference}', [OrderController::class, 'showMockPayment'])->name('mock.xendit.payment');
Route::post('/mock-xendit-payment/{reference}/pay', [OrderController::class, 'payMockPayment'])->name('mock.xendit.pay');
