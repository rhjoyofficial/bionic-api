<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
  require __DIR__ . '/public.php';
  require __DIR__ . '/admin.php';
});
