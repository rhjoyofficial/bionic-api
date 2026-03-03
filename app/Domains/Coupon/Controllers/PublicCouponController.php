<?php

namespace App\Domains\Coupon\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Coupon\Services\CouponValidationService;
use Illuminate\Http\Request;

class PublicCouponController extends Controller
{
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

            return response()->json([
                'valid' => true,
                'discount' => $result['discount']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
