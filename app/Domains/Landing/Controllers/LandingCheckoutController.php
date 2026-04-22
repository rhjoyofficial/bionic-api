<?php

namespace App\Domains\Landing\Controllers;

use App\Domains\Landing\Services\LandingCheckoutService;
use App\Domains\Landing\Models\LandingPage;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * LandingCheckoutController — API controller
 *
 * Provides preview (pricing) and checkout (order creation) endpoints
 * for landing page embedded checkout forms.
 */
class LandingCheckoutController extends Controller
{
    public function __construct(
        private readonly LandingCheckoutService $checkoutService,
    ) {}

    /**
     * POST /api/landing/{slug}/preview
     *
     * Returns real-time pricing for the landing page checkout form.
     */
    public function preview(Request $request, string $slug): JsonResponse
    {
        $landing = LandingPage::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        if (! $landing->hasEmbeddedCheckout()) {
            return ApiResponse::error('This page type does not support direct checkout.', null, 422);
        }

        $validated = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.combo_id'   => 'nullable|integer|exists:combos,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'zone_id'            => 'required|integer|exists:shipping_zones,id',
            'coupon_code'        => 'nullable|string|max:50',
        ]);

        try {
            $user = Auth::guard('web')->user() ?? Auth::guard('sanctum')->user();

            $result = $this->checkoutService->preview(
                items: $validated['items'],
                zoneId: $validated['zone_id'],
                landing: $landing,
                couponCode: $validated['coupon_code'] ?? null,
                user: $user,
            );

            return ApiResponse::success($result, 'Pricing calculated');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), null, 422);
        }
    }

    /**
     * POST /api/landing/{slug}/checkout
     *
     * Creates an order directly from the landing page.
     */
    public function checkout(Request $request, string $slug): JsonResponse
    {
        $landing = LandingPage::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        if (! $landing->hasEmbeddedCheckout()) {
            return ApiResponse::error('This page type does not support direct checkout.', null, 422);
        }

        $validated = $request->validate([
            'customer_name'      => 'required|string|max:100',
            'customer_phone'     => 'required|string|max:20',
            'customer_email'     => 'nullable|email|max:100',
            'address_line'       => 'required|string|max:500',
            'area'               => 'nullable|string|max:100',
            'city'               => 'nullable|string|max:100',
            'zone_id'            => 'required|integer|exists:shipping_zones,id',
            'payment_method'     => 'required|in:cod,sslcommerz',
            'items'              => 'required|array|min:1',
            'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.combo_id'   => 'nullable|integer|exists:combos,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'coupon_code'        => 'nullable|string|max:50',
        ]);

        try {
            $user = Auth::guard('web')->user() ?? Auth::guard('sanctum')->user();

            $order = $this->checkoutService->checkout($validated, $landing, $user);

            // Store last order in session for the success page
            $request->session()->put('last_order_id', $order->id);

            $redirectUrl = $order->payment_method === 'cod'
                ? route('order.success', ['order' => $order->order_number])
                : route('order.failed') . '?reason=payment_gateway_pending&order=' . $order->order_number;

            return ApiResponse::success([
                'order_number' => $order->order_number,
                'redirect_url' => $redirectUrl,
            ], 'Order placed successfully', 201);
        } catch (Exception $e) {
            Log::error('Landing Checkout Error: ' . $e->getMessage(), [
                'slug'           => $slug,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'zone_id'        => $validated['zone_id'] ?? null,
            ]);

            return ApiResponse::error(
                $e->getMessage() ?: 'Order could not be placed. Please try again.',
                null,
                422,
            );
        }
    }
}
