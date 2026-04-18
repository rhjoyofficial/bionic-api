<?php

namespace App\Infrastructure\Courier\Drivers;

use App\Infrastructure\Courier\CourierInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PathaoCourier implements CourierInterface
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('courier.pathao.base_url'), '/');
    }

    public function driverName(): string
    {
        return 'pathao';
    }

    public function createShipment(array $payload): array
    {
        try {
            $token = $this->getAccessToken();

            $location = $this->resolveLocationIds($token, $payload['recipient_city'] ?? '', $payload['recipient_area'] ?? '');

            $response = Http::withToken($token)
                ->timeout(30)
                ->post("{$this->baseUrl}/aladdin/api/v1/orders", [
                    'store_id'            => config('courier.pathao.store_id'),
                    'merchant_order_id'   => $payload['order_number'] ?? null,
                    'recipient_name'      => $payload['recipient_name'],
                    'recipient_phone'     => $payload['recipient_phone'],
                    'recipient_address'   => $payload['recipient_address'],
                    'recipient_city'      => $location['city_id'],
                    'recipient_zone'      => $location['zone_id'],
                    'delivery_type'       => 48,  // Normal delivery
                    'item_type'           => 2,   // Parcel
                    'item_quantity'       => 1,
                    'item_weight'         => $payload['item_weight'] ?? 0.5,
                    'item_description'    => $payload['item_description'] ?? 'E-commerce order',
                    'amount_to_collect'   => (int) round($payload['amount_to_collect'] ?? 0),
                    'special_instruction' => $payload['special_instruction'] ?? '',
                ]);

            $data = $response->json();

            if ($response->successful() && !empty($data['data']['consignment_id'])) {
                return [
                    'success'        => true,
                    'consignment_id' => (string) $data['data']['consignment_id'],
                    'tracking_code'  => (string) ($data['data']['tracking_id'] ?? $data['data']['consignment_id']),
                    'fee'            => (float) ($data['data']['delivery_fee'] ?? 0),
                    'raw'            => $data,
                ];
            }

            Log::warning('Pathao createShipment failed', ['response' => $data]);
            return [
                'success'        => false,
                'consignment_id' => null,
                'tracking_code'  => null,
                'fee'            => null,
                'raw'            => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('Pathao createShipment exception: ' . $e->getMessage());
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
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->timeout(15)
                ->get("{$this->baseUrl}/aladdin/api/v1/orders/{$consignmentId}");

            $data = $response->json();

            if ($response->successful()) {
                $courierStatus = $data['data']['order_status'] ?? 'Unknown';
                return [
                    'success' => true,
                    'status'  => $this->normalizeStatus($courierStatus),
                    'message' => $data['data']['order_status_slug'] ?? $courierStatus,
                    'raw'     => $data,
                ];
            }

            return ['success' => false, 'status' => null, 'message' => 'Tracking failed', 'raw' => $data];
        } catch (\Throwable $e) {
            Log::error('Pathao trackShipment exception: ' . $e->getMessage());
            return ['success' => false, 'status' => null, 'message' => $e->getMessage(), 'raw' => []];
        }
    }

    public function cancelShipment(string $consignmentId): array
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->timeout(15)
                ->post("{$this->baseUrl}/aladdin/api/v1/orders/{$consignmentId}/cancel");

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Shipment cancelled' : 'Cancel failed',
            ];
        } catch (\Throwable $e) {
            Log::error('Pathao cancelShipment exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function normalizeStatus(string $courierStatus): string
    {
        $status = strtolower(trim($courierStatus));

        return match (true) {
            in_array($status, ['pending', 'pickup_requested', 'assigned_for_pickup']) => 'pending',
            in_array($status, ['picked', 'picked_up', 'pickup_completed'])            => 'picked_up',
            in_array($status, ['in_transit', 'at_sorting_hub', 'transferred'])         => 'in_transit',
            in_array($status, ['out_for_delivery', 'dispatched'])                      => 'out_for_delivery',
            in_array($status, ['delivered', 'delivery_completed'])                     => 'delivered',
            in_array($status, ['partial_delivered', 'partial_delivery'])                => 'partial_delivery',
            in_array($status, ['cancelled', 'canceled'])                               => 'cancelled',
            in_array($status, ['returned', 'return', 'return_completed'])              => 'returned',
            in_array($status, ['on_hold', 'hold'])                                     => 'on_hold',
            default                                                                    => 'pending',
        };
    }

    /**
     * Get OAuth2 access token from Pathao (cached for 50 minutes).
     */
    private function getAccessToken(): string
    {
        return Cache::remember('pathao_access_token', 3000, function () {
            $response = Http::post("{$this->baseUrl}/aladdin/api/v1/issue-token", [
                'client_id'     => config('courier.pathao.client_id'),
                'client_secret' => config('courier.pathao.client_secret'),
                'username'      => config('courier.pathao.username'),
                'password'      => config('courier.pathao.password'),
                'grant_type'    => 'password',
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException('Pathao authentication failed: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Dynamically map text city/zone to Pathao IDs, falling back to defaults if not found.
     */
    private function resolveLocationIds(string $token, string $cityStr, string $zoneStr): array
    {
        $defaultCityId = 1; // Dhaka
        $defaultZoneId = 1167; // Mirpur Default

        if (empty(trim($cityStr))) {
            return ['city_id' => $defaultCityId, 'zone_id' => $defaultZoneId];
        }

        $cityId = $defaultCityId;

        $cities = Cache::remember('pathao_cities', 86400, function () use ($token) {
            $res = Http::withToken($token)->timeout(10)->get("{$this->baseUrl}/aladdin/api/v1/countries/1/city-list");
            return $res->json('data.data') ?? [];
        });

        $cityMatch = collect($cities)->first(fn($c) => stripos($c['city_name'], trim($cityStr)) !== false);
        if ($cityMatch) {
            $cityId = $cityMatch['city_id'];
        }

        $zones = Cache::remember("pathao_zones_{$cityId}", 86400, function () use ($token, $cityId) {
            $res = Http::withToken($token)->timeout(10)->get("{$this->baseUrl}/aladdin/api/v1/cities/{$cityId}/zone-list");
            return $res->json('data.data') ?? [];
        });

        $zoneId = $zones[0]['zone_id'] ?? $defaultZoneId;

        if (!empty(trim($zoneStr))) {
            $zoneMatch = collect($zones)->first(fn($z) => stripos($z['zone_name'], trim($zoneStr)) !== false);
            if ($zoneMatch) {
                $zoneId = $zoneMatch['zone_id'];
            }
        }

        return ['city_id' => $cityId, 'zone_id' => $zoneId];
    }
}
