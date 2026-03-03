<?php

namespace App\Domains\Coupon\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Coupon\Models\Coupon;
use App\Domains\Coupon\Requests\StoreCouponRequest;
use App\Domains\Coupon\Requests\UpdateCouponRequest;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminCouponController extends Controller
{
    public function index()
    {
        try {
            return Coupon::latest()->paginate(10);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve coupons');
        }
    }

    public function store(StoreCouponRequest $request)
    {
        try {
            $data = $request->validated();
            $data['code'] = strtoupper($data['code']);
            $coupon = Coupon::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Coupon created successfully',
                'data' => $coupon
            ], 201);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to create coupon');
        }
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        try {
            $coupon->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Coupon updated successfully',
                'data' => $coupon
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to update coupon');
        }
    }

    public function destroy(Coupon $coupon)
    {
        try {
            $coupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Coupon deleted successfully'
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete coupon');
        }
    }

    /**
     * Common error response handler
     */
    private function handleError(Exception $e, string $msg)
    {
        Log::error($msg . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return response()->json([
            'success' => false,
            'message' => $msg,
            'error'   => config('app.debug') ? $e->getMessage() : 'Server Error'
        ], 500);
    }
}
