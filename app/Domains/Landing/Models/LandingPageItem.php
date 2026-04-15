<?php

namespace App\Domains\Landing\Models;

use App\Domains\Product\Models\Combo;
use App\Domains\Product\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPageItem extends Model
{
    protected $fillable = [
        'landing_page_id',
        'product_variant_id',
        'combo_id',
        'is_preselected',
        'sort_order',
    ];

    protected $casts = [
        'is_preselected' => 'boolean',
    ];

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class);
    }
}
