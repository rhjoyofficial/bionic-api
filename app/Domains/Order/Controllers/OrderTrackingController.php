<?php

namespace App\Domains\Order\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Order\Models\Order;

class OrderTrackingController extends Controller
{
  public function show(Order $order)
  {
    return $order->shipment;
  }
}
