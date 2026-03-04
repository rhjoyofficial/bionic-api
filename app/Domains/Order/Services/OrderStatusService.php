<?php

namespace App\Domains\Order\Services;

use App\Domains\Order\Models\Order;
use App\Domains\Order\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderStatusService
{
    public function changeStatus(Order $order, OrderStatus $newStatus): Order
    {
        // 1. Capture old status for the event
        $oldStatus = $order->order_status;

        // 2. Validate Transition
        if (!$this->isValidTransition($oldStatus, $newStatus->value)) {
            throw new Exception("Invalid status transition from {$oldStatus} to {$newStatus->value}");
        }

        try {
            return DB::transaction(function () use ($order, $newStatus, $oldStatus) {
                // 3. Update Status
                $order->order_status = $newStatus->value;

                // 4. Timestamp updates
                match ($newStatus) {
                    OrderStatus::Confirmed => $order->confirmed_at = now(),
                    OrderStatus::Shipped   => $order->shipped_at = now(),
                    OrderStatus::Delivered => $order->delivered_at = now(),
                    default => null
                };

                $order->save();

                // 5. Trigger the Event
                event(new OrderStatusChanged($order, $oldStatus, $newStatus->value));

                return $order;
            });
        } catch (Exception $e) {
            Log::error('Order Status Update Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function isValidTransition(string $current, string $next): bool
    {
        $allowed = [
            'pending'    => ['confirmed', 'cancelled'],
            'confirmed'  => ['processing', 'cancelled'],
            'processing' => ['shipped'],
            'shipped'    => ['delivered', 'returned'],
            'delivered'  => [],
            'cancelled'  => [],
            'returned'   => [],
        ];

        return in_array($next, $allowed[$current] ?? []);
    }
}
