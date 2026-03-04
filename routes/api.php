<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
  require __DIR__ . '/public.php';
  require __DIR__ . '/admin.php';

  Route::get('/test-pricing/{variant}/{qty}', function (
    \App\Domains\Product\Models\ProductVariant $variant,
    $qty
  ) {
    $service = new \App\Domains\Product\Services\PricingService();

    return $service->calculate($variant, (int)$qty);
  });
});
