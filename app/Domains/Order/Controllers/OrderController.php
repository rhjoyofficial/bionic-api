<?php

namespace App\Domains\Order\Controllers;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\User;
use Exception;

class OrderController extends Controller
{
  public function success()
  {
    return view('store.order-success');
  }

  public function failed()
  {
    return view('store.order-failed');
  }
}
