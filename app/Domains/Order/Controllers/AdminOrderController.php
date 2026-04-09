<?php

namespace App\Domains\Order\Controllers;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Requests\UpdateOrderStatusRequest;
use App\Domains\Order\Resources\OrderResource;
use App\Domains\Order\Services\OrderStatusService;
use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminOrderController extends Controller
{
    public function index()
    {
        try {
            $orders = Order::withCount('items')
                ->with(['zone', 'user'])
                ->when(request('q'), function ($q, $search) {
                    $q->where(fn($inner) =>
                        $inner->where('order_number', 'like', "%{$search}%")
                              ->orWhere('customer_phone', 'like', "%{$search}%")
                              ->orWhere('customer_name', 'like', "%{$search}%")
                    );
                })
                ->when(request('status'), fn($q, $s) => $q->where('order_status', $s))
                ->when(request('payment'), fn($q, $p) => $q->where('payment_method', $p))
                ->when(request('payment_status'), fn($q, $ps) => $q->where('payment_status', $ps))
                ->when(request('customer_id'), fn($q, $id) => $q->where('user_id', $id))
                ->when(request('date_from'), fn($q, $d) => $q->whereDate('placed_at', '>=', $d))
                ->when(request('date_to'), fn($q, $d) => $q->whereDate('placed_at', '<=', $d))
                ->latest('placed_at')
                ->paginate(15);

            return ApiResponse::paginated(OrderResource::collection($orders));
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve orders');
        }
    }

    public function show(Order $order)
    {
        try {
            $order->load(['items', 'zone', 'user', 'shippingAddress', 'adminNotes.admin']);

            return ApiResponse::success(
                new OrderResource($order),
                'Order details retrieved'
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve order details');
        }
    }

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
        OrderStatusService $service,
    ) {
        try {
            $updated = $service->changeStatus(
                $order,
                OrderStatus::from($request->status),
            );

            return ApiResponse::success(
                ['order_status' => $updated->order_status],
                'Order status updated to ' . $request->status,
            );
        } catch (Exception $e) {
            $code = str_contains($e->getMessage(), 'Invalid status transition') ? 422 : 500;
            return $this->handleError($e, $e->getMessage(), $code);
        }
    }

    public function addNote(Request $request, Order $order)
    {
        try {
            $request->validate(['body' => 'required|string|max:2000']);

            $note = $order->adminNotes()->create([
                'user_id' => auth()->id(),
                'body'    => $request->body,
            ]);

            $note->load('admin');

            return ApiResponse::success([
                'id'         => $note->id,
                'body'       => $note->body,
                'admin_name' => $note->admin?->name ?? 'System',
                'created_at' => $note->created_at?->toDateTimeString(),
            ], 'Note added', 201);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to add note');
        }
    }

    private function handleError(Exception $e, string $msg, int $code = 500)
    {
        Log::error($msg . ': ' . $e->getMessage());

        return ApiResponse::error(
            $msg,
            config('app.debug') ? $e->getMessage() : null,
            $code,
        );
    }
}
