<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminWithdrawalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StoreCmsController;
use App\Http\Controllers\StorePageController;
use App\Http\Controllers\StoreSettingsController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

// Guest (public) routes
Route::middleware(['guest'])->group(function () {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/{store_slug}/login', [AuthController::class, 'showStoreLogin'])->name('store.login');
    Route::post('/{store_slug}/login', [AuthController::class, 'storeLogin']);
    Route::get('/{store_slug}/register', [AuthController::class, 'showStoreRegister'])->name('store.register');
    Route::post('/{store_slug}/register', [AuthController::class, 'storeRegister']);
});

// Authenticated routes (any role)
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/admin/impersonate/stop', [AdminController::class, 'stopImpersonation'])->name('admin.impersonate.stop');
});

// Buyer area
Route::middleware(['auth', 'role:buyer'])->prefix('{store_slug}')->group(function () {
    Route::get('/account', [AccountController::class, 'show'])->name('account');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{id}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::patch('/addresses/{id}/default', [AddressController::class, 'makeDefault'])->name('addresses.default');
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderNumber}/success', [CheckoutController::class, 'success'])->name('orders.success');
    Route::get('/orders/{orderNumber}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
});

// Seller area
Route::middleware(['auth', 'role:seller'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('seller.orders.index');
    Route::get('/customers', [CustomerController::class, 'index'])->name('seller.customers.index');
    Route::get('/orders/{orderNumber}/invoice', [OrderController::class, 'invoice'])->name('seller.orders.invoice');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/store-settings', [StoreSettingsController::class, 'edit'])->name('store-settings.edit');
    Route::put('/store-settings', [StoreSettingsController::class, 'update'])->name('store-settings.update');
    Route::get('/store-cms', [StoreCmsController::class, 'edit'])->name('store-cms.edit');
    Route::put('/store-cms', [StoreCmsController::class, 'update'])->name('store-cms.update');
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('/products/{id}/stock', [ProductController::class, 'updateStock'])->name('products.stock');
    Route::post('/products/{id}/toggle-active', [ProductController::class, 'toggleActive'])->name('products.toggle-active');
    Route::get('/inventory', [StockController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{id}/history', [StockController::class, 'history'])->name('inventory.history');
    Route::post('/inventory/{id}/in', [StockController::class, 'storeIn'])->name('inventory.in');
    Route::post('/inventory/{id}/out', [StockController::class, 'storeOut'])->name('inventory.out');
    Route::post('/inventory/{id}/adjust', [StockController::class, 'adjust'])->name('inventory.adjust');
    Route::get('/wallet', [WithdrawalController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/withdraw', [WithdrawalController::class, 'store'])->name('wallet.withdraw');
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::post('/catalog/categories', [CatalogController::class, 'storeCategory'])->name('catalog.categories.store');
    Route::put('/catalog/categories/{id}', [CatalogController::class, 'updateCategory'])->name('catalog.categories.update');
    Route::delete('/catalog/categories/{id}', [CatalogController::class, 'destroyCategory'])->name('catalog.categories.destroy');
    Route::post('/catalog/brands', [CatalogController::class, 'storeBrand'])->name('catalog.brands.store');
    Route::put('/catalog/brands/{id}', [CatalogController::class, 'updateBrand'])->name('catalog.brands.update');
    Route::delete('/catalog/brands/{id}', [CatalogController::class, 'destroyBrand'])->name('catalog.brands.destroy');
    Route::post('/catalog/labels', [CatalogController::class, 'storeLabel'])->name('catalog.labels.store');
    Route::put('/catalog/labels/{id}', [CatalogController::class, 'updateLabel'])->name('catalog.labels.update');
    Route::delete('/catalog/labels/{id}', [CatalogController::class, 'destroyLabel'])->name('catalog.labels.destroy');
    Route::get('/products/{id}/variants', [ProductVariantController::class, 'index'])->name('products.variants.index');
    Route::post('/products/{id}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
    Route::put('/products/{id}/variants/{variant}', [ProductVariantController::class, 'update'])->name('products.variants.update');
    Route::delete('/products/{id}/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');
});

// Admin area
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::patch('/admin/users/{id}/role', [AdminController::class, 'updateRole'])->name('admin.users.role');
    Route::delete('/admin/users/{id}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::post('/admin/users/{id}/impersonate', [AdminController::class, 'impersonate'])->name('admin.users.impersonate');
    Route::get('/admin/withdrawals', [AdminWithdrawalController::class, 'index'])->name('admin.withdrawals.index');
    Route::patch('/admin/withdrawals/{id}/approve', [AdminWithdrawalController::class, 'approve'])->name('admin.withdrawals.approve');
    Route::patch('/admin/withdrawals/{id}/reject', [AdminWithdrawalController::class, 'reject'])->name('admin.withdrawals.reject');
    Route::patch('/admin/withdrawals/{id}/transferred', [AdminWithdrawalController::class, 'markTransferred'])->name('admin.withdrawals.transferred');
});

// Public Storefront (Fallback routes)
Route::get('/{store_slug}', [StorePageController::class, 'show'])->name('store.show');
Route::get('/{store_slug}/p/{product_slug}', [StorePageController::class, 'product'])->name('store.product.show');
