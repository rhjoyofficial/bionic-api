<?php

use App\Domains\Auth\Controllers\AuthController;
use App\Domains\Cart\Controllers\PublicCartController;
use App\Domains\Order\Controllers\CheckoutController;
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

Route::get('/order-failed', function () {
    return view('store.order-failed');
})->name('order.failed');


/*
|--------------------------------------------------------------------------
| Customer Account
|--------------------------------------------------------------------------
*/

Route::prefix('account')->middleware('auth:sanctum')->group(function () {

    Route::get('/dashboard', fn() => view('account.dashboard'));

    Route::get('/orders', fn() => view('account.orders'));

    Route::get('/orders/{order}', fn() => view('account.order-details'));

    Route::get('/profile', fn() => view('account.profile'));
});

Route::middleware('guest:sanctum')->group(function () {
    Route::get('/login', fn() => view('auth.login'))->name('login');
    Route::get('/register', fn() => view('auth.register'))->name('register');

    // Password reset — named password.* so the layout excludes header/footer
    Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
    Route::get('/password/reset/{token}', function (string $token) {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    })->name('password.reset');
});

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
| Admin Panel (Blade Admin)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {

    Route::get('/login', function () {
        return view('admin.auth.login');
    })->name('admin.login');

    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});


/*
|--------------------------------------------------------------------------
| Admin Product Management
|--------------------------------------------------------------------------
*/

Route::prefix('admin/products')->group(function () {

    Route::get('/', function () {
        return view('admin.products.index');
    })->name('admin.products');

    Route::get('/create', function () {
        return view('admin.products.create');
    })->name('admin.products.create');

    Route::get('/{product}/edit', function () {
        return view('admin.products.edit');
    })->name('admin.products.edit');
});


/*
|--------------------------------------------------------------------------
| Admin Categories
|--------------------------------------------------------------------------
*/

Route::prefix('admin/categories')->group(function () {

    Route::get('/', function () {
        return view('admin.categories.index');
    });

    Route::get('/create', function () {
        return view('admin.categories.create');
    });

    Route::get('/{category}/edit', function () {
        return view('admin.categories.edit');
    });
});


/*
|--------------------------------------------------------------------------
| Admin Orders
|--------------------------------------------------------------------------
*/

Route::prefix('admin/orders')->group(function () {

    Route::get('/', function () {
        return view('admin.orders.index');
    });

    Route::get('/{order}', function () {
        return view('admin.orders.show');
    });
});


/*
|--------------------------------------------------------------------------
| Admin Coupons
|--------------------------------------------------------------------------
*/

Route::prefix('admin/coupons')->group(function () {

    Route::get('/', function () {
        return view('admin.coupons.index');
    });

    Route::get('/create', function () {
        return view('admin.coupons.create');
    });
});


/*
|--------------------------------------------------------------------------
| Admin Shipping
|--------------------------------------------------------------------------
*/

Route::prefix('admin/shipping')->group(function () {

    Route::get('/', function () {
        return view('admin.shipping.index');
    });
});


/*
|--------------------------------------------------------------------------
| Admin Webhooks
|--------------------------------------------------------------------------
*/

Route::prefix('admin/webhooks')->group(function () {

    Route::get('/', function () {
        return view('admin.webhooks.index');
    });
});
