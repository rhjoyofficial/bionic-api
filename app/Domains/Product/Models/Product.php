<?php

namespace App\Domains\Product\Models;

use App\Domains\Category\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'category_id',
        'base_price',
        'thumbnail',
        'gallery',
        'sku',
        'is_active',
        'is_featured',
        'is_trending',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'landing_slug',
        'is_landing_enabled',
    ];

    protected $casts = [
        'gallery' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_trending' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function allVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true)->orderBy('id');
    }
    public function relations()
    {
        return $this->hasMany(ProductRelation::class);
    }

    public function upsells()
    {
        return $this->belongsToMany(
            Product::class,
            'product_relations',
            'product_id',
            'related_product_id'
        )->wherePivot('type', 'upsell');
    }

    public function crossSells()
    {
        return $this->belongsToMany(
            Product::class,
            'product_relations',
            'product_id',
            'related_product_id'
        )->wherePivot('type', 'cross_sell');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    public function scopeTrending(Builder $query): Builder
    {
        return $query->where('is_trending', true);
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/products/' . ($this->thumbnail ?? 'default-products.jpg'));
    }

    
}
