<?php

namespace App\Domains\Order\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Order\Requests\CheckoutRequest;
use App\Domains\Order\Services\OrderService;
use App\Support\ApiResponse; // Import your helper
use Illuminate\Support\Facades\Log;
use Exception;

class CheckoutController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function store(CheckoutRequest $request)
    {
        try {
            // The service handles transactions, pricing, and event triggers
            $order = $this->service->create($request->validated());

            return ApiResponse::success(
                $order,
                'Order placed successfully',
                201
            );
        } catch (Exception $e) {
            // Log full details for the developer
            Log::error('Checkout Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            // Determine if the error is a user-facing logic error (like coupon failure)
            // or a structural server error.
            $statusCode = $this->getStatusCode($e);

            return ApiResponse::error(
                $e->getMessage() ?: 'Your order could not be processed at this time.',
                config('app.debug') ? $e->getTrace() : null,
                $statusCode
            );
        }
    }

    /**
     * Determine status code based on exception type
     */
    private function getStatusCode(Exception $e): int
    {
        return match (get_class($e)) {
            \Illuminate\Validation\ValidationException::class => 422,
            \Illuminate\Database\Eloquent\ModelNotFoundException::class => 404,
            default => 500,
        };
    }
}
