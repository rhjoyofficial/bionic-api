<?php

namespace App\Domains\Certification\Models;

use App\Domains\Product\Models\Product;
use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $fillable = [
        'name',
        'category',
        'organization',
        'given_date',
        'expiry_date',
        'additional_details',
        'logo_path',
        'image_path',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'given_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    public function getLogoUrlAttribute(): string
    {
        return asset('storage/' . $this->logo_path);
    }
}
