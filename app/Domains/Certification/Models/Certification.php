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

    public function products()
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }
}
