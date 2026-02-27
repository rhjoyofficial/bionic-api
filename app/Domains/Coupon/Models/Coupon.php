<?php

namespace App\Domains\Coupon\Models;

use App\Domains\Order\Models\Order;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_purchase',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'is_active'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
