<?php

namespace App\Domains\Order\Services;

use App\Domains\Coupon\Services\CouponValidationService;
use App\Domains\Order\DTOs\CheckoutPricingResult;
use App\Domains\Product\Models\Combo;
use App\Domains\Product\Models\ProductVariant;
use App\Domains\Product\Services\PricingService;
use App\Domains\Shipping\Models\ShippingZone;
use App\Domains\Shipping\Services\ShippingCalculator;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CheckoutPricingService
{
    public function __construct(
        private readonly PricingService $pricingService,
        private readonly CouponValidationService $couponService,
        private readonly ShippingCalculator $shippingCalculator,
    ) {}

    /**
     * Single pricing engine for cart display, checkout preview, and order creation.
     */
    public function calculate(
        array $items,
        ?string $couponCode = null,
        ?int $zoneId = null,
        ?User $user = null,
        bool $withLock = true,
    ): CheckoutPricingResult {
        $variants = $this->loadVariants($items, $withLock);

        $lineItems        = [];
        $subtotal         = 0;
        $tierDiscountTotal = 0;

        foreach ($items as $item) {
            if (!empty($item['combo_id'])) {
                $line = $this->processComboItem($item, $variants);
            } else {
                $line = $this->processVariantItem($item, $variants);
            }

            $lineItems[]       = $line;
            $subtotal         += $line['original_line_total'];
            $tierDiscountTotal += $line['tier_discount'];
        }

        $afterTierDiscount = $subtotal - $tierDiscountTotal;

        // Coupon
        $couponDiscount = 0;
        $coupon         = null;

        if ($couponCode) {
            $couponResult   = $this->couponService->validate($couponCode, $afterTierDiscount, $user);
            $coupon         = $couponResult['coupon'];
            $couponDiscount = $couponResult['discount'];
        }

        // Shipping
        $shippingCost = 0;
        if ($zoneId) {
            $zone         = ShippingZone::findOrFail($zoneId);
            $shippingCost = $this->shippingCalculator->calculate($zone, $afterTierDiscount - $couponDiscount);
        }

        $grandTotal = max(0, $afterTierDiscount - $couponDiscount) + $shippingCost;

        return new CheckoutPricingResult(
            lineItems: $lineItems,
            subtotal: $subtotal,
            tierDiscountTotal: $tierDiscountTotal,
            couponDiscount: $couponDiscount,
            coupon: $coupon,
            shippingCost: $shippingCost,
            grandTotal: $grandTotal,
            lockedVariants: $variants,
        );
    }

    private function processComboItem(array $item, Collection $variants): array
    {
        $combo      = Combo::with('items')->findOrFail($item['combo_id']);
        $comboPrice = $combo->final_price;
        $quantity   = $item['quantity'];

        foreach ($combo->items as $comboItem) {
            $component = $variants->get($comboItem->product_variant_id);
            if (!$component || !$component->hasStock($comboItem->quantity * $quantity)) {
                throw new Exception("Component stock exhausted for bundle: {$combo->title}");
            }
        }

        return [
            'combo_id'            => $combo->id,
            'variant_id'          => null,
            'name'                => $combo->title,
            'variant_title'       => 'Bundle',
            'quantity'            => $quantity,
            'original_unit_price' => $comboPrice,
            'unit_price'          => $comboPrice,
            'original_line_total' => $comboPrice * $quantity,
            'line_total'          => $comboPrice * $quantity,
            'tier_discount'       => 0,
            'discount_type'       => 'none',
            'discount_value'      => 0,
        ];
    }

    private function processVariantItem(array $item, Collection $variants): array
    {
        $variant  = $variants->get($item['variant_id']);
        $quantity = $item['quantity'];

        if (!$variant) {
            throw new Exception('Invalid product variant selected.');
        }

        if (!$variant->hasStock($quantity)) {
            throw new Exception("Insufficient stock for {$variant->title}");
        }

        $pricing = $this->pricingService->calculate($variant, $quantity, $variant->tierPrices);

        return [
            'combo_id'            => null,
            'variant_id'          => $variant->id,
            'name'                => $variant->product->name,
            'variant_title'       => $variant->title,
            'quantity'            => $quantity,
            'original_unit_price' => $pricing['original_unit_price'],
            'unit_price'          => $pricing['unit_price'],
            'original_line_total' => $pricing['original_unit_price'] * $quantity,
            'line_total'          => $pricing['total'],
            'tier_discount'       => $pricing['discount_amount'],
            'discount_type'       => $pricing['discount_type'],
            'discount_value'      => $pricing['discount_value'],
        ];
    }

    private function loadVariants(array $items, bool $withLock): Collection
    {
        $variantIds = collect($items)->pluck('variant_id')->filter()->unique();

        $comboIds = collect($items)->pluck('combo_id')->filter()->unique();
        if ($comboIds->isNotEmpty()) {
            $comboVariantIds = DB::table('combo_items')
                ->whereIn('combo_id', $comboIds)
                ->pluck('product_variant_id');

            $variantIds = $variantIds->merge($comboVariantIds)->unique();
        }

        $query = ProductVariant::query()
            ->with(['product', 'tierPrices'])
            ->whereIn('id', $variantIds);

        if ($withLock) {
            $query->lockForUpdate();
        }

        return $query->get()->keyBy('id');
    }
}
