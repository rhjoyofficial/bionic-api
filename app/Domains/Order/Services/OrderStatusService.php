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
        $oldStatusStr = $order->order_status;

        if (!$this->isValidTransition($oldStatusStr, $newStatus->value)) {
            throw new Exception("Invalid status transition from {$oldStatusStr} to {$newStatus->value}");
        }

        try {
            return DB::transaction(function () use ($order, $newStatus, $oldStatusStr) {
                // Lock the order for update to prevent race conditions during status changes
                $order = Order::lockForUpdate()->findOrFail($order->id);

                // 1. Inventory Logic: Handle Transitions
                // Only fulfill stock if we are moving TO Shipped from a non-shipped state
                if ($newStatus === OrderStatus::Shipped && $oldStatusStr !== 'shipped') {
                    $this->fulfillStock($order);
                }

                // Handle Cancellation
                if ($newStatus === OrderStatus::Cancelled) {
                    $this->releaseStock($order);
                }

                // 2. Update Status & Timestamps
                $order->order_status = $newStatus->value;

                match ($newStatus) {
                    OrderStatus::Confirmed  => $order->confirmed_at = now(),
                    OrderStatus::Processing => $order->processing_at = now(),
                    OrderStatus::Shipped    => $order->shipped_at = now(),
                    OrderStatus::Delivered  => $order->delivered_at = now(),
                    OrderStatus::Cancelled  => $order->cancelled_at = now(),
                    default => null
                };

                $order->save();

                event(new OrderStatusChanged($order, $oldStatusStr, $newStatus->value));

                return $order;
            });
        } catch (Exception $e) {
            Log::error('Order Status Update Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Finalize the inventory: Move items out of 'reserved' and out of 'physical stock'.
     */
    private function fulfillStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->variant) {
                // Atomic update: Reduce physical stock and clear the reservation
                $item->variant->decrement('stock', $item->quantity);
                $item->variant->decrement('reserved_stock', $item->quantity);
            }

            // If you have Combo items, you must also deduct stock for each variant inside the combo
            if ($item->combo_id && $item->combo) {
                foreach ($item->combo->items as $comboItem) {
                    $comboItem->variant->decrement('stock', $comboItem->quantity * $item->quantity);
                    $comboItem->variant->decrement('reserved_stock', $comboItem->quantity * $item->quantity);
                }
            }
        }
    }

    /**
     * Release reserved stock back to the available pool (for cancellations).
     */
    private function releaseStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->variant) {
                // Only reduce the reservation. The physical 'stock' stays the same because it never left.
                $item->variant->decrement('reserved_stock', $item->quantity);
            }

            if ($item->combo_id && $item->combo) {
                foreach ($item->combo->items as $comboItem) {
                    $comboItem->variant->decrement('reserved_stock', $comboItem->quantity * $item->quantity);
                }
            }
        }
    }

    private function isValidTransition(string $current, string $next): bool
    {
        $allowed = [
            'pending'    => ['confirmed', 'cancelled'],
            'confirmed'  => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped'    => ['delivered', 'returned'],
            'delivered'  => [],
            'cancelled'  => [],
            'returned'   => [],
        ];

        return in_array($next, $allowed[$current] ?? []);
    }
}
