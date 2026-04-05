<?php

namespace App\Domains\Cart\Controllers;

use App\Domains\Cart\Resources\CartItemResource;
use App\Domains\Cart\Services\CartPricingService;
use App\Domains\Cart\Services\CartService;
use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private CartPricingService $cartPricingService
    ) {}

    public function view(Request $request)
    {
        try {
            $cart = $this->resolveCart($request);

            // Sync prices and get the result
            $wasUpdated = $this->cartService->syncCartPrices($cart);

            return ApiResponse::success(
                array_merge($this->payload($cart->fresh()), [
                    'prices_updated' => $wasUpdated // The key flag for frontend
                ]),
                $wasUpdated ? 'Prices updated' : 'Cart loaded'
            );
        } catch (Exception $e) {
            return $this->fail($e, 'Could not retrieve cart');
        }
    }

    public function add(Request $request)
    {
        try {

            $request->validate([
                'variant_id' => 'required|exists:product_variants,id',
                'quantity' => 'required|integer|min:1'
            ]);


            $cart = $this->resolveCart($request);

            $this->cartService->addItem(
                $cart,
                $request->variant_id,
                $request->quantity
            );

            return ApiResponse::success(
                $this->payload($cart->fresh()),
                'Item added',
                201
            );
        } catch (Exception $e) {
            Log::error('Add to cart failed: ' . $e->getMessage());
            return $this->fail($e, 'Add failed');
        }
    }

    public function addCombo(Request $request)
    {
        try {
            $request->validate([
                'combo_id' => 'required|exists:combos,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $cart = $this->resolveCart($request);

            $this->cartService->addCombo(
                $cart,
                $request->combo_id,
                $request->quantity
            );

            return ApiResponse::success(
                $this->payload($cart->fresh()),
                'Combo added to cart',
                201
            );
        } catch (Exception $e) {
            return $this->fail($e, 'Add combo failed');
        }
    }

    public function update(Request $request)
    {
        try {
            $cart = $this->resolveCart($request);

            $request->validate([
                'cart_item_id' => [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) use ($cart) {
                        $exists = \Illuminate\Support\Facades\DB::table('cart_items')
                            ->where('id', $value)
                            ->where('cart_id', $cart->id)
                            ->exists();

                        if (!$exists) {
                            $fail('The selected cart item is invalid or does not belong to your cart.');
                        }
                    },
                ],
                'quantity' => 'required|integer|min:1'
            ]);


            $this->cartService->updateItemQuantity(
                $cart,
                $request->cart_item_id,
                $request->quantity
            );

            return ApiResponse::success(
                $this->payload($cart->fresh()),
                'Cart updated'
            );
        } catch (Exception $e) {
            return $this->fail($e, 'Update failed');
        }
    }

    public function remove(Request $request)
    {
        try {
            $cart = $this->resolveCart($request);

            $request->validate([
                'cart_item_id' => [
                    'required',
                    function ($attribute, $value, $fail) use ($cart) {
                        $exists = \Illuminate\Support\Facades\DB::table('cart_items')
                            ->where('id', $value)
                            ->where('cart_id', $cart->id)
                            ->exists();

                        if (!$exists) {
                            $fail('The selected cart item is invalid.');
                        }
                    },
                ],
            ]);

            $this->cartService->removeItem($cart, $request->cart_item_id);

            return ApiResponse::success($this->payload($cart->fresh()), 'Item removed');
        } catch (Exception $e) {
            return $this->fail($e, 'Remove failed');
        }
    }

    public function clear(Request $request)
    {
        try {

            $cart = $this->resolveCart($request);

            $this->cartService->clearCart($cart);

            return ApiResponse::success(
                $this->payload($cart->fresh()),
                'Cart cleared'
            );
        } catch (Exception $e) {
            return $this->fail($e, 'Clear failed');
        }
    }

    private function resolveCart(Request $request)
    {
        if (Auth::check()) {
            return $this->cartService->getCart(Auth::id(), null);
        }

        // Trust the token provided by the HandleCartSession middleware
        $sessionToken = $request->attributes->get('cart_token');

        if (!$sessionToken) {
            throw new \Exception('Guest session token is missing.');
        }

        return $this->cartService->getCart(null, $sessionToken);
    }

    private function payload($cart)
    {
        $cart->load(['items.variant.product', 'items.variant.tierPrices', 'items.combo']);
        return [
            'items' => CartItemResource::collection($cart->items),
            'totals' => $this->cartPricingService->calculate($cart),
            'cart_id' => $cart->id,
        ];
    }

    private function fail(Exception $e, string $msg)
    {
        Log::error($msg . ': ' . $e->getMessage());

        return ApiResponse::error(
            $msg,
            config('app.debug') ? $e->getMessage() : null,
            500
        );
    }
}
