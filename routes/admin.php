<?php

use App\Domains\Category\Controllers\AdminCategoryController;
use App\Domains\Product\Controllers\AdminProductController;
use App\Domains\Product\Controllers\ProductTierPriceController;
use App\Domains\Shipping\Controllers\AdminShippingZoneController;
use App\Domains\Coupon\Controllers\AdminCouponController;
use App\Domains\Order\Controllers\AdminOrderController;
use App\Domains\Product\Controllers\ProductRelationController;
use App\Domains\Webhook\Controllers\AdminWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:Admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Admin Access']);
    });
    // Categories
    Route::get('categories', [AdminCategoryController::class, 'index'])->middleware('permission:category.view');

    Route::post('categories', [AdminCategoryController::class, 'store']);

    Route::put('categories/{category}', [AdminCategoryController::class, 'update']);

    Route::delete('categories/{category}', [AdminCategoryController::class, 'destroy']);
    // Products
    Route::get('products', [AdminProductController::class, 'index'])->middleware('permission:product.view');

    Route::post('products', [AdminProductController::class, 'store'])->middleware('permission:product.create');

    Route::put('products/{product}', [AdminProductController::class, 'update'])->middleware('permission:product.update');

    Route::delete('products/{product}', [AdminProductController::class, 'destroy'])->middleware('permission:product.delete');
    // Product Tier
    Route::post('products/{variant}/tier-prices', [ProductTierPriceController::class, 'store']);

    Route::delete('products/{variant}/tier-prices/{tierId}',  [ProductTierPriceController::class, 'destroy']);
    // Shipping Zone
    Route::apiResource('shipping-zones', AdminShippingZoneController::class);
    // Coupons
    Route::apiResource('coupons', AdminCouponController::class);
    // Order Management
    Route::get('orders', [AdminOrderController::class, 'index'])->middleware('permission:order.view');

    Route::get('orders/{order}', [AdminOrderController::class, 'show']);

    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
    // Product Relation
    Route::post('/product-relations', [ProductRelationController::class, 'store']);
    Route::delete('/product-relations', [ProductRelationController::class, 'destroy']);

    Route::get('/webhooks', [AdminWebhookController::class, 'index']);

    Route::post('/webhooks', [AdminWebhookController::class, 'store']);

    Route::delete('/webhooks/{webhook}', [AdminWebhookController::class, 'destroy']);
});


// Route::middleware(['auth:sanctum', 'permission:product.create'])
//     ->post('/admin/products', [ProductController::class, 'store']);
