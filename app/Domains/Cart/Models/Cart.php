<?php

namespace App\Domains\Cart\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_token'
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
