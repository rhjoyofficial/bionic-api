<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
  require __DIR__ . '/public.php';
  require __DIR__ . '/admin.php';

  if (app()->environment('local', 'testing')) {
    Route::get('/test-pricing/{variant}/{qty}', function (
      \App\Domains\Product\Models\ProductVariant $variant,
      $qty,
      \App\Domains\Product\Services\PricingService $service
    ) {
      return $service->calculate($variant, (int)$qty);
    });
  }
});
