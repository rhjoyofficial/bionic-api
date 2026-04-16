<?php

namespace App\Domains\Landing\Controllers;

use App\Domains\Landing\Models\LandingPage;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Models\Combo;
use App\Domains\Shipping\Models\ShippingZone;
use App\Http\Controllers\Controller;

class LandingPageController extends Controller
{
    public function show(string $slug)
    {
        $landing = LandingPage::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $zones = ShippingZone::where('is_active', true)->orderBy('sort_order')
            ->get(['id', 'name', 'base_charge', 'free_shipping_threshold']);
        $data = match ($landing->type) {
            LandingPage::TYPE_PRODUCT => $this->buildProductData($landing),
            LandingPage::TYPE_COMBO   => $this->buildComboData($landing),
            LandingPage::TYPE_SALES   => $this->buildSalesData($landing),
            default                   => [],
        };
        return view($landing->resolveView(), array_merge($data, [
            'landing' => $landing,
            'zones'   => $zones,
        ]));
    }

    /**
     * Product landing: load the product with its variants and tier prices.
     */
    private function buildProductData(LandingPage $landing): array
    {
        // Do NOT filter by is_landing_enabled here — that flag lives on the
        // Product table and belongs to the product-index sync flow. The landing
        // page's own is_active flag is the authoritative gatekeeper (checked at
        // the top of show()). Filtering here would break landing pages that are
        // managed independently or accessed via their direct /product-page/ URL.
        $product = Product::with(['variants.tierPrices', 'category'])
            ->where('id', $landing->product_id)
            ->where('is_active', true)
            ->firstOrFail();

        return ['product' => $product];
    }

    /**
     * Combo landing: load the combo with its items and variant details.
     */
    private function buildComboData(LandingPage $landing): array
    {
        $combo = Combo::with(['items.variant.product'])
            ->where('id', $landing->combo_id)
            ->where('is_landing_enabled', true)
            ->firstOrFail();

        return ['combo' => $combo];
    }

    /**
     * Sales landing: load all items (variants + combos) attached to this page.
     */
    private function buildSalesData(LandingPage $landing): array
    {
        $landing->load([
            'items.variant.product',
            'items.variant.tierPrices',
            'items.combo.items.variant',
        ]);

        return ['salesItems' => $landing->items];
    }
}
