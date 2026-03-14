<?php

namespace App\Domains\Coupon\Models;

use App\Domains\Coupon\Models\CouponUsage;
use App\Domains\Order\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_purchase',
        'usage_limit',
        'used_count',
        'limit_per_user',
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

    public function isValidForUser(?User $user = null): bool
    {
        if (!$this->is_active) return false;
        if ($this->start_date && now()->lt($this->start_date)) return false;
        if ($this->end_date && now()->gt($this->end_date)) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;

        if ($user && $this->limit_per_user) {
            $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
            if ($userUsageCount >= $this->limit_per_user) {
                return false;
            }
        }

        return true;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }
}
