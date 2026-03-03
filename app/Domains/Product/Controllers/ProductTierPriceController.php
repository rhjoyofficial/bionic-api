<?php

namespace App\Domains\Product\Controllers;

use App\Domains\Product\Models\ProductVariant;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductTierPriceController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, ProductVariant $variant)
    {
        try {
            $this->authorize('product.update');

            $validated = $request->validate([
                'min_quantity' => 'required|integer|min:1',
                'discount_type' => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0'
            ]);

            $tierPrice = $variant->tierPrices()->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Tier price added successfully',
                'data' => $tierPrice
            ], 201);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to add tier price');
        }
    }

    public function destroy(ProductVariant $variant, $tierId)
    {
        try {
            $this->authorize('product.update');

            $tier = $variant->tierPrices()->findOrFail($tierId);
            $tier->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tier price deleted successfully'
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete tier price');
        }
    }

    /**
     * Standardized error handler
     */
    private function handleError(Exception $e, string $customMessage)
    {
        // Log the error for internal debugging
        Log::error($customMessage . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return response()->json([
            'success' => false,
            'message' => $customMessage,
            // Only show detailed error message if debug mode is on
            'error'   => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
        ], 500);
    }
}
