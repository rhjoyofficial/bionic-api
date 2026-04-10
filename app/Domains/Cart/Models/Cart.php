<?php

namespace App\Domains\Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_token',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
