<?php

namespace App\Domains\Shipping\Models;

use App\Domains\Order\Models\Order;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = [
        'name',
        'base_charge',
        'free_shipping_threshold',
        'estimated_days',
        'is_active'
    ];
    protected $casts = [
        'base_charge' => 'float',
        'free_shipping_threshold' => 'float',
        'estimated_days' => 'integer', 
        'is_active' => 'boolean'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'zone_id');
    }
}
