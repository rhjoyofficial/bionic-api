<?php

namespace App\Domains\Order\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Order\Requests\CheckoutRequest;
use App\Domains\Order\Services\OrderService;

class CheckoutController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function store(CheckoutRequest $request)
    {
        $order = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
}
