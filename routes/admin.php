<?php

use App\Domains\Admin\Controllers\AdminSettingsController;
use App\Domains\Auth\Controllers\AdminRoleController;
use App\Domains\Category\Controllers\AdminCategoryController;
use App\Domains\Courier\Controllers\AdminCourierController;
use App\Domains\Customer\Controllers\AdminCustomerController;
use App\Domains\Notification\Controllers\AdminNotificationController;
use App\Domains\Product\Controllers\AdminComboController;
use App\Domains\Product\Controllers\AdminProductController;
use App\Domains\Product\Controllers\ProductTierPriceController;
use App\Domains\Shipping\Controllers\AdminShippingZoneController;
use App\Domains\Coupon\Controllers\AdminCouponController;
use App\Domains\Order\Controllers\AdminOrderController;
use App\Domains\Order\Controllers\AdminTransactionController;
use App\Domains\Product\Controllers\ProductRelationController;
use App\Domains\Landing\Controllers\AdminLandingPageController;
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
        Route::get('products/search', [AdminProductController::class, 'searchProducts']);
        Route::get('products', [AdminProductController::class, 'index']);
        Route::get('products/{product}', [AdminProductController::class, 'show']);
    });
    Route::post('products', [AdminProductController::class, 'store'])->middleware('permission:product.create');
    Route::put('products/{product}', [AdminProductController::class, 'update'])->middleware('permission:product.update');
    Route::delete('products/{product}', [AdminProductController::class, 'destroy'])->middleware('permission:product.delete');

    // --- Combos ---
    Route::middleware('permission:product.view')->group(function () {
        Route::get('combos/search', [AdminComboController::class, 'searchCombos']);
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
    // Static routes BEFORE wildcard {order} to avoid route conflicts
    Route::get('orders/search-products', [AdminOrderController::class, 'searchProducts'])
        ->middleware('permission:order.update');
    Route::get('/shipping-zones', [AdminOrderController::class, 'shippingZones'])
        ->middleware('permission:order.view');

    Route::middleware('permission:order.view')->group(function () {
        Route::get('orders', [AdminOrderController::class, 'index']);
        Route::get('orders/{order}', [AdminOrderController::class, 'show']);
    });

    // Admin create order
    Route::post('orders', [AdminOrderController::class, 'store'])
        ->middleware('permission:order.create');

    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->middleware('permission:order.update');
    Route::post('orders/{order}/notes', [AdminOrderController::class, 'addNote'])->middleware('permission:order.update');

    // --- Order Editing (items + customer + address + zone) ---
    Route::middleware('permission:order.update')->group(function () {
        Route::get('orders/{order}/edit-data', [AdminOrderController::class, 'editData']);
        Route::post('orders/{order}/preview-edit', [AdminOrderController::class, 'previewEdit']);
        // PUT /orders/{order} applies full edit: items + customer + address + zone
        Route::put('orders/{order}', [AdminOrderController::class, 'applyEdit']);
    });

    // --- Courier & Shipments ---
    Route::group(['prefix' => 'courier', 'middleware' => 'permission:order.update'], function () {
        Route::get('/drivers', [AdminCourierController::class, 'drivers']);
        Route::post('/assign', [AdminCourierController::class, 'assign']);
        Route::post('/bulk-assign', [AdminCourierController::class, 'bulkAssign']);
        Route::get('/shipments/{order}', [AdminCourierController::class, 'orderShipments']);
        Route::post('/shipments/{shipment}/sync', [AdminCourierController::class, 'syncStatus']);
        Route::post('/shipments/{shipment}/cancel', [AdminCourierController::class, 'cancel']);
    });

    // --- Transactions & Payment Reconciliation ---
    Route::group(['prefix' => 'transactions'], function () {
        Route::middleware('permission:order.view')->group(function () {
            Route::get('/summary', [AdminTransactionController::class, 'summary']);
            Route::get('/', [AdminTransactionController::class, 'index']);
            Route::get('/reconciliation', [AdminTransactionController::class, 'reconciliation']);
            Route::get('/order/{order}', [AdminTransactionController::class, 'orderTransactions']);
        });

        Route::middleware('permission:order.update')->group(function () {
            Route::post('/order/{order}', [AdminTransactionController::class, 'store']);
            Route::patch('/order/{order}/payment-status', [AdminTransactionController::class, 'updatePaymentStatus']);
        });
    });

    // --- Customers ---
    Route::middleware('permission:customer.view')->group(function () {
        Route::get('customers', [AdminCustomerController::class, 'index']);
        Route::get('customers/{user}', [AdminCustomerController::class, 'show']);
    });
    Route::patch('customers/{user}/toggle-active', [AdminCustomerController::class, 'toggleActive'])
        ->middleware('permission:customer.deactivate');

    // --- Notifications ---
    Route::group(['prefix' => 'notifications'], function () {
        Route::middleware('permission:notification.view')->group(function () {
            Route::get('/stats', [AdminNotificationController::class, 'stats']);
            Route::get('/', [AdminNotificationController::class, 'index']);
            Route::get('/failed-jobs', [AdminNotificationController::class, 'failedJobs']);
        });

        Route::middleware('permission:notification.send')
            ->post('/send', [AdminNotificationController::class, 'send']);

        Route::middleware('permission:notification.manage')->group(function () {
            Route::post('/failed-jobs/{uuid}/retry', [AdminNotificationController::class, 'retryJob']);
            Route::post('/failed-jobs/retry-all', [AdminNotificationController::class, 'retryAllFailed']);
            Route::delete('/failed-jobs/{uuid}', [AdminNotificationController::class, 'deleteFailedJob']);
        });
    });

    // --- System / Webhooks ---
    Route::group(['middleware' => 'permission:system.webhooks'], function () {
        Route::get('/webhooks', [AdminWebhookController::class, 'index']);
        Route::post('/webhooks', [AdminWebhookController::class, 'store']);
        Route::delete('/webhooks/{webhook}', [AdminWebhookController::class, 'destroy']);
    });

    // --- Access Control (Roles & Permissions) ---
    Route::group(['prefix' => 'access-control', 'middleware' => 'permission:role.manage'], function () {
        // Role list + matrix
        Route::get('/roles',  [AdminRoleController::class, 'index']);
        Route::get('/matrix', [AdminRoleController::class, 'matrix']);

        // Role CRUD
        Route::post('/roles',        [AdminRoleController::class, 'store']);
        Route::put('/roles/{role}',  [AdminRoleController::class, 'update']);
        Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy']);

        // Sync permissions for a single role (matrix save)
        Route::put('/roles/{role}/permissions', [AdminRoleController::class, 'syncPermissions']);

        // Role → Users
        Route::get('/roles/{role}/users', [AdminRoleController::class, 'users']);

        // Admin staff list + role assignment
        Route::get('/admin-users',            [AdminRoleController::class, 'adminUsers']);
        Route::patch('/admin-users/{user}/role', [AdminRoleController::class, 'assignRole']);
    });

    // --- Landing Pages ---
    Route::group(['prefix' => 'landing-pages'], function () {
        Route::middleware('permission:product.view')->group(function () {
            Route::get('/', [AdminLandingPageController::class, 'index']);
            Route::get('/{landingPage}', [AdminLandingPageController::class, 'show']);
        });

        Route::post('/', [AdminLandingPageController::class, 'store'])->middleware('permission:product.create');
        Route::put('/{landingPage}', [AdminLandingPageController::class, 'update'])->middleware('permission:product.update');
        Route::patch('/{landingPage}/toggle-active', [AdminLandingPageController::class, 'toggleActive'])->middleware('permission:product.update');
        Route::delete('/{landingPage}', [AdminLandingPageController::class, 'destroy'])->middleware('permission:product.delete');
    });

    // --- Settings & System Health ---
    Route::group(['prefix' => 'settings', 'middleware' => 'permission:system.settings'], function () {
        Route::get('/',         [AdminSettingsController::class, 'index']);
        Route::put('/',         [AdminSettingsController::class, 'update']);
        Route::get('/health',   [AdminSettingsController::class, 'health']);
        Route::post('/clear-cache',          [AdminSettingsController::class, 'clearCache']);
        Route::post('/toggle-maintenance',   [AdminSettingsController::class, 'toggleMaintenance']);
        Route::post('/optimize',             [AdminSettingsController::class, 'optimizeApp']);
    });
});
