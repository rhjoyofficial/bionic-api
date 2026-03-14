<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Auth\Controllers\AuthController;
use App\Domains\Category\Controllers\PublicCategoryController;
use App\Domains\Product\Controllers\PublicProductController;
use App\Domains\Order\Controllers\CheckoutController;
use App\Domains\Shipping\Controllers\PublicShippingZoneController;
use App\Domains\Coupon\Controllers\PublicCouponController;
use App\Domains\Cart\Controllers\CartController;
use App\Domains\Product\Controllers\ProductSearchController;
use App\Domains\Product\Controllers\ProductLandingController;
use App\Domains\Product\Controllers\ProductRecommendationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::get('/me', [AuthController::class, 'me']);
});

Route::get('/categories', [PublicCategoryController::class, 'index']);

Route::get('/products', [PublicProductController::class, 'index']);
Route::get('/products/{slug}', [PublicProductController::class, 'show']);

Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1');

Route::get('/shipping-zones', [PublicShippingZoneController::class, 'index']);

Route::post('/coupon/validate', [PublicCouponController::class, 'validateCoupon'])->middleware('throttle:20,1');

Route::middleware('throttle:60,1')->group(function () {

  Route::prefix('cart')->group(function () {

    Route::get('/', [CartController::class, 'view']);
    Route::post('/add', [CartController::class, 'add']);
    Route::post('/update', [CartController::class, 'update']);
    Route::post('/remove', [CartController::class, 'remove']);
    Route::delete('/clear', [CartController::class, 'clear']);
  });
  
});

Route::get('/products/search',  [ProductSearchController::class, 'search']);

Route::get('/landing/{slug}', [ProductLandingController::class, 'show']);

Route::get('/products/{id}/recommendations',  [ProductRecommendationController::class, 'show']);
