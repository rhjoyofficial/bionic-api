<?php

namespace App\Domains\Order\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OrderNote extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['order_id', 'user_id', 'body'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
