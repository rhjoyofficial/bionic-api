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
        $oldStatus = $order->order_status;

        if (!$this->isValidTransition($oldStatus, $newStatus->value)) {
            throw new Exception("Invalid status transition from {$oldStatus} to {$newStatus->value}");
        }

        try {
            return DB::transaction(function () use ($order, $newStatus, $oldStatus) {
                $order = Order::lockForUpdate()->findOrFail($order->id);
                $oldStatus = $order->order_status;

                if (!$this->isValidTransition($oldStatus, $newStatus->value)) {
                    throw new Exception("Invalid status transition from {$oldStatus} to {$newStatus->value}");
                }
                // 1. Update Status & Timestamps
                $order->order_status = $newStatus->value;

                match ($newStatus) {
                    OrderStatus::Confirmed  => $order->confirmed_at = now(),
                    OrderStatus::Processing => $order->processing_at = now(),
                    OrderStatus::Shipped    => $order->shipped_at = now(),
                    OrderStatus::Delivered  => $order->delivered_at = now(),
                    OrderStatus::Cancelled  => $order->cancelled_at = now(),
                    default => null
                };

                // 2. Inventory Logic: Handle Cancellation
                if ($newStatus === OrderStatus::Cancelled) {
                    $this->releaseStock($order);
                }

                $order->save();

                event(new OrderStatusChanged($order, $oldStatus, $newStatus->value));

                return $order;
            });
        } catch (Exception $e) {
            Log::error('Order Status Update Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Release reserved stock back to the available pool.
     */
    private function releaseStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->variant) {
                // Use atomic increments/decrements to avoid race conditions
                $item->variant->decrement('reserved_stock', $item->quantity);

                // Note: We only increment 'stock' if the item was already deducted 
                // from physical inventory (e.g., at shipping). 
                // If it was just 'reserved', we only decrease reservation.
                // Assuming reservation-only logic for pending orders:
            }
        }
    }

    private function isValidTransition(string $current, string $next): bool
    {
        $allowed = [
            'pending'    => ['confirmed', 'cancelled'],
            'confirmed'  => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'], // Added cancelled to processing
            'shipped'    => ['delivered', 'returned'],
            'delivered'  => [],
            'cancelled'  => [],
            'returned'   => [],
        ];

        return in_array($next, $allowed[$current] ?? []);
    }
}
