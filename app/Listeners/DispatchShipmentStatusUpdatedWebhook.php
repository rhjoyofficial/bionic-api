<?php

namespace App\Listeners;

use App\Events\ShipmentStatusUpdated;
use App\Jobs\SendWebhookJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchShipmentStatusUpdatedWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public bool  $afterCommit = true;
    public int   $tries       = 3;
    public array $backoff      = [30, 120, 300];

    public function handle(ShipmentStatusUpdated $event): void
    {
        $shipment = $event->shipment;
        $shipment->loadMissing('order');

        SendWebhookJob::dispatch('shipment.status_updated', [
            'order_number'           => $shipment->order?->order_number,
            'courier'                => $shipment->courier,
            'tracking_code'          => $shipment->tracking_code,
            'consignment_id'         => $shipment->consignment_id,
            'old_status'             => $event->oldStatus,
            'new_status'             => $event->newStatus,
            'courier_status_message' => $shipment->courier_status_message,
            'synced_at'              => $shipment->status_synced_at?->toISOString(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('DispatchShipmentStatusUpdatedWebhook failed: ' . $e->getMessage());
    }
}
