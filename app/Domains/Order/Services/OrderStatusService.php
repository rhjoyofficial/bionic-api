<?php

namespace App\Domains\Order\Services;

use App\Domains\Order\Models\Order;
use App\Domains\Order\Enums\OrderStatus;
use Exception;

class OrderStatusService
{
    public function changeStatus(Order $order, OrderStatus $newStatus): Order
    {
        if (!$this->isValidTransition($order->order_status, $newStatus->value)) {
            throw new Exception('Invalid status transition');
        }

        $order->order_status = $newStatus->value;

        // Timestamp updates
        match ($newStatus) {
            OrderStatus::Confirmed => $order->confirmed_at = now(),
            OrderStatus::Shipped => $order->shipped_at = now(),
            OrderStatus::Delivered => $order->delivered_at = now(),
            default => null
        };

        $order->save();

        return $order;
    }

    private function isValidTransition(string $current, string $next): bool
    {
        $allowed = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'cancelled'],
            'processing' => ['shipped'],
            'shipped' => ['delivered', 'returned'],
            'delivered' => [],
            'cancelled' => [],
            'returned' => [],
        ];

        return in_array($next, $allowed[$current] ?? []);
    }
}
