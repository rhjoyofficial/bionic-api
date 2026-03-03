<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Auth\Controllers\AuthController;
use App\Domains\Category\Controllers\PublicCategoryController;
use App\Domains\Product\Controllers\PublicProductController;
use App\Domains\Order\Controllers\CheckoutController;
use App\Domains\Shipping\Controllers\PublicShippingZoneController;
use App\Domains\Coupon\Controllers\PublicCouponController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::get('/me', [AuthController::class, 'me']);
});

Route::get('/categories', [PublicCategoryController::class, 'index']);

Route::get('/products', [PublicProductController::class, 'index']);
Route::get('/products/{slug}', [PublicProductController::class, 'show']);

Route::post('/checkout', [CheckoutController::class, 'store']);

Route::get('/shipping-zones', [PublicShippingZoneController::class, 'index']);

Route::post('/coupon/validate', [PublicCouponController::class, 'validateCoupon']);
