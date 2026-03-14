<?php

namespace App\Domains\Store\Models;

use App\Domains\Product\Models\Product;
use App\Domains\Category\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class HeroBanner extends Model
{
    protected $table = 'hero_banners';

    protected $fillable = [
        'badge',
        'title',
        'subtitle',
        'description',
        'button_text',
        'button_url',
        'image',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
        'product_id',
        'category_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    /**
     * Get the product associated with this banner
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the category associated with this banner
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope for active banners based on dates and status
     */
    public function scopeActive($query)
    {
        // $now = Carbon::now();

        return $query->where('is_active', true)
            // ->where(function ($q) use ($now) {
            //     $q->whereNull('starts_at')
            //         ->orWhere('starts_at', '<=', $now);
            // })
            // ->where(function ($q) use ($now) {
            //     $q->whereNull('ends_at')
            //         ->orWhere('ends_at', '>=', $now);
            // })
        ;
    }

    /**
     * Scope to order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get the banner image URL
     */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
    }
}
