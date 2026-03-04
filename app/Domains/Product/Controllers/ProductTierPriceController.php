<?php

namespace App\Domains\Product\Controllers;

use App\Domains\Product\Models\ProductVariant;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse; // Import your helper
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

            // Check if a tier for this quantity already exists to avoid confusion
            $tierPrice = $variant->tierPrices()->updateOrCreate(
                ['min_quantity' => $validated['min_quantity']],
                $validated
            );

            return ApiResponse::success(
                $tierPrice,
                'Tier price added successfully',
                201
            );
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

            return ApiResponse::success(null, 'Tier price deleted successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete tier price');
        }
    }

    /**
     * Updated to use standard ApiResponse
     */
    private function handleError(Exception $e, string $customMessage)
    {
        Log::error($customMessage . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return ApiResponse::error(
            $customMessage,
            config('app.debug') ? $e->getMessage() : null,
            500
        );
    }
}
