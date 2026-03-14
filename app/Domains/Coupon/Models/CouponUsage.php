<?php

namespace App\Domains\Coupon\Models;

use App\Domains\Order\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $fillable = ['coupon_id', 'user_id', 'order_id', 'discount_amount'];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
