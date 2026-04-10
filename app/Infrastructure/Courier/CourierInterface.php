<?php

namespace App\Infrastructure\Courier;

interface CourierInterface
{
    /**
     * Get the courier driver name (e.g. 'pathao', 'steadfast', 'carrybee').
     */
    public function driverName(): string;

    /**
     * Create a shipment/consignment with the courier.
     *
     * @param  array $payload Normalized shipment data:
     *   - recipient_name, recipient_phone, recipient_address, recipient_city, recipient_area
     *   - amount_to_collect (COD), item_weight, item_description, order_number
     * @return array ['success' => bool, 'consignment_id' => ?string, 'tracking_code' => ?string, 'fee' => ?float, 'raw' => array]
     */
    public function createShipment(array $payload): array;

    /**
     * Fetch current status from courier API.
     *
     * @param  string $consignmentId The courier's consignment/order ID
     * @return array ['success' => bool, 'status' => ?string, 'message' => ?string, 'raw' => array]
     */
    public function trackShipment(string $consignmentId): array;

    /**
     * Cancel a shipment (before pickup).
     *
     * @param  string $consignmentId
     * @return array ['success' => bool, 'message' => ?string]
     */
    public function cancelShipment(string $consignmentId): array;

    /**
     * Normalize a courier-specific status string to a system status.
     *
     * @return string One of: pending, picked_up, in_transit, out_for_delivery, delivered, partial_delivery, cancelled, returned, on_hold
     */
    public function normalizeStatus(string $courierStatus): string;
}
