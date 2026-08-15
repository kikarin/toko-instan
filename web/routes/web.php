<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminWithdrawalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CheckoutVoucherController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\DigitalDownloadController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\MediaProxyController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OtpLoginController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductAiController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SellerChatController;
use App\Http\Controllers\SellerNotificationController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StoreBlogController;
use App\Http\Controllers\StoreChatController;
use App\Http\Controllers\StoreCmsController;
use App\Http\Controllers\StorePageController;
use App\Http\Controllers\StoreSettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TaxReportController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::get('/media/{path}', [MediaProxyController::class, 'show'])
    ->where('path', '.*')
    ->name('media.proxy');

Route::post('/webhooks/midtrans', MidtransWebhookController::class)
    ->middleware('throttle:60,1')
    ->name('webhooks.midtrans');

Route::middleware(['guest'])->group(function () {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('/auth/google', [AuthController::class, 'google'])->middleware('throttle:auth');

    Route::get('/otp-login', [OtpLoginController::class, 'show'])->name('otp.login');
    Route::post('/otp-login', [OtpLoginController::class, 'send'])->middleware('throttle:auth')->name('otp.send');
    Route::post('/otp-login/verify', [OtpLoginController::class, 'verify'])->middleware('throttle:auth')->name('otp.verify');

    Route::get('/{store_slug}/login', [AuthController::class, 'showStoreLogin'])->name('store.login');
    Route::post('/{store_slug}/login', [AuthController::class, 'storeLogin'])->middleware('throttle:auth');
    Route::get('/{store_slug}/register', [AuthController::class, 'showStoreRegister'])->name('store.register');
    Route::post('/{store_slug}/register', [AuthController::class, 'storeRegister'])->middleware('throttle:auth');
    Route::get('/{store_slug}/otp-login', [OtpLoginController::class, 'show'])->name('store.otp.login');
    Route::post('/{store_slug}/otp-login', [OtpLoginController::class, 'send'])->middleware('throttle:auth')->name('store.otp.send');
    Route::post('/{store_slug}/otp-login/verify', [OtpLoginController::class, 'verify'])->middleware('throttle:auth')->name('store.otp.verify');

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:auth')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:auth')->name('password.store');
    Route::get('/{store_slug}/forgot-password', [PasswordResetController::class, 'showForgotPassword'])->name('store.password.request');
    Route::post('/{store_slug}/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:auth')->name('store.password.email');
    Route::get('/{store_slug}/reset-password/{token}', [PasswordResetController::class, 'showResetPassword'])->name('store.password.reset');
    Route::post('/{store_slug}/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:auth')->name('store.password.store');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/admin/impersonate/stop', [AdminController::class, 'stopImpersonation'])->name('admin.impersonate.stop');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// Admin area — must be registered before `/{store_slug}/…` or admin paths are captured as store slugs.
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/tenants', [AdminController::class, 'tenants'])->name('admin.tenants');
    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::patch('/users/{id}/role', [AdminController::class, 'updateRole'])->name('admin.users.role');
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::post('/users/{id}/impersonate', [AdminController::class, 'impersonate'])->name('admin.users.impersonate');
    Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('admin.withdrawals.index');
    Route::patch('/withdrawals/{id}/approve', [AdminWithdrawalController::class, 'approve'])->name('admin.withdrawals.approve');
    Route::patch('/withdrawals/{id}/reject', [AdminWithdrawalController::class, 'reject'])->name('admin.withdrawals.reject');
    Route::patch('/withdrawals/{id}/transferred', [AdminWithdrawalController::class, 'markTransferred'])->name('admin.withdrawals.transferred');
});

$reservedStoreSlugs = 'admin|login|register|dashboard|horizon|uploads|products|inventory|wallet|catalog|customers|profile|forgot-password|reset-password|auth|up|email|otp-login|webhooks|media|vouchers|tax-reports|blog|chats|developer|api';

Route::middleware(['auth', 'verified'])->prefix('{store_slug}')->where(['store_slug' => "^(?!($reservedStoreSlugs)$)[^/]+"])->group(function () {
    Route::post('/shipping/quote', [ShippingController::class, 'quote'])->name('shipping.quote');
    Route::post('/vouchers/preview', [CheckoutVoucherController::class, 'preview'])->name('vouchers.preview');
});

Route::middleware(['auth', 'verified', 'role:buyer'])->prefix('{store_slug}')->where(['store_slug' => "^(?!($reservedStoreSlugs)$)[^/]+"])->group(function () {
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
    Route::post('/orders/{orderNumber}/sync-payment', [PaymentController::class, 'sync'])->name('orders.sync-payment');
    Route::get('/orders/{orderNumber}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/items/{orderItemId}/download', [DigitalDownloadController::class, 'download'])->name('orders.digital.download');
    Route::post('/orders/{orderNumber}/payment-proof', [PaymentController::class, 'uploadProof'])->name('orders.payment-proof');
    Route::post('/p/{product_slug}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

Route::middleware(['auth', 'verified', 'role:seller'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('seller.orders.index');
    Route::get('/customers', [CustomerController::class, 'index'])->name('seller.customers.index');
    Route::get('/orders/{orderNumber}/invoice', [OrderController::class, 'invoice'])->name('seller.orders.invoice');
    Route::get('/notifications', [SellerNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [SellerNotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/chats', [SellerChatController::class, 'index'])->name('chats.index');
    Route::get('/developer', [DeveloperController::class, 'index'])->name('developer.index');
    Route::post('/developer/tokens', [DeveloperController::class, 'store'])->name('developer.tokens.store');
    Route::delete('/developer/tokens/{id}', [DeveloperController::class, 'destroy'])->name('developer.tokens.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/store-settings', [StoreSettingsController::class, 'edit'])->name('store-settings.edit');
    Route::put('/store-settings', [StoreSettingsController::class, 'update'])->name('store-settings.update');
    Route::get('/store-cms', [StoreCmsController::class, 'edit'])->name('store-cms.edit');
    Route::put('/store-cms', [StoreCmsController::class, 'update'])->name('store-cms.update');
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/payments/{paymentId}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::post('/products/ai-generate', [ProductAiController::class, 'generate'])
        ->middleware('throttle:ai-generate')
        ->name('products.ai.generate');
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
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
    Route::post('/subscription/sync', [SubscriptionController::class, 'sync'])->name('subscription.sync');
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::put('/vouchers/{id}', [VoucherController::class, 'update'])->name('vouchers.update');
    Route::delete('/vouchers/{id}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');
    Route::get('/blog', [BlogPostController::class, 'index'])->name('blog.index');
    Route::post('/blog', [BlogPostController::class, 'store'])->name('blog.store');
    Route::put('/blog/{id}', [BlogPostController::class, 'update'])->name('blog.update');
    Route::delete('/blog/{id}', [BlogPostController::class, 'destroy'])->name('blog.destroy');
    Route::post('/blog/categories', [BlogPostController::class, 'storeCategory'])->name('blog.categories.store');
    Route::get('/tax-reports', [TaxReportController::class, 'index'])->name('tax-reports.index');
    Route::get('/tax-reports/export', [TaxReportController::class, 'export'])->name('tax-reports.export');
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
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');
    Route::post('/uploads/digital', [UploadController::class, 'storeDigital'])->name('uploads.digital');
});

Route::get('/{store_slug}/chat', [StoreChatController::class, 'show'])
    ->where('store_slug', "^(?!($reservedStoreSlugs)$)[^/]+")
    ->name('store.chat.show');
Route::post('/{store_slug}/chat', [StoreChatController::class, 'store'])
    ->where('store_slug', "^(?!($reservedStoreSlugs)$)[^/]+")
    ->name('store.chat.store');

Route::get('/{store_slug}/sitemap.xml', [SeoController::class, 'sitemap'])
    ->where('store_slug', "^(?!($reservedStoreSlugs)$)[^/]+")
    ->name('store.sitemap');
Route::get('/{store_slug}/robots.txt', [SeoController::class, 'robots'])
    ->where('store_slug', "^(?!($reservedStoreSlugs)$)[^/]+")
    ->name('store.robots');
Route::get('/{store_slug}/blog', [StoreBlogController::class, 'index'])
    ->middleware('store.visit')
    ->where('store_slug', "^(?!($reservedStoreSlugs)$)[^/]+")
    ->name('store.blog.index');
Route::get('/{store_slug}/blog/{post_slug}', [StoreBlogController::class, 'show'])
    ->middleware('store.visit')
    ->where('store_slug', "^(?!($reservedStoreSlugs)$)[^/]+")
    ->name('store.blog.show');

// Public Storefront — product detail MUST be registered before the catalog catch-all.
// `store_slug` must be a single path segment (`[^/]+`); `.+` would swallow `/p/{product_slug}`.
Route::get('/{store_slug}/p/{product_slug}', [StorePageController::class, 'product'])
    ->middleware('store.visit')
    ->where('store_slug', "^(?!($reservedStoreSlugs)$)[^/]+")
    ->name('store.product.show');
Route::get('/{store_slug}', [StorePageController::class, 'show'])
    ->middleware('store.visit')
    ->where('store_slug', "^(?!($reservedStoreSlugs)$)[^/]+")
    ->name('store.show');
