<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('store.home');
})->name('home');

Route::get('/shop', function () {
    return view('store.shop');
})->name('shop');

Route::get('/category/{slug}', function () {
    return view('store.category');
})->name('category.view');

Route::get('/product/{slug}', function () {
    return view('store.product');
})->name('product.view');

Route::get('/landing/{slug}', function () {
    return view('store.landing');
})->name('landing.page');


/*
|--------------------------------------------------------------------------
| Cart & Checkout
|--------------------------------------------------------------------------
*/

Route::get('/cart', function () {
    return view('store.cart');
})->name('cart');

Route::get('/checkout', function () {
    return view('store.checkout');
})->name('checkout');

Route::get('/order-success/{order}', function () {
    return view('store.order-success');
})->name('order.success');

Route::get('/order-failed', function () {
    return view('store.order-failed');
})->name('order.failed');


/*
|--------------------------------------------------------------------------
| Customer Account
|--------------------------------------------------------------------------
*/

Route::prefix('account')->group(function () {

    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::get('/dashboard', function () {
        return view('account.dashboard');
    })->name('account.dashboard');

    Route::get('/orders', function () {
        return view('account.orders');
    })->name('account.orders');

    Route::get('/orders/{order}', function () {
        return view('account.order-details');
    })->name('account.order.details');

    Route::get('/profile', function () {
        return view('account.profile');
    })->name('account.profile');
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
