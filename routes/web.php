<?php

use App\Domains\Cart\Controllers\PublicCartController;
use App\Domains\Order\Controllers\CheckoutController;
use App\Domains\Store\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Domains\Store\Controllers\ProductPageController;

/*
|--------------------------------------------------------------------------
| Storefront Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index']);

Route::get('/shop', function () {
    return view('store.shop');
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

Route::get('/checkout', [CheckoutController::class, 'index'])->middleware(['cart.session'])->name('checkout.index');

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

Route::get('/account/login', fn() => view('auth.login'))->name('login');
Route::get('/account/register', fn() => view('auth.register'))->name('register');

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
