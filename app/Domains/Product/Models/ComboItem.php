<?php

namespace App\Domains\Product\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Product\Models\ProductVariant;

class ComboItem extends Model
{
    public $timestamps = false;
    protected $fillable = ['combo_id', 'product_variant_id', 'quantity', 'combo_name_snapshot'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }
}
