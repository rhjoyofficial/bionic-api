<?php

use App\Domains\Admin\Controllers\AdminDashboardController;
use App\Domains\Auth\Controllers\AdminAuthController;
use App\Domains\Auth\Controllers\WebAuthController;
use App\Domains\Cart\Controllers\PublicCartController;
use App\Domains\Customer\Controllers\CustomerDashboard;
use App\Domains\Order\Controllers\CheckoutController;
use App\Domains\Order\Controllers\OrderController;
use App\Domains\Store\Controllers\HomeController;
use App\Domains\Store\Controllers\ProductPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront Routes
|--------------------------------------------------------------------------
*/
// 1. Give the route a name
Route::get('/', [HomeController::class, 'index'])->name('home');

// 2. Redirect using that name
Route::get('/shop', function () {
    return redirect()->route('home');
})->name('shop');

Route::get('/category/{slug}', function () {
    return view('store.category');
})->name('category.view');

Route::get('/product/{slug}', [ProductPageController::class, 'show'])->name('product.show');

Route::get('/products', function () {
    return view('store.pages.products');
})->name('products.index');

Route::get('/landing/{slug}', function () {
    return view('store.landing');
})->name('landing.page');


/*
|--------------------------------------------------------------------------
| Cart & Checkout
|--------------------------------------------------------------------------
*/

Route::get('/cart', [PublicCartController::class, 'view'])->middleware(['cart.session'])->name('cart.view');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware(['cart.session', 'throttle:10,1'])
    ->name('checkout.store');

Route::get('/order-success/{order}', function ($orderNumber) {
    $order = \App\Domains\Order\Models\Order::with(['items', 'shippingAddress'])
        ->where('order_number', $orderNumber)
        ->firstOrFail();

    return view('store.order-success', compact('order'));
})->name('order.success');
Route::get('/order-failed', [OrderController::class, 'failed'])->name('order.failed');




/*
|--------------------------------------------------------------------------
| Customer Account
|--------------------------------------------------------------------------
*/

Route::prefix('account')->middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [CustomerDashboard::class, 'index'])->name('customer.dashboard');
    Route::post('/referral-code', [CustomerDashboard::class, 'generateReferralCode'])->name('customer.referral.generate');
    Route::get('/orders', [CustomerDashboard::class, 'orders'])->name('customer.orders');
    Route::get('/orders/{order}', [CustomerDashboard::class, 'orderDetails'])->name('customer.order-details');
    Route::get('/profile', [CustomerDashboard::class, 'profile'])->name('customer.profile');
});

/*
|--------------------------------------------------------------------------
| Web Auth — session-based (makes @auth / @guest work in Blade)
|--------------------------------------------------------------------------
| POST routes intentionally live in the web middleware group so that
| StartSession is active when Auth::login() is called, persisting
| the PHP session and making @auth directives work on all subsequent
| Blade page renders.
*/

Route::middleware('guest:sanctum')->group(function () {
    // GET — render forms
    Route::get('/login',          fn() => view('auth.login'))->name('login');
    Route::get('/register',       fn() => view('auth.register'))->name('register');
    Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
    Route::get('/password/reset/{token}', function (string $token) {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    })->name('password.reset');

    // POST — handle form submissions (session-based)
    Route::post('/login',    [WebAuthController::class, 'login'])->name('web.login')
        ->middleware('throttle:10,1');
    Route::post('/register', [WebAuthController::class, 'register'])->name('web.register')
        ->middleware('throttle:5,1');
});

// Logout is accessible to authenticated users
Route::post('/logout', [WebAuthController::class, 'logout'])
    ->middleware('auth:sanctum')
    ->name('web.logout');

/*
|--------------------------------------------------------------------------
| Informational Pages
|--------------------------------------------------------------------------
*/

Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');

Route::get('/faq', function () {
    return view('pages.faq');
})->name('faq');

Route::get('/privacy-policy', function () {
    return view('pages.privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('pages.terms');
})->name('terms');


/*
|--------------------------------------------------------------------------
| Admin Panel — Authentication (public)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {
    Route::get('/login',  [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit')
        ->middleware('throttle:5,1');
});

/*
|--------------------------------------------------------------------------
| Admin Panel — Protected Routes
|--------------------------------------------------------------------------
| All admin Blade pages sit behind two layers:
|   1. auth:sanctum — must be logged in
|   2. admin       — must hold a non-Customer role
|
| Individual permission checks happen at the controller/view level.
*/

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Dashboard
    Route::get('/dashboard', AdminDashboardController::class)->name('admin.dashboard');

    // Products
    Route::get('/products',              fn() => view('admin.products.index'))->name('admin.products')
        ->middleware('permission:product.view');
    Route::get('/products/create',       fn() => view('admin.products.create'))->name('admin.products.create')
        ->middleware('permission:product.create');
    Route::get('/products/{product}/edit', fn() => view('admin.products.edit'))->name('admin.products.edit')
        ->middleware('permission:product.update');

    // Categories
    Route::get('/categories',              fn() => view('admin.categories.index'))->name('admin.categories')
        ->middleware('permission:category.view');
    Route::get('/categories/create',       fn() => view('admin.categories.create'))->name('admin.categories.create')
        ->middleware('permission:category.create');
    Route::get('/categories/{category}/edit', fn() => view('admin.categories.edit'))->name('admin.categories.edit')
        ->middleware('permission:category.update');

    // Orders
    Route::get('/orders',        fn() => view('admin.orders.index'))->name('admin.orders')
        ->middleware('permission:order.view');
    Route::get('/orders/{order}', fn() => view('admin.orders.show'))->name('admin.orders.show')
        ->middleware('permission:order.view');

    // Coupons
    Route::get('/coupons',        fn() => view('admin.coupons.index'))->name('admin.coupons')
        ->middleware('permission:coupon.view');
    Route::get('/coupons/create', fn() => view('admin.coupons.create'))->name('admin.coupons.create')
        ->middleware('permission:coupon.create');

    // Shipping
    Route::get('/shipping', fn() => view('admin.shipping.index'))->name('admin.shipping')
        ->middleware('permission:shipping.view');

    // Webhooks
    Route::get('/webhooks', fn() => view('admin.webhooks.index'))->name('admin.webhooks')
        ->middleware('permission:system.webhooks');

    // Activity Log
    Route::get('/activity-log', fn() => view('admin.activity-log.index'))->name('admin.activity-log')
        ->middleware('permission:system.activity_log');
});
