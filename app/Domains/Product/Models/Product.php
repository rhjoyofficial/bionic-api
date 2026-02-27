<?php

namespace App\Domains\Product\Models;

use App\Domains\Category\Models\Category;
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
        'meta_title',
        'meta_description'
    ];

    protected $casts = [
        'gallery' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function relations()
    {
        return $this->hasMany(ProductRelation::class);
    }
}
