<?php

namespace App\Domains\Order\Models;

use App\Domains\Coupon\Models\Coupon;
use App\Domains\Courier\Models\CourierShipment;
use App\Domains\Marketing\Models\LandingPage;
use App\Domains\Shipping\Models\ShippingZone;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'zone_id',
        'subtotal',
        'discount_total',
        'shipping_cost',
        'grand_total',
        'coupon_id',
        'coupon_code_snapshot',
        'coupon_discount',
        'payment_method',
        'payment_status',
        'order_status',
        'source',
        'landing_page_id',
        'placed_at',
        'notes',
        'checkout_token',
        'gateway_transaction_id',
    ];

    protected $casts = [
        'placed_at'     => 'datetime',
        'confirmed_at'  => 'datetime',
        'processing_at' => 'datetime',
        'shipped_at'    => 'datetime',
        'delivered_at'  => 'datetime',
        'cancelled_at'  => 'datetime',
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

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function adminNotes()
    {
        return $this->hasMany(OrderNote::class)->latest();
    }

    public function transactions()
    {
        return $this->hasMany(OrderTransaction::class)->orderBy('created_at');
    }

    /**
     * Courier shipments for this order.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(CourierShipment::class)->latest();
    }

    /**
     * The latest active (non-cancelled) shipment.
     */
    public function activeShipment(): HasOne
    {
        return $this->hasOne(CourierShipment::class)
            ->whereNotIn('status', ['cancelled'])
            ->latest();
    }

    /**
     * Check if this order is in a non-terminal status (legacy / customer-facing).
     */
    public function isEditable(): bool
    {
        return in_array($this->order_status, ['pending', 'confirmed']);
    }

    /**
     * Full admin-edit check: order is not terminal AND no shipment has been picked up.
     * Uses the loaded 'shipments' relation to avoid N+1 when called from a collection.
     */
    public function canAdminEdit(): bool
    {
        // Terminal orders can never be edited
        if (in_array($this->order_status, ['delivered', 'returned', 'cancelled'])) {
            return false;
        }

        // Statuses that mean the parcel has left the warehouse
        $blockedShipmentStatuses = ['picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'partial_delivery'];

        if ($this->relationLoaded('shipments')) {
            return $this->shipments->whereIn('status', $blockedShipmentStatuses)->isEmpty();
        }

        return !$this->shipments()->whereIn('status', $blockedShipmentStatuses)->exists();
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
        if ($this->shipped_at || !$this->placed_at) return false;
        return $this->placed_at->addHours(48)->isPast();
    }

    /**
     * Convert the grand total to words.
     */
    public function getAmountInWordsAttribute()
    {
        $number = $this->grand_total;
        $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        $words = $f->format($number);

        return strtoupper($words . " TAKA ONLY");
    }
}
