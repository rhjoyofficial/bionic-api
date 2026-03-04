<?php

namespace App\Domains\Coupon\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Coupon\Models\Coupon;
use App\Domains\Coupon\Requests\StoreCouponRequest;
use App\Domains\Coupon\Requests\UpdateCouponRequest;
use App\Support\ApiResponse; // Import your helper
use Illuminate\Support\Facades\Log;
use Exception;

class AdminCouponController extends Controller
{
    public function index()
    {
        try {
            $coupons = Coupon::latest()->paginate(10);

            return ApiResponse::paginated($coupons);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve coupons');
        }
    }

    public function store(StoreCouponRequest $request)
    {
        try {
            $data = $request->validated();
            $data['code'] = strtoupper($data['code']); // Keep the logic to force uppercase

            $coupon = Coupon::create($data);

            return ApiResponse::success(
                $coupon,
                'Coupon created successfully',
                201
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to create coupon');
        }
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        try {
            $coupon->update($request->validated());

            return ApiResponse::success(
                $coupon,
                'Coupon updated successfully'
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to update coupon');
        }
    }

    public function destroy(Coupon $coupon)
    {
        try {
            $coupon->delete();

            return ApiResponse::success(null, 'Coupon deleted successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete coupon');
        }
    }

    /**
     * Unified error response handler using ApiResponse
     */
    private function handleError(Exception $e, string $msg)
    {
        Log::error($msg . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return ApiResponse::error(
            $msg,
            config('app.debug') ? $e->getMessage() : null,
            500
        );
    }
}
