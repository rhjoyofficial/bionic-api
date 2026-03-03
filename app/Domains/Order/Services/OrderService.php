<?php

namespace App\Domains\Order\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Domains\Order\Models\Order;
use App\Domains\Product\Models\ProductVariant;
use App\Domains\Product\Services\PricingService;
use App\Domains\Shipping\Models\ShippingZone;
use App\Domains\Shipping\Services\ShippingCalculator;
use App\Domains\Coupon\Services\CouponValidationService;

class OrderService
{
    public function __construct(
        private PricingService $pricingService,
        private CouponValidationService $couponService
    ) {}

    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {

            $subtotal = 0;
            $discountTotal = 0;

            $items = $data['items'];
            unset($data['items']);

            $zone = ShippingZone::findOrFail($data['zone_id']);

            $order = Order::create([
                ...$data,
                'order_number' => 'BNC-' . strtoupper(Str::random(8)),
                'subtotal' => 0,
                'discount_total' => 0,
                'shipping_cost' => 0,
                'grand_total' => 0,
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'order_status' => 'pending'
            ]);

            foreach ($items as $item) {

                $variant = ProductVariant::with('product')
                    ->findOrFail($item['variant_id']);

                $pricing = $this->pricingService
                    ->calculate($variant, $item['quantity']);

                $subtotal += $variant->price * $item['quantity'];
                $discountTotal += $pricing['discount'];

                $order->items()->create([
                    'product_id' => $variant->product->id,
                    'variant_id' => $variant->id,
                    'product_name_snapshot' => $variant->product->name,
                    'variant_title_snapshot' => $variant->title,
                    'quantity' => $item['quantity'],
                    'unit_price' => $variant->price,
                    'total_price' => $pricing['total']
                ]);
            }

            $couponDiscount = 0;
            $couponId = null;

            if (!empty($data['coupon_code'])) {

                $couponResult = $this->couponService->validate(
                    $data['coupon_code'],
                    $subtotal - $discountTotal
                );

                $coupon = $couponResult['coupon'];
                $couponDiscount = $couponResult['discount'];

                $couponId = $coupon->id;

                $coupon->increment('used_count');
            }

            $calculator = new ShippingCalculator();

            $shippingCost = $calculator->calculate($zone, $subtotal - $discountTotal);

            if (
                $zone->free_shipping_threshold &&
                ($subtotal - $discountTotal) >= $zone->free_shipping_threshold
            ) {
                $shippingCost = 0;
            }

            $totalAfterProductDiscount = $subtotal - $discountTotal;

            $totalAfterCoupon = $totalAfterProductDiscount - $couponDiscount;

            $grandTotal = $totalAfterCoupon + $shippingCost;

            $order->update([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal + $couponDiscount,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'coupon_id' => $couponId
            ]);

            return $order->load('items');
        });
    }
}
