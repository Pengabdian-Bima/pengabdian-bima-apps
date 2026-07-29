<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserAddressController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RajaOngkirController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\CashierController;
use App\Http\Controllers\PreOrderController;
use App\Http\Controllers\Admin\PreOrderController as AdminPreOrderController;

// Public
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
Route::get('/produk/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/api/locations/search', [RajaOngkirController::class, 'search'])->name('rajaongkir.search');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// User (authenticated)
Route::middleware('auth')->group(function () {
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang', [CartController::class, 'add'])->name('cart.add');
    Route::put('/keranjang/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/keranjang/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/pesanan/{order}/bayar', fn (\App\Models\Order $order) => redirect()->route('orders.show', $order));
    Route::post('/pesanan/{order}/bayar', [OrderController::class, 'storePayment'])->name('orders.store-payment');
    Route::post('/pesanan/{order}/batal', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/pesanan/{order}/selesai', [OrderController::class, 'complete'])->name('orders.complete');

    Route::get('/profil', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    Route::post('/alamat', [UserAddressController::class, 'store'])->name('addresses.store');
    Route::put('/alamat/{address}', [UserAddressController::class, 'update'])->name('addresses.update');
    Route::delete('/alamat/{address}', [UserAddressController::class, 'destroy'])->name('addresses.destroy');
    Route::put('/alamat/{address}/default', [UserAddressController::class, 'setDefault'])->name('addresses.default');

    Route::post('/api/shipping-cost', [RajaOngkirController::class, 'calculateCost'])->name('rajaongkir.cost');
    Route::post('/ulasan', [ReviewController::class, 'store'])->name('reviews.store');

    // Pre-Order
    Route::get('/pre-order', [PreOrderController::class, 'index'])->name('pre-orders.index');
    Route::get('/pre-order/buat', [PreOrderController::class, 'create'])->name('pre-orders.create');
    Route::post('/pre-order', [PreOrderController::class, 'store'])->name('pre-orders.store');
    Route::get('/pre-order/{preOrder}', [PreOrderController::class, 'show'])->name('pre-orders.show');
    Route::get('/pre-order/{preOrder}/pengiriman', [PreOrderController::class, 'selectShipping'])->name('pre-orders.select-shipping');
    Route::post('/pre-order/{preOrder}/pengiriman', [PreOrderController::class, 'storeShipping'])->name('pre-orders.store-shipping');
    Route::post('/pre-order/{preOrder}/bayar', [PreOrderController::class, 'storePayment'])->name('pre-orders.store-payment');
    Route::post('/pre-order/{preOrder}/batal', [PreOrderController::class, 'cancel'])->name('pre-orders.cancel');
});

// Admin
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('realtime', [DashboardController::class, 'realtime'])->name('dashboard.realtime');

    Route::resource('products', AdminProductController::class)->except('show');
    Route::delete('product-images/{productImage}', [AdminProductController::class, 'deleteImage'])->name('product-images.destroy');

    Route::get('cashier', [CashierController::class, 'index'])->name('cashier.index');
    Route::post('cashier', [CashierController::class, 'store'])->name('cashier.store');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('stock/{product}/history', [StockController::class, 'history'])->name('stock.history');
    Route::post('stock/{product}/adjust', [StockController::class, 'adjust'])->name('stock.adjust');

    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/products', [ReportController::class, 'productReport'])->name('reports.products');
    Route::get('reports/products/export-pdf', [ReportController::class, 'exportProductReportPdf'])->name('reports.products-pdf');
    Route::get('reports/products/export-excel', [ReportController::class, 'exportProductReportExcel'])->name('reports.products-excel');
    Route::get('reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::get('reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
    Route::get('reports/best-selling-pdf', [ReportController::class, 'exportBestSellingPdf'])->name('reports.best-selling-pdf');
    Route::get('reports/best-selling-excel', [ReportController::class, 'exportBestSellingExcel'])->name('reports.best-selling-excel');

    Route::get('profile', [AdminProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.update-password');

    // Pre-Order (Admin)
    Route::get('pre-orders', [AdminPreOrderController::class, 'index'])->name('pre-orders.index');
    Route::get('pre-orders/{preOrder}', [AdminPreOrderController::class, 'show'])->name('pre-orders.show');
    Route::put('pre-orders/{preOrder}/accept', [AdminPreOrderController::class, 'accept'])->name('pre-orders.accept');
    Route::put('pre-orders/{preOrder}/reject', [AdminPreOrderController::class, 'reject'])->name('pre-orders.reject');
    Route::put('pre-orders/{preOrder}/complete', [AdminPreOrderController::class, 'complete'])->name('pre-orders.complete');
});
