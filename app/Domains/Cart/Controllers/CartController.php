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
        private CartPricingService $pricing
    ) {}

    public function view(Request $request)
    {
        try {

            $cart = $this->resolveCart($request);

            return ApiResponse::success(
                $this->payload($cart),
                'Cart loaded'
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
            $request->validate([
                'cart_item_id' => 'required|exists:cart_items,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $cart = $this->resolveCart($request);

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

            $this->cartService->removeItem(
                $cart,
                $request->variant_id
            );

            return ApiResponse::success(
                $this->payload($cart->fresh()),
                'Item removed'
            );
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
        return $this->cartService->getCart(
            Auth::id(),
            $request->header('X-Session-Token') ?? $request->session_token
        );
    }

    private function payload($cart)
    {
        $cart->load('items.variant.product');

        return [
            'items' => CartItemResource::collection($cart->items),
            'totals' => $this->pricing->calculate($cart),
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
