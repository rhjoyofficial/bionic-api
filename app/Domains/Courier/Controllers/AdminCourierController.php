<?php

namespace App\Domains\Courier\Controllers;

use App\Domains\ActivityLog\Models\ActivityLog;
use App\Domains\Courier\Models\CourierShipment;
use App\Domains\Courier\Services\ShipmentService;
use App\Domains\Order\Models\Order;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Infrastructure\Courier\CourierService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminCourierController extends Controller
{
    public function __construct(
        private readonly ShipmentService $shipmentService,
        private readonly CourierService $courierService,
    ) {}

    /**
     * Get available courier drivers for UI dropdown.
     */
    public function drivers()
    {
        return ApiResponse::success($this->courierService->availableDrivers());
    }

    /**
     * Get all shipments for a specific order.
     */
    public function orderShipments(Order $order)
    {
        $shipments = CourierShipment::where('order_id', $order->id)
            ->with('creator:id,name')
            ->latest()
            ->get()
            ->map(fn($s) => $this->formatShipment($s));

        return ApiResponse::success($shipments);
    }

    /**
     * Assign a courier to a single order (create shipment).
     */
    public function assign(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'courier'  => 'required|in:pathao,steadfast,carrybee',
        ]);

        try {
            $order = Order::findOrFail($request->order_id);

            $shipment = $this->shipmentService->createShipment(
                $order,
                $request->courier,
                auth()->id(),
            );

            $this->logActivity('courier_assigned', $order, [
                'courier'        => $request->courier,
                'shipment_id'    => $shipment->id,
                'tracking_code'  => $shipment->tracking_code,
                'consignment_id' => $shipment->consignment_id,
            ]);

            return ApiResponse::success(
                $this->formatShipment($shipment),
                'Courier assigned successfully.',
                201,
            );
        } catch (Exception $e) {
            Log::error('Courier assign failed: ' . $e->getMessage());
            return ApiResponse::error($e->getMessage(), null, 422);
        }
    }

    /**
     * Bulk assign courier to multiple orders.
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'order_ids'   => 'required|array|min:1|max:50',
            'order_ids.*' => 'exists:orders,id',
            'courier'     => 'required|in:pathao,steadfast,carrybee',
        ]);

        try {
            $result = $this->shipmentService->bulkAssign(
                $request->order_ids,
                $request->courier,
                auth()->id(),
            );

            $this->logActivity('courier_bulk_assigned', null, [
                'courier'       => $request->courier,
                'total'         => count($request->order_ids),
                'success_count' => count($result['created']),
                'error_count'   => count($result['errors']),
            ]);

            return ApiResponse::success([
                'created' => collect($result['created'])->map(fn($s) => $this->formatShipment($s)),
                'errors'  => $result['errors'],
                'summary' => [
                    'total'     => count($request->order_ids),
                    'succeeded' => count($result['created']),
                    'failed'    => count($result['errors']),
                ],
            ], count($result['created']) . ' shipments created, ' . count($result['errors']) . ' failed.');
        } catch (Exception $e) {
            Log::error('Courier bulk assign failed: ' . $e->getMessage());
            return ApiResponse::error($e->getMessage(), null, 422);
        }
    }

    /**
     * Sync/refresh shipment status from courier API.
     */
    public function syncStatus(CourierShipment $shipment)
    {
        try {
            $oldStatus = $shipment->status;
            $updated   = $this->shipmentService->syncStatus($shipment);

            if ($oldStatus !== $updated->status) {
                $this->logActivity('shipment_status_synced', $updated->order, [
                    'shipment_id' => $updated->id,
                    'old_status'  => $oldStatus,
                    'new_status'  => $updated->status,
                    'courier'     => $updated->courier,
                ]);
            }

            return ApiResponse::success(
                $this->formatShipment($updated),
                'Status synced: ' . $updated->status_label,
            );
        } catch (Exception $e) {
            Log::error("Sync status failed for shipment #{$shipment->id}: " . $e->getMessage());
            return ApiResponse::error($e->getMessage(), null, 422);
        }
    }

    /**
     * Cancel a shipment.
     */
    public function cancel(CourierShipment $shipment)
    {
        try {
            $cancelled = $this->shipmentService->cancelShipment($shipment);

            $this->logActivity('shipment_cancelled', $cancelled->order, [
                'shipment_id'   => $cancelled->id,
                'courier'       => $cancelled->courier,
                'tracking_code' => $cancelled->tracking_code,
            ]);

            return ApiResponse::success(
                $this->formatShipment($cancelled),
                'Shipment cancelled.',
            );
        } catch (Exception $e) {
            Log::error("Cancel shipment failed #{$shipment->id}: " . $e->getMessage());
            return ApiResponse::error($e->getMessage(), null, 422);
        }
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    private function formatShipment(CourierShipment $s): array
    {
        return [
            'id'                     => $s->id,
            'order_id'               => $s->order_id,
            'courier'                => $s->courier,
            'courier_label'          => match ($s->courier) {
                'pathao'    => 'Pathao',
                'steadfast' => 'Steadfast',
                'carrybee'  => 'CarryBee',
                default     => ucfirst($s->courier),
            },
            'tracking_code'          => $s->tracking_code,
            'consignment_id'         => $s->consignment_id,
            'status'                 => $s->status,
            'status_label'           => $s->status_label,
            'delivery_fee'           => $s->delivery_fee ? (float) $s->delivery_fee : null,
            'cod_amount'             => $s->cod_amount ? (float) $s->cod_amount : null,
            'courier_status_message' => $s->courier_status_message,
            'is_cancellable'         => $s->isCancellable(),
            'is_terminal'            => $s->isTerminal(),
            'created_by_name'        => $s->creator?->name ?? null,
            'picked_at'              => $s->picked_at?->toDateTimeString(),
            'delivered_at'           => $s->delivered_at?->toDateTimeString(),
            'status_synced_at'       => $s->status_synced_at?->toDateTimeString(),
            'created_at'             => $s->created_at?->toDateTimeString(),
        ];
    }

    private function logActivity(string $event, ?Order $order, array $properties = []): void
    {
        try {
            ActivityLog::create([
                'log_name'     => 'courier',
                'description'  => str_replace('_', ' ', ucfirst($event)),
                'subject_type' => $order ? Order::class : null,
                'subject_id'   => $order?->id,
                'causer_type'  => get_class(auth()->user()),
                'causer_id'    => auth()->id(),
                'event'        => $event,
                'properties'   => $properties,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Activity log failed: ' . $e->getMessage());
        }
    }
}
