<?php

namespace App\Domains\Coupon\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Coupon\Services\CouponValidationService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class PublicCouponController extends Controller
{
    /**
     * Validate a coupon code and return the calculated discount.
     */
    public function validateCoupon(
        Request $request,
        CouponValidationService $service
    ) {
        $request->validate([
            'code' => 'required|string',
            'order_amount' => 'required|numeric|min:0'
        ]);

        try {
            $result = $service->validate(
                $request->code,
                $request->order_amount
            );

            // Using our standard success envelope
            return ApiResponse::success([
                'valid' => true,
                'discount' => $result['discount'],
                'coupon_id' => $result['coupon']->id, // Useful for the checkout payload
            ], 'Coupon validated successfully');
        } catch (Exception $e) {
            // We use 422 (Unprocessable Entity) for logical validation failures
            // Like "Coupon expired" or "Amount too low"
            return ApiResponse::error(
                $e->getMessage(),
                ['valid' => false],
                422
            );
        }
    }
}
