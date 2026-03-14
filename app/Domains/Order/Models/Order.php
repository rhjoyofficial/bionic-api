<?php

namespace App\Domains\Order\Models;

use App\Domains\Coupon\Models\Coupon;
use App\Domains\Shipping\Models\ShippingZone;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'zone_id',
        'subtotal',
        'discount_total',
        'shipping_cost',
        'grand_total',
        'coupon_id',
        'payment_method',
        'payment_status',
        'order_status',
        'placed_at',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'notes',
        'checkout_token',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'shipping');
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'billing');
    }

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Calculate how long it took to fulfill the order.
     */
    public function getFulfillmentDurationAttribute(): ?string
    {
        if (!$this->placed_at || !$this->delivered_at) {
            return null;
        }

        return $this->placed_at->diffForHumans($this->delivered_at, [
            'syntax' => Carbon::DIFF_ABSOLUTE,
            'parts' => 2,
        ]);
    }
    /**
     * Check if the order exceeded your SLA (e.g., 48 hours to ship)
     */
    public function isLateToShip(): bool
    {
        if ($this->shipped_at) return false;

        return $this->placed_at->addHours(48)->isPast();
    }
}
