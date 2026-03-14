<?php

namespace App\Domains\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPage extends Model
{
    protected $fillable = [
        'slug',
        'product_id',
        'title',
        'hero_image',
        'content',
        'meta_title',
        'meta_description',
        'pixel_event_name',
        'is_active',
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Product\Models\Product::class);
    }
}
