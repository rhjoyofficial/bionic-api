<?php

namespace App\Domains\Order\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderStatusService;
use App\Domains\Order\Requests\UpdateOrderStatusRequest;
use App\Domains\Order\Enums\OrderStatus;

class AdminOrderController extends Controller
{
    public function index()
    {
        return Order::latest()->paginate(10);
    }

    public function show(Order $order)
    {
        return $order->load('items');
    }

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
        OrderStatusService $service
    ) {
        try {
            $updated = $service->changeStatus(
                $order,
                OrderStatus::from($request->status)
            );

            return response()->json([
                'success' => true,
                'data' => $updated
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
