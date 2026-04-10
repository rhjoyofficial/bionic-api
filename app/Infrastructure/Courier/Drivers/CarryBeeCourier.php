<?php

namespace App\Infrastructure\Courier\Drivers;

use App\Infrastructure\Courier\CourierInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CarryBeeCourier implements CourierInterface
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('courier.carrybee.base_url'), '/');
    }

    public function driverName(): string
    {
        return 'carrybee';
    }

    public function createShipment(array $payload): array
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->timeout(30)
                ->post("{$this->baseUrl}/orders", [
                    'merchant_order_id' => $payload['order_number'] ?? '',
                    'recipient_name'    => $payload['recipient_name'],
                    'recipient_phone'   => $payload['recipient_phone'],
                    'recipient_address' => $payload['recipient_address'],
                    'recipient_city'    => $payload['recipient_city'] ?? null,
                    'recipient_area'    => $payload['recipient_area'] ?? null,
                    'cod_amount'        => $payload['amount_to_collect'] ?? 0,
                    'weight'            => $payload['item_weight'] ?? 0.5,
                    'note'              => $payload['item_description'] ?? '',
                ]);

            $data = $response->json();

            if ($response->successful() && ($data['success'] ?? false)) {
                $order = $data['data'] ?? [];
                return [
                    'success'        => true,
                    'consignment_id' => (string) ($order['order_id'] ?? ''),
                    'tracking_code'  => (string) ($order['tracking_id'] ?? $order['order_id'] ?? ''),
                    'fee'            => (float) ($order['delivery_charge'] ?? 0),
                    'raw'            => $data,
                ];
            }

            Log::warning('CarryBee createShipment failed', ['response' => $data]);
            return [
                'success'        => false,
                'consignment_id' => null,
                'tracking_code'  => null,
                'fee'            => null,
                'raw'            => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('CarryBee createShipment exception: ' . $e->getMessage());
            return [
                'success'        => false,
                'consignment_id' => null,
                'tracking_code'  => null,
                'fee'            => null,
                'raw'            => ['error' => $e->getMessage()],
            ];
        }
    }

    public function trackShipment(string $consignmentId): array
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->timeout(15)
                ->get("{$this->baseUrl}/orders/{$consignmentId}/track");

            $data = $response->json();

            if ($response->successful()) {
                $status = $data['data']['status'] ?? 'unknown';
                return [
                    'success' => true,
                    'status'  => $this->normalizeStatus($status),
                    'message' => $data['data']['status_message'] ?? $status,
                    'raw'     => $data,
                ];
            }

            return ['success' => false, 'status' => null, 'message' => 'Tracking failed', 'raw' => $data];
        } catch (\Throwable $e) {
            Log::error('CarryBee trackShipment exception: ' . $e->getMessage());
            return ['success' => false, 'status' => null, 'message' => $e->getMessage(), 'raw' => []];
        }
    }

    public function cancelShipment(string $consignmentId): array
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->timeout(15)
                ->post("{$this->baseUrl}/orders/{$consignmentId}/cancel");

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Shipment cancelled' : 'Cancel failed',
            ];
        } catch (\Throwable $e) {
            Log::error('CarryBee cancelShipment exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function normalizeStatus(string $courierStatus): string
    {
        $status = strtolower(trim($courierStatus));

        return match (true) {
            in_array($status, ['pending', 'processing'])               => 'pending',
            in_array($status, ['picked_up', 'collected'])              => 'picked_up',
            in_array($status, ['in_transit', 'at_hub', 'transferring'])=> 'in_transit',
            in_array($status, ['out_for_delivery'])                    => 'out_for_delivery',
            in_array($status, ['delivered'])                           => 'delivered',
            in_array($status, ['partial_delivery'])                    => 'partial_delivery',
            in_array($status, ['cancelled', 'canceled'])              => 'cancelled',
            in_array($status, ['returned', 'return'])                  => 'returned',
            in_array($status, ['on_hold', 'hold'])                     => 'on_hold',
            default                                                    => 'pending',
        };
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('courier.carrybee.api_key'),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }
}
