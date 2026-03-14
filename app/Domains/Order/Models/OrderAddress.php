<?php

namespace App\Domains\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{
    protected $fillable = [
        'order_id',
        'type', // 'shipping' or 'billing'
        'customer_name',
        'customer_phone',
        'address_line',
        'area',
        'city',
        'postal_code',
        'latitude',  // Useful for courier API integrations
        'longitude',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
