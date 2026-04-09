<?php

namespace App\Domains\Category\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function products()
    {
        return $this->hasMany(
            \App\Domains\Product\Models\Product::class
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeHasProducts(Builder $query): Builder
    {
        return $query->has('products');
    }

    public function scopeHasActiveProducts(Builder $query): Builder
    {
        return $query->whereHas('products', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Get the banner image URL
     */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . ($this->image ?? 'default-category.jpg'));
    }

    public function getCategoryPageAttribute(): string
    {
        return '/category/' . $this->slug;
    }
}
