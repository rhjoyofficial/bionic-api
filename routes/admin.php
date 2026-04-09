<?php

use App\Domains\Category\Controllers\AdminCategoryController;
use App\Domains\Customer\Controllers\AdminCustomerController;
use App\Domains\Product\Controllers\AdminComboController;
use App\Domains\Product\Controllers\AdminProductController;
use App\Domains\Product\Controllers\ProductTierPriceController;
use App\Domains\Shipping\Controllers\AdminShippingZoneController;
use App\Domains\Coupon\Controllers\AdminCouponController;
use App\Domains\Order\Controllers\AdminOrderController;
use App\Domains\Product\Controllers\ProductRelationController;
use App\Domains\Webhook\Controllers\AdminWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {

    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Admin Access']);
    });

    // --- Categories ---
    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', [AdminCategoryController::class, 'index'])->middleware('permission:category.view');
        Route::post('/', [AdminCategoryController::class, 'store'])->middleware('permission:category.create');
        Route::put('/{category}', [AdminCategoryController::class, 'update'])->middleware('permission:category.update');
        Route::delete('/{category}', [AdminCategoryController::class, 'destroy'])->middleware('permission:category.delete');
    });

    // --- Products & Variants ---
    Route::middleware('permission:product.view')->group(function () {
        Route::get('products', [AdminProductController::class, 'index']);
        Route::get('products/{product}', [AdminProductController::class, 'show']);
    });
    Route::post('products', [AdminProductController::class, 'store'])->middleware('permission:product.create');
    Route::put('products/{product}', [AdminProductController::class, 'update'])->middleware('permission:product.update');
    Route::delete('products/{product}', [AdminProductController::class, 'destroy'])->middleware('permission:product.delete');

    // --- Combos ---
    Route::middleware('permission:product.view')->group(function () {
        Route::get('combos', [AdminComboController::class, 'index']);
        Route::get('combos/{combo}', [AdminComboController::class, 'show']);
    });
    Route::post('combos', [AdminComboController::class, 'store'])->middleware('permission:product.create');
    Route::put('combos/{combo}', [AdminComboController::class, 'update'])->middleware('permission:product.update');
    Route::patch('combos/{combo}/toggle-active', [AdminComboController::class, 'toggleActive'])->middleware('permission:product.update');
    Route::delete('combos/{combo}', [AdminComboController::class, 'destroy'])->middleware('permission:product.delete');

    // --- Product Tier Prices ---
    Route::group(['middleware' => 'permission:product.update'], function () {
        Route::post('products/{variant}/tier-prices', [ProductTierPriceController::class, 'store']);
        Route::delete('products/{variant}/tier-prices/{tierId}', [ProductTierPriceController::class, 'destroy']);

        // Product Relations 
        Route::post('/product-relations', [ProductRelationController::class, 'store']);
        Route::delete('/product-relations', [ProductRelationController::class, 'destroy']);
    });

    // --- Shipping Zones ---
    Route::group(['prefix' => 'shipping-zones'], function () {
        Route::middleware('permission:shipping.view')->group(function () {
            Route::get('/', [AdminShippingZoneController::class, 'index']);
            Route::get('/{shipping_zone}', [AdminShippingZoneController::class, 'show']);
        });

        Route::post('/', [AdminShippingZoneController::class, 'store'])->middleware('permission:shipping.create');
        Route::patch('/reorder', [AdminShippingZoneController::class, 'reorder'])->middleware('permission:shipping.update');
        Route::put('/{shipping_zone}', [AdminShippingZoneController::class, 'update'])->middleware('permission:shipping.update');
        Route::delete('/{shipping_zone}', [AdminShippingZoneController::class, 'destroy'])->middleware('permission:shipping.delete');
    });

    // --- Coupons ---
    Route::group(['prefix' => 'coupons'], function () {
        Route::middleware('permission:coupon.view')->group(function () {
            Route::get('/', [AdminCouponController::class, 'index']);
            Route::get('/stats', [AdminCouponController::class, 'stats']);
            Route::get('/{coupon}', [AdminCouponController::class, 'show']);
        });

        Route::post('/', [AdminCouponController::class, 'store'])->middleware('permission:coupon.create');
        Route::post('/bulk-generate', [AdminCouponController::class, 'bulkGenerate'])->middleware('permission:coupon.create');
        Route::put('/{coupon}', [AdminCouponController::class, 'update'])->middleware('permission:coupon.update');
        Route::delete('/{coupon}', [AdminCouponController::class, 'destroy'])->middleware('permission:coupon.delete');
    });

    // --- Order Management ---
    Route::middleware('permission:order.view')->group(function () {
        Route::get('orders', [AdminOrderController::class, 'index']);
        Route::get('orders/{order}', [AdminOrderController::class, 'show']);
    });
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->middleware('permission:order.update');
    Route::post('orders/{order}/notes', [AdminOrderController::class, 'addNote'])->middleware('permission:order.update');

    // --- Customers ---
    Route::middleware('permission:customer.view')->group(function () {
        Route::get('customers', [AdminCustomerController::class, 'index']);
        Route::get('customers/{user}', [AdminCustomerController::class, 'show']);
    });
    Route::patch('customers/{user}/toggle-active', [AdminCustomerController::class, 'toggleActive'])
        ->middleware('permission:customer.deactivate');

    // --- System / Webhooks ---
    Route::group(['middleware' => 'permission:system.webhooks'], function () {
        Route::get('/webhooks', [AdminWebhookController::class, 'index']);
        Route::post('/webhooks', [AdminWebhookController::class, 'store']);
        Route::delete('/webhooks/{webhook}', [AdminWebhookController::class, 'destroy']);
    });
});
