<?php

namespace App\Domains\Courier\Models;

use App\Domains\Order\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CourierShipment extends Model
{
    protected $fillable = [
        'order_id',
        'courier',
        'tracking_code',
        'consignment_id',
        'status',
        'delivery_fee',
        'cod_amount',
        'courier_status_message',
        'courier_response',
        'created_by',
        'picked_at',
        'delivered_at',
        'status_synced_at',
    ];

    protected $casts = [
        'courier_response' => 'array',
        'delivery_fee'     => 'decimal:2',
        'cod_amount'       => 'decimal:2',
        'picked_at'        => 'datetime',
        'delivered_at'     => 'datetime',
        'status_synced_at' => 'datetime',
    ];

    /**
     * Normalized system statuses.
     */
    public const STATUSES = [
        'pending',
        'picked_up',
        'in_transit',
        'out_for_delivery',
        'delivered',
        'partial_delivery',
        'cancelled',
        'returned',
        'on_hold',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this shipment can be cancelled (only before pickup).
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending']);
    }

    /**
     * Check if the shipment has reached a terminal state.
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, ['delivered', 'cancelled', 'returned']);
    }

    /**
     * Get a human-friendly status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'          => 'Pending Pickup',
            'picked_up'        => 'Picked Up',
            'in_transit'       => 'In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered'        => 'Delivered',
            'partial_delivery' => 'Partial Delivery',
            'cancelled'        => 'Cancelled',
            'returned'         => 'Returned',
            'on_hold'          => 'On Hold',
            default            => ucfirst($this->status),
        };
    }
}
