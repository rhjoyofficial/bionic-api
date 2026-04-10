<?php

namespace App\Infrastructure\Courier\Drivers;

use App\Infrastructure\Courier\CourierInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteadfastCourier implements CourierInterface
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('courier.steadfast.base_url'), '/');
    }

    public function driverName(): string
    {
        return 'steadfast';
    }

    public function createShipment(array $payload): array
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->timeout(30)
                ->post("{$this->baseUrl}/create_order", [
                    'invoice'        => $payload['order_number'] ?? '',
                    'recipient_name' => $payload['recipient_name'],
                    'recipient_phone'=> $payload['recipient_phone'],
                    'recipient_address' => $payload['recipient_address'],
                    'cod_amount'     => $payload['amount_to_collect'] ?? 0,
                    'note'           => $payload['item_description'] ?? '',
                ]);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? 0) == 200) {
                $consignment = $data['consignment'] ?? [];
                return [
                    'success'        => true,
                    'consignment_id' => (string) ($consignment['consignment_id'] ?? ''),
                    'tracking_code'  => (string) ($consignment['tracking_code'] ?? $consignment['consignment_id'] ?? ''),
                    'fee'            => null, // Steadfast returns fee on tracking
                    'raw'            => $data,
                ];
            }

            Log::warning('Steadfast createShipment failed', ['response' => $data]);
            return [
                'success'        => false,
                'consignment_id' => null,
                'tracking_code'  => null,
                'fee'            => null,
                'raw'            => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('Steadfast createShipment exception: ' . $e->getMessage());
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
                ->get("{$this->baseUrl}/status_by_cid/{$consignmentId}");

            $data = $response->json();

            if ($response->successful()) {
                $delivery = $data['delivery_status'] ?? 'unknown';
                return [
                    'success' => true,
                    'status'  => $this->normalizeStatus($delivery),
                    'message' => $delivery,
                    'raw'     => $data,
                ];
            }

            return ['success' => false, 'status' => null, 'message' => 'Tracking failed', 'raw' => $data];
        } catch (\Throwable $e) {
            Log::error('Steadfast trackShipment exception: ' . $e->getMessage());
            return ['success' => false, 'status' => null, 'message' => $e->getMessage(), 'raw' => []];
        }
    }

    public function cancelShipment(string $consignmentId): array
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->timeout(15)
                ->post("{$this->baseUrl}/cancel_order", [
                    'consignment_id' => $consignmentId,
                ]);

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Shipment cancelled' : 'Cancel failed',
            ];
        } catch (\Throwable $e) {
            Log::error('Steadfast cancelShipment exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function normalizeStatus(string $courierStatus): string
    {
        $status = strtolower(trim($courierStatus));

        return match (true) {
            in_array($status, ['pending', 'in_review'])                 => 'pending',
            in_array($status, ['picked', 'picked_up'])                  => 'picked_up',
            in_array($status, ['in_transit', 'on_the_way'])             => 'in_transit',
            in_array($status, ['out_for_delivery'])                     => 'out_for_delivery',
            in_array($status, ['delivered'])                            => 'delivered',
            in_array($status, ['partial_delivered', 'partial_delivery'])=> 'partial_delivery',
            in_array($status, ['cancelled', 'canceled'])               => 'cancelled',
            in_array($status, ['returned', 'return'])                   => 'returned',
            in_array($status, ['on_hold', 'hold'])                      => 'on_hold',
            default                                                     => 'pending',
        };
    }

    private function authHeaders(): array
    {
        return [
            'Api-Key'    => config('courier.steadfast.api_key'),
            'Secret-Key' => config('courier.steadfast.secret_key'),
            'Content-Type' => 'application/json',
        ];
    }
}
