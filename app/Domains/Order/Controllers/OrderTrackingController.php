<?php

namespace App\Domains\Order\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Order\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderTrackingController extends Controller
{
    public function show(Order $order)
    {
        // Guests cannot track orders (no auth = no access)
        if (!Auth::check()) {
            abort(401);
        }

        // Customers can only view their own order shipment data
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        return $order->shipment;
    }
}
