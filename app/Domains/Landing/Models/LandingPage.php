<?php

namespace App\Domains\Landing\Models;

use App\Domains\Product\Models\Combo;
use App\Domains\Product\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingPage extends Model
{
    public const TYPE_PRODUCT = 'product';
    public const TYPE_COMBO   = 'combo';
    public const TYPE_SALES   = 'sales';

    protected $fillable = [
        'slug',
        'type',
        'product_id',
        'combo_id',
        'title',
        'hero_image',
        'blade_template',
        'content',
        'meta_title',
        'meta_description',
        'pixel_event_name',
        'config',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config'    => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class);
    }

    /**
     * Items for sales-type landing pages (multiple variants/combos).
     */
    public function items(): HasMany
    {
        return $this->hasMany(LandingPageItem::class)->orderBy('sort_order');
    }

    // ── Config Helpers ────────────────────────────────────────

    /**
     * Get a config value with optional default.
     */
    public function cfg(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Free delivery after this total amount (null = use zone default).
     */
    public function freeDeliveryAmount(): ?float
    {
        $val = $this->cfg('free_delivery_amount');
        return $val !== null ? (float) $val : null;
    }

    /**
     * Free delivery after this total quantity (null = disabled).
     */
    public function freeDeliveryQty(): ?int
    {
        $val = $this->cfg('free_delivery_qty');
        return $val !== null ? (int) $val : null;
    }

    // ── Blade Resolution ──────────────────────────────────────

    /**
     * Resolve the full Blade view path for this landing page.
     * Falls back to the default template for the type.
     */
    public function resolveView(): string
    {
        $custom = 'landing.templates.' . $this->blade_template;

        if (view()->exists($custom)) {
            return $custom;
        }

        // Fallback to type default
        return 'landing.templates.' . $this->type . '-default';
    }
}
