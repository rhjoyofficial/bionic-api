<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Auth\Controllers\AuthController;
use App\Domains\Auth\Controllers\ForgotPasswordController;
use App\Domains\Category\Controllers\PublicCategoryController;
use App\Domains\Product\Controllers\PublicProductController;
use App\Domains\Order\Controllers\CheckoutController;
use App\Domains\Shipping\Controllers\PublicShippingZoneController;
use App\Domains\Coupon\Controllers\PublicCouponController;
use App\Domains\Cart\Controllers\CartController;
use App\Domains\Product\Controllers\ProductSearchController;
use App\Domains\Product\Controllers\ProductLandingController;
use App\Domains\Product\Controllers\ProductRecommendationController;

Route::middleware('guest:sanctum')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('user.register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('user.login');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::post('/password/reset', [ForgotPasswordController::class, 'reset'])->middleware('throttle:3,1')->name('password.update');
});

Route::middleware('auth:sanctum')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::get('/me', [AuthController::class, 'me']);
});

Route::get('/categories', [PublicCategoryController::class, 'index']);

Route::get('/products', [PublicProductController::class, 'index']);
Route::get('/products/search', [ProductSearchController::class, 'search']);
Route::get('/products/{slug}', [PublicProductController::class, 'show']);
Route::get('/products/{id}/recommendations', [ProductRecommendationController::class, 'show']);

Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1');
Route::post('/checkout/preview', [CheckoutController::class, 'preview'])->middleware('throttle:30,1');

Route::get('/shipping-zones', [PublicShippingZoneController::class, 'index']);

Route::post('/coupon/validate', [PublicCouponController::class, 'validateCoupon'])->middleware('throttle:20,1');

Route::middleware(['throttle:60,1', 'cart.session'])->prefix('cart')->group(function () {
  Route::get('/', [CartController::class, 'view']);
  Route::post('/add', [CartController::class, 'add']);
  Route::post('/add-combo', [CartController::class, 'addCombo']);
  Route::post('/update', [CartController::class, 'update']);
  Route::post('/remove', [CartController::class, 'remove']);
  Route::delete('/clear', [CartController::class, 'clear']);
});

Route::get('/landing/{slug}', [ProductLandingController::class, 'show']);
