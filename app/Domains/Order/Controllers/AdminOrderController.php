<?php

namespace App\Domains\Order\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderStatusService;
use App\Domains\Order\Requests\UpdateOrderStatusRequest;
use App\Domains\Order\Enums\OrderStatus;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminOrderController extends Controller
{
    public function index()
    {
        try {
            $orders = Order::latest()->paginate(10);

            return ApiResponse::paginated($orders);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve orders');
        }
    }

    public function show(Order $order)
    {
        try {
            return ApiResponse::success(
                $order->load(['items', 'coupon', 'shippingZone']),
                'Order details retrieved'
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve order details');
        }
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

            return ApiResponse::success(
                $updated,
                'Order status updated to ' . $request->status
            );
        } catch (Exception $e) {
            // Using 422 for transition errors, 500 for system errors
            $code = $e->getMessage() === 'Invalid status transition' ? 422 : 500;
            return $this->handleError($e, $e->getMessage(), $code);
        }
    }

    /**
     * Unified error handler using the ApiResponse helper
     */
    private function handleError(Exception $e, string $msg, int $code = 500)
    {
        Log::error($msg . ': ' . $e->getMessage());

        return ApiResponse::error(
            $msg,
            config('app.debug') ? $e->getMessage() : null,
            $code
        );
    }
}
