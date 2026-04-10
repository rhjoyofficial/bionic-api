<?php

namespace App\Domains\Order\Controllers;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Requests\UpdateOrderStatusRequest;
use App\Domains\Order\Resources\OrderResource;
use App\Domains\Order\Services\OrderEditService;
use App\Domains\Order\Services\OrderStatusService;
use App\Domains\Product\Models\Combo;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Models\ProductVariant;
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
                ->with(['zone', 'user', 'shipments'])
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
            $order->load(['items', 'zone', 'user', 'shippingAddress', 'adminNotes.admin', 'shipments.creator']);

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

    // ──────────────────────────────────────────────────────────────
    // ORDER EDIT ENDPOINTS
    // ──────────────────────────────────────────────────────────────

    /**
     * Get order data prepared for editing (items + stock info).
     */
    public function editData(Order $order, OrderEditService $editService)
    {
        try {
            $data = $editService->getEditData($order);
            return ApiResponse::success($data);
        } catch (Exception $e) {
            return $this->handleError($e, $e->getMessage(), 422);
        }
    }

    /**
     * Preview recalculated totals without applying changes.
     */
    public function previewEdit(Request $request, Order $order, OrderEditService $editService)
    {
        $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Each item must have either variant_id or combo_id
        foreach ($request->items as $i => $item) {
            if (empty($item['variant_id']) && empty($item['combo_id'])) {
                return ApiResponse::error("Item #{$i} must have variant_id or combo_id.", null, 422);
            }
        }

        try {
            $preview = $editService->previewEdit($order, $request->items);
            return ApiResponse::success($preview);
        } catch (Exception $e) {
            return $this->handleError($e, $e->getMessage(), 422);
        }
    }

    /**
     * Apply the edit — replace items and recalculate totals.
     */
    public function applyEdit(Request $request, Order $order, OrderEditService $editService)
    {
        $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        foreach ($request->items as $i => $item) {
            if (empty($item['variant_id']) && empty($item['combo_id'])) {
                return ApiResponse::error("Item #{$i} must have variant_id or combo_id.", null, 422);
            }
        }

        try {
            $updated = $editService->applyEdit($order, $request->items, auth()->id());

            return ApiResponse::success(
                new OrderResource($updated->load(['items', 'zone', 'user', 'shippingAddress', 'adminNotes.admin', 'shipments.creator'])),
                'Order items updated and totals recalculated.',
            );
        } catch (Exception $e) {
            return $this->handleError($e, $e->getMessage(), 422);
        }
    }

    /**
     * Search products/variants for the "add item" form.
     */
    public function searchProducts(Request $request)
    {
        $q = $request->get('q', '');

        if (strlen($q) < 2) {
            return ApiResponse::success([]);
        }

        // Search variants
        $variants = ProductVariant::with('product:id,name,thumbnail')
            ->where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%")
                      ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$q}%"));
            })
            ->limit(15)
            ->get()
            ->map(fn($v) => [
                'type'            => 'variant',
                'variant_id'      => $v->id,
                'combo_id'        => null,
                'product_name'    => $v->product?->name,
                'variant_title'   => $v->title,
                'sku'             => $v->sku,
                'price'           => (float) $v->final_price,
                'available_stock' => $v->available_stock,
                'thumbnail'       => $v->product?->thumbnail ? asset('storage/' . $v->product->thumbnail) : null,
            ]);

        // Search combos
        $combos = Combo::where('is_active', true)
            ->where('title', 'like', "%{$q}%")
            ->limit(5)
            ->get()
            ->map(fn($c) => [
                'type'            => 'combo',
                'variant_id'      => null,
                'combo_id'        => $c->id,
                'product_name'    => $c->title,
                'variant_title'   => 'Bundle',
                'sku'             => null,
                'price'           => (float) $c->final_price,
                'available_stock' => 999,
                'thumbnail'       => $c->image ? asset('storage/' . $c->image) : null,
            ]);

        return ApiResponse::success($variants->concat($combos)->values());
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
