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

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function isValid(): bool
    {
        if (!$this->is_active) return false;

        if ($this->start_date && now()->lt($this->start_date)) return false;

        if ($this->end_date && now()->gt($this->end_date)) return false;

        if ($this->usage_limit && $this->used_count >= $this->usage_limit)
            return false;

        return true;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
