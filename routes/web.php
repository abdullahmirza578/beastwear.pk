<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ShippingSettingsController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\PaymentReviewController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CatalogController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\ContactController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentProofController;
use App\Http\Controllers\Customer\PasswordResetController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\TrackOrderController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\CustomerAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::get('/category', [CatalogController::class, 'category'])->name('category');
Route::get('/product/{slug}', [CatalogController::class, 'product'])->name('products.show');
Route::redirect('/product', '/shop')->name('product');
Route::get('/shop', [CatalogController::class, 'shop'])->name('shop');
Route::view('/contact', 'pages.contact')->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::view('/faq', 'pages.faq')->name('faq');
Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('forgot-password');
Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
    ->middleware('throttle:5,1')
    ->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->middleware('throttle:5,1')
    ->name('password.update');
Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [CustomerAuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.store');
Route::get('/register', [CustomerAuthController::class, 'showRegistration'])->name('register');
Route::post('/register', [CustomerAuthController::class, 'register'])
    ->middleware('throttle:5,1')
    ->name('register.store');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/returns', 'pages.returns')->name('returns');
Route::get('/search', [CatalogController::class, 'search'])->name('search');
Route::view('/shipping', 'pages.shipping')->name('shipping');
Route::view('/size-guide', 'pages.size-guide')->name('size-guide');
Route::view('/terms', 'pages.terms')->name('terms');
Route::get('/track-order', [TrackOrderController::class, 'show'])->name('track-order');
Route::post('/track-order', [TrackOrderController::class, 'lookup'])->name('track-order.lookup');

Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
    Route::post('/account/addresses', [AccountController::class, 'storeAddress'])->name('addresses.store');
    Route::put('/account/addresses/{address}', [AccountController::class, 'updateAddress'])->name('addresses.update');
    Route::delete('/account/addresses/{address}', [AccountController::class, 'destroyAddress'])->name('addresses.destroy');

    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');
    Route::delete('/cart/items', [CartController::class, 'destroySelected'])->name('cart.items.destroy-selected');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order:order_number}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order:order_number}/payment-proof', [PaymentProofController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('orders.payment-proof');
    Route::get('/order-details', fn () => redirect()->route('orders'))->name('order-details');
    Route::get('/order-success', fn () => redirect()->route('orders'))->name('order-success');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
});

Route::get('/index.html', fn () => redirect()->route('home', [], 301));

foreach ([
    'about', 'account', 'cart', 'category', 'checkout', 'contact', 'faq',
    'forgot-password', 'login', 'order-details', 'order-success', 'orders',
    'privacy', 'product', 'register', 'returns', 'search', 'shipping',
    'shop', 'size-guide', 'terms', 'track-order', 'wishlist',
] as $page) {
    Route::get("/{$page}.html", fn () => redirect()->route($page, request()->query(), 301));
}

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
    Route::get('/', fn () => redirect()->route('admin.dashboard'));

    Route::get('/banners/data', [AdminBannerController::class, 'data'])->name('banners.data');
    Route::post('/banners/hero', [AdminBannerController::class, 'saveHero'])->name('banners.hero.save');
    Route::post('/banners/promos', [AdminBannerController::class, 'storePromo'])->name('banners.promos.store');
    Route::put('/banners/promos/{banner}', [AdminBannerController::class, 'updatePromo'])->name('banners.promos.update');
    Route::delete('/banners/promos/{banner}', [AdminBannerController::class, 'destroyPromo'])->name('banners.promos.destroy');
    Route::get('/settings/shipping/data', [ShippingSettingsController::class, 'show'])->name('shipping-settings.data');
    Route::put('/settings/shipping', [ShippingSettingsController::class, 'update'])->name('shipping-settings.update');
    Route::get('/settings/data', [SiteSettingsController::class, 'show'])->name('settings.data');
    Route::post('/settings', [SiteSettingsController::class, 'update'])->name('settings.update');
    Route::get('/payments', [PaymentReviewController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}/proof', [PaymentReviewController::class, 'proof'])->name('payments.proof');
    Route::patch('/payments/{payment}/approve', [PaymentReviewController::class, 'approve'])->name('payments.approve');
    Route::patch('/payments/{payment}/reject', [PaymentReviewController::class, 'reject'])->name('payments.reject');

    Route::get('/data/orders', [AdminOrderController::class, 'index'])->name('data.orders.index');
    Route::get('/data/orders/{orderKey}', [AdminOrderController::class, 'show'])->name('data.orders.show');
    Route::put('/data/orders/{orderKey}', [AdminOrderController::class, 'update'])->name('data.orders.update');
    Route::get('/data/customers', [AdminCustomerController::class, 'index'])->name('data.customers.index');
    Route::get('/data/customers/{customerId}', [AdminCustomerController::class, 'show'])->name('data.customers.show');
    Route::put('/data/customers/{customerId}', [AdminCustomerController::class, 'update'])->name('data.customers.update');
    Route::get('/reviews/data', [AdminReviewController::class, 'index'])->name('reviews.data');
    Route::patch('/reviews/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::view('/dashboard', 'admin.dashboard.index')->name('dashboard');
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/inventory/data', [AdminInventoryController::class, 'index'])->name('inventory.data');
    Route::patch('/inventory/{product}/stock', [AdminInventoryController::class, 'updateStock'])->name('inventory.stock');
    Route::patch('/inventory/{product}/threshold', [AdminInventoryController::class, 'updateThreshold'])->name('inventory.threshold');
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/form', [AdminCategoryController::class, 'edit'])->name('categories.form');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
    Route::view('/orders', 'admin.orders.index')->name('orders.index');
    Route::view('/orders/show', 'admin.orders.show')->name('orders.show');
    Route::view('/customers', 'admin.customers.index')->name('customers.index');
    Route::view('/customers/show', 'admin.customers.show')->name('customers.show');
    Route::get('/inventory', [AdminInventoryController::class, 'show'])->name('inventory.index');
    Route::view('/coupons', 'admin.coupons.index')->name('coupons.index');
    Route::get('/reviews', [AdminReviewController::class, 'show'])->name('reviews.index');
    Route::view('/banners', 'admin.banners.index')->name('banners.index');
    Route::view('/admins', 'admin.admins.index')->name('admins.index');
    Route::view('/reports', 'admin.reports.index')->name('reports');
    Route::view('/settings', 'admin.settings.index')->name('settings');
    Route::view('/settings/shipping', 'admin.settings.shipping')->name('shipping-settings');
    Route::view('/settings/profile', 'admin.settings.profile')->name('profile');

    $legacyAdminPages = [
        'dashboard.html' => 'admin.dashboard',
        'products.html' => 'admin.products.index',
        'add-product.html' => 'admin.products.create',
        'edit-product.html' => 'admin.products.edit',
        'categories.html' => 'admin.categories.index',
        'category-form.html' => 'admin.categories.form',
        'orders.html' => 'admin.orders.index',
        'order-details.html' => 'admin.orders.show',
        'customers.html' => 'admin.customers.index',
        'customer-details.html' => 'admin.customers.show',
        'inventory.html' => 'admin.inventory.index',
        'coupons.html' => 'admin.coupons.index',
        'reviews.html' => 'admin.reviews.index',
        'banners.html' => 'admin.banners.index',
        'admins.html' => 'admin.admins.index',
        'reports.html' => 'admin.reports',
        'settings.html' => 'admin.settings',
        'shipping-settings.html' => 'admin.shipping-settings',
        'profile.html' => 'admin.profile',
    ];

    foreach ($legacyAdminPages as $legacyPath => $routeName) {
        Route::get("/{$legacyPath}", fn () => redirect()->route($routeName, request()->query(), 301));
    }
});

Route::get('/admin/admin-login.html', fn () => redirect()->route('admin.login', request()->query(), 301));
