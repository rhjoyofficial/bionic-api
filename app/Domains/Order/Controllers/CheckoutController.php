<?php

namespace App\Domains\Order\Controllers;

use App\Domains\Order\Requests\CheckoutRequest;
use App\Domains\Order\Services\OrderService;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(private readonly OrderService $service) {}

    public function store(CheckoutRequest $request)
    {
        try {
            $order = $this->service->create($request->validated());

            return ApiResponse::success(
                $order,
                'Order placed successfully',
                201,
            );
        } catch (Exception $e) {
            Log::error('Checkout Error: '.$e->getMessage(), [
                'customer_phone' => $request->input('customer_phone'),
                'zone_id' => $request->input('zone_id'),
                'item_count' => count($request->input('items', [])),
            ]);

            return ApiResponse::error(
                $e->getMessage() ?: 'Your order could not be processed at this time.',
                config('app.debug') ? $e->getMessage() : null,
                $this->getStatusCode($e),
            );
        }
    }

    private function getStatusCode(Exception $e): int
    {
        return match (true) {
            $e instanceof ValidationException => 422,
            $e instanceof ModelNotFoundException => 404,
            default => 500,
        };
    }
}
