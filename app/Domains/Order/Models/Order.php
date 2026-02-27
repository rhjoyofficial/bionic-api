<?php

namespace App\Domains\Order\Models;

use App\Domains\Coupon\Models\Coupon;
use App\Domains\Shipping\Models\ShippingZone;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'address_line',
        'area',
        'city',
        'postal_code',
        'zone_id',
        'subtotal',
        'discount_total',
        'shipping_cost',
        'grand_total',
        'coupon_id',
        'payment_method',
        'payment_status',
        'order_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
