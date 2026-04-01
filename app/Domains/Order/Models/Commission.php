<?php

namespace App\Domains\Order\Models;

use App\Domains\Order\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'order_id',
        'referrer_id',
        'commission_amount',
        'status'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
}
