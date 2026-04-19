<?php

namespace App\Infrastructure\Courier\Drivers;

use App\Infrastructure\Courier\CourierInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RedxCourier implements CourierInterface
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('courier.redx.base_url', 'https://openapi.redx.com.bd/v1.0.0-beta'), '/');
    }

    public function driverName(): string
    {
        return 'redx';
    }

    public function createShipment(array $payload): array
    {
        try {
            $response = Http::withToken($this->getApiKey(), 'Bearer')
                ->withHeaders(['Content-Type' => 'application/json'])
                ->timeout(30)
                ->post("{$this->baseUrl}/parcel", [
                    'name'          => $payload['recipient_name'],
                    'phone'         => $payload['recipient_phone'],
                    'address'       => $payload['recipient_address'],
                    'merchant_invoice_id' => $payload['order_number'] ?? '',
                    'cash_collection_amount' => (int) round($payload['amount_to_collect'] ?? 0),
                    'parcel_weight' => (float) ($payload['item_weight'] ?? 0.5),
                    'instruction'   => $payload['special_instruction'] ?? '',
                    'value'         => 0,
                ]);

            $data = $response->json();

            if ($response->successful() && !empty($data['tracking_id'])) {
                return [
                    'success'        => true,
                    'consignment_id' => (string) $data['tracking_id'],
                    'tracking_code'  => (string) $data['tracking_id'],
                    'fee'            => null,
                    'raw'            => $data,
                ];
            }

            Log::warning('RedX createShipment failed', ['response' => $data]);
            return [
                'success'        => false,
                'consignment_id' => null,
                'tracking_code'  => null,
                'fee'            => null,
                'raw'            => $data,
                'message'        => $data['message'] ?? 'RedX API error',
            ];
        } catch (\Throwable $e) {
            Log::error('RedX createShipment exception: ' . $e->getMessage());
            return [
                'success'        => false,
                'consignment_id' => null,
                'tracking_code'  => null,
                'fee'            => null,
                'raw'            => ['error' => $e->getMessage()],
                'message'        => $e->getMessage(),
            ];
        }
    }

    public function trackShipment(string $consignmentId): array
    {
        try {
            $response = Http::withToken($this->getApiKey(), 'Bearer')
                ->timeout(15)
                ->get("{$this->baseUrl}/parcel/info/{$consignmentId}");

            $data = $response->json();

            if ($response->successful()) {
                $status = $data['parcel']['status'] ?? 'unknown';
                return [
                    'success' => true,
                    'status'  => $this->normalizeStatus($status),
                    'message' => $status,
                    'raw'     => $data,
                ];
            }

            return ['success' => false, 'status' => null, 'message' => 'Tracking failed', 'raw' => $data];
        } catch (\Throwable $e) {
            Log::error('RedX trackShipment exception: ' . $e->getMessage());
            return ['success' => false, 'status' => null, 'message' => $e->getMessage(), 'raw' => []];
        }
    }

    public function cancelShipment(string $consignmentId): array
    {
        try {
            $response = Http::withToken($this->getApiKey(), 'Bearer')
                ->timeout(15)
                ->post("{$this->baseUrl}/parcel/cancel/{$consignmentId}");

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Shipment cancelled' : ($response->json('message') ?? 'Cancel failed'),
            ];
        } catch (\Throwable $e) {
            Log::error('RedX cancelShipment exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function normalizeStatus(string $courierStatus): string
    {
        $status = strtolower(trim($courierStatus));

        return match (true) {
            in_array($status, ['pending', 'pickup_requested', 'assigned_for_pickup']) => 'pending',
            in_array($status, ['picked', 'picked_up', 'pickup_done'])                 => 'picked_up',
            in_array($status, ['in_transit', 'in_sorting_hub'])                       => 'in_transit',
            in_array($status, ['out_for_delivery'])                                   => 'out_for_delivery',
            in_array($status, ['delivered', 'delivery_done'])                         => 'delivered',
            in_array($status, ['partial_delivered', 'partial_delivery'])              => 'partial_delivery',
            in_array($status, ['cancelled', 'canceled'])                              => 'cancelled',
            in_array($status, ['returned', 'return'])                                 => 'returned',
            in_array($status, ['on_hold', 'hold'])                                    => 'on_hold',
            default                                                                    => 'pending',
        };
    }

    private function getApiKey(): string
    {
        return config('courier.redx.api_key', '');
    }
}
