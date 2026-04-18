<?php

namespace App\Domains\Courier\Services;

use App\Domains\Courier\Models\CourierShipment;
use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderStatusService;
use App\Infrastructure\Courier\CourierService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShipmentService
{
    public function __construct(
        private readonly CourierService $courierService,
        private readonly OrderStatusService $orderStatusService,
    ) {}

    /**
     * Create a shipment for an order using the specified courier.
     */
    public function createShipment(Order $order, string $courierName, ?int $createdBy = null): CourierShipment
    {
        $order->load('shippingAddress');

        $address = $order->shippingAddress;

        if (!$address) {
            throw new Exception('Order has no shipping address.');
        }

        $driver  = $this->courierService->driver($courierName);

        $payload = [
            'order_number'      => $order->order_number,
            'recipient_name'    => $address->customer_name ?? $order->customer_name,
            'recipient_phone'   => $address->customer_phone ?? $order->customer_phone,
            'recipient_address' => $address->address_line,
            'recipient_city'    => $address->city ?? '',
            'recipient_area'    => $address->area ?? '',
            'amount_to_collect' => $order->payment_method === 'cod' ? $order->grand_total : 0,
            'item_weight'       => 0.5,
            'item_description'  => "Order {$order->order_number}",
        ];

        $result = $driver->createShipment($payload);

        $shipment = CourierShipment::create([
            'order_id'               => $order->id,
            'courier'                => $courierName,
            'tracking_code'          => $result['tracking_code'],
            'consignment_id'         => $result['consignment_id'],
            'status'                 => $result['success'] ? 'pending' : 'failed',
            'delivery_fee'           => $result['fee'],
            'cod_amount'             => $payload['amount_to_collect'],
            'courier_status_message' => $result['success'] ? 'Shipment created' : 'API call failed',
            'courier_response'       => $result['raw'] ?? null,
            'created_by'             => $createdBy,
        ]);

        if ($result['success'] && in_array($order->order_status, ['confirmed', 'processing'])) {
            try {
                if ($order->order_status === 'confirmed') {
                    $this->orderStatusService->changeStatus($order, OrderStatus::Processing);
                    $order->refresh();
                }
                $this->orderStatusService->changeStatus($order, OrderStatus::Shipped);
            } catch (\Exception $e) {
                Log::warning("ShipmentService: could not auto-ship order #{$order->id}: {$e->getMessage()}");
            }
        }

        return $shipment;
    }

    /**
     * Bulk assign courier to multiple orders.
     *
     * @return array ['created' => CourierShipment[], 'errors' => array]
     */
    public function bulkAssign(array $orderIds, string $courierName, ?int $createdBy = null): array
    {
        $created = [];
        $errors  = [];

        foreach ($orderIds as $orderId) {
            try {
                $order = Order::findOrFail($orderId);

                // Skip if order already has an active (non-cancelled) shipment
                $existing = CourierShipment::where('order_id', $orderId)
                    ->whereNotIn('status', ['cancelled', 'returned'])
                    ->first();

                if (in_array($order->order_status, ['shipped', 'delivered', 'cancelled', 'returned'])) {
                    $errors[] = [
                        'order_id' => $orderId,
                        'order_number' => $order->order_number,
                        'message' => 'Cannot assign courier to order with status: ' . $order->order_status,
                    ];
                    continue;
                }

                if ($existing) {
                    $errors[] = [
                        'order_id' => $orderId,
                        'order_number' => $order->order_number,
                        'message' => "Already has active shipment ({$existing->courier} — {$existing->tracking_code})",
                    ];
                    continue;
                }

                $shipment  = $this->createShipment($order, $courierName, $createdBy);
                $created[] = $shipment;
            } catch (\Throwable $e) {
                $errors[] = [
                    'order_id' => $orderId,
                    'order_number' => $order->order_number ?? "#{$orderId}",
                    'message' => $e->getMessage(),
                ];
                Log::error("Bulk courier assign failed for order {$orderId}: " . $e->getMessage());
            }
        }

        return ['created' => $created, 'errors' => $errors];
    }

    /**
     * Sync shipment status from courier API.
     */
    public function syncStatus(CourierShipment $shipment): CourierShipment
    {
        if ($shipment->isTerminal()) {
            return $shipment; // No need to re-check terminal states
        }

        if (!$shipment->consignment_id) {
            return $shipment;
        }

        $driver = $this->courierService->driver($shipment->courier);
        $result = $driver->trackShipment($shipment->consignment_id);

        if ($result['success'] && $result['status']) {
            $oldStatus = $shipment->status;
            $newStatus = $result['status'];

            $shipment->update([
                'status'                 => $newStatus,
                'courier_status_message' => $result['message'] ?? $newStatus,
                'status_synced_at'       => now(),
                'picked_at'              => ($newStatus === 'picked_up' && !$shipment->picked_at) ? now() : $shipment->picked_at,
                'delivered_at'           => ($newStatus === 'delivered' && !$shipment->delivered_at) ? now() : $shipment->delivered_at,
            ]);

            // Auto-update order status if shipment is delivered.
            // Route through OrderStatusService to ensure fulfillStock() runs,
            // transition validation is enforced, and OrderStatusChanged event fires.
            if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
                $order = $shipment->order;
                if ($order && in_array($order->order_status, ['shipped', 'processing'])) {
                    try {
                        // If order is still processing, move to shipped first (required by state machine)
                        if ($order->order_status === 'processing') {
                            $this->orderStatusService->changeStatus($order, OrderStatus::Shipped);
                            $order->refresh();
                        }
                        $this->orderStatusService->changeStatus($order, OrderStatus::Delivered);
                    } catch (Exception $e) {
                        Log::warning("ShipmentService: could not auto-deliver order #{$order->id}: {$e->getMessage()}");
                    }
                }
            }
        }

        return $shipment->fresh();
    }

    /**
     * Cancel a shipment via courier API.
     */
    public function cancelShipment(CourierShipment $shipment): CourierShipment
    {
        if (!$shipment->isCancellable()) {
            throw new Exception("Shipment #{$shipment->id} cannot be cancelled (status: {$shipment->status}).");
        }

        if ($shipment->consignment_id) {
            $driver = $this->courierService->driver($shipment->courier);
            $result = $driver->cancelShipment($shipment->consignment_id);

            if (!$result['success']) {
                throw new Exception("Courier cancel failed: " . ($result['message'] ?? 'Unknown error'));
            }
        }

        $shipment->update([
            'status'                 => 'cancelled',
            'courier_status_message' => 'Cancelled by admin',
            'status_synced_at'       => now(),
        ]);

        return $shipment->fresh();
    }
}
