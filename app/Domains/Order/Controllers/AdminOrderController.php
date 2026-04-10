<?php

namespace App\Domains\Order\Controllers;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Requests\UpdateOrderStatusRequest;
use App\Domains\Order\Resources\OrderResource;
use App\Domains\Order\Services\AdminOrderCreationService;
use App\Domains\Order\Services\OrderEditService;
use App\Domains\Order\Services\OrderStatusService;
use App\Domains\Product\Models\Combo;
use App\Domains\Product\Models\ProductVariant;
use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminOrderController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // LISTING & DETAIL
    // ──────────────────────────────────────────────────────────────

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

    // ──────────────────────────────────────────────────────────────
    // ADMIN CREATE ORDER
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a brand-new order from the admin panel.
     * Bypasses cart flow — admin provides all data directly.
     */
    public function store(Request $request, AdminOrderCreationService $creationService)
    {
        $validated = $request->validate([
            'customer_name'    => 'required|string|max:200',
            'customer_phone'   => 'required|string|max:20',
            'customer_email'   => 'nullable|email|max:200',
            'address_line'     => 'required|string|max:500',
            'area'             => 'nullable|string|max:200',
            'city'             => 'nullable|string|max:100',
            'postal_code'      => 'nullable|string|max:20',
            'zone_id'          => 'required|integer|exists:shipping_zones,id',
            'payment_method'   => 'required|string|in:cod,online',
            'coupon_code'      => 'nullable|string|max:50',
            'notes'            => 'nullable|string|max:2000',
            'linked_user_id'   => 'nullable|integer|exists:users,id',
            'items'            => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Each item needs variant_id or combo_id
        foreach ($validated['items'] as $i => $item) {
            if (empty($item['variant_id']) && empty($item['combo_id'])) {
                return ApiResponse::error("Item #{$i} must have variant_id or combo_id.", null, 422);
            }
        }

        try {
            $linkedUser = !empty($validated['linked_user_id'])
                ? User::find($validated['linked_user_id'])
                : null;

            $order = $creationService->create($validated, auth()->id(), $linkedUser);

            return ApiResponse::success(
                new OrderResource($order->load(['items', 'zone', 'user', 'shippingAddress', 'shipments'])),
                'Order created successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->handleError($e, $e->getMessage(), 422);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // STATUS & NOTES
    // ──────────────────────────────────────────────────────────────

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
    // ORDER EDITING (items + customer/address/zone)
    // ──────────────────────────────────────────────────────────────

    /**
     * Return current order data for the edit form.
     * Now includes customer info, address, zone.
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
     * Preview recalculated totals without committing.
     * Accepts optional new zone_id to preview shipping changes.
     */
    public function previewEdit(Request $request, Order $order, OrderEditService $editService)
    {
        $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
            'zone_id'          => 'nullable|integer|exists:shipping_zones,id',
        ]);

        foreach ($request->items as $i => $item) {
            if (empty($item['variant_id']) && empty($item['combo_id'])) {
                return ApiResponse::error("Item #{$i} must have variant_id or combo_id.", null, 422);
            }
        }

        try {
            $preview = $editService->previewEdit($order, $request->items, $request->zone_id);
            return ApiResponse::success($preview);
        } catch (Exception $e) {
            return $this->handleError($e, $e->getMessage(), 422);
        }
    }

    /**
     * Apply full order edit — items, customer info, address, zone.
     */
    public function applyEdit(Request $request, Order $order, OrderEditService $editService)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.quantity'   => 'required|integer|min:1',
            'zone_id'            => 'nullable|integer|exists:shipping_zones,id',
            'customer_name'      => 'nullable|string|max:200',
            'customer_phone'     => 'nullable|string|max:20',
            'customer_email'     => 'nullable|email|max:200',
            'address_line'       => 'nullable|string|max:500',
            'area'               => 'nullable|string|max:200',
            'city'               => 'nullable|string|max:100',
            'postal_code'        => 'nullable|string|max:20',
            'notes'              => 'nullable|string|max:2000',
        ]);

        foreach ($request->items as $i => $item) {
            if (empty($item['variant_id']) && empty($item['combo_id'])) {
                return ApiResponse::error("Item #{$i} must have variant_id or combo_id.", null, 422);
            }
        }

        // Collect customer/address fields if provided
        $customerData = array_filter([
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'address_line'   => $request->address_line,
            'area'           => $request->area,
            'city'           => $request->city,
            'postal_code'    => $request->postal_code,
            'notes'          => $request->notes,
        ], fn($v) => $v !== null);

        try {
            $updated = $editService->applyEdit(
                $order,
                $request->items,
                auth()->id(),
                $customerData,
                $request->zone_id,
            );

            return ApiResponse::success(
                new OrderResource($updated->load(['items', 'zone', 'user', 'shippingAddress', 'adminNotes.admin', 'shipments.creator'])),
                'Order updated successfully.',
            );
        } catch (Exception $e) {
            return $this->handleError($e, $e->getMessage(), 422);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // PRODUCT SEARCH (used by edit + create forms)
    // ──────────────────────────────────────────────────────────────

    /**
     * Search active variants and combos by name / SKU.
     * Used by both the order edit panel and the create order form.
     */
    public function searchProducts(Request $request)
    {
        $q = $request->get('q', '');

        if (strlen($q) < 2) {
            return ApiResponse::success([]);
        }

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

    // ──────────────────────────────────────────────────────────────
    // SHIPPING ZONES (for dropdowns in create/edit forms)
    // ──────────────────────────────────────────────────────────────

    /**
     * Return all active shipping zones for dropdowns.
     */
    public function shippingZones()
    {
        $zones = \App\Domains\Shipping\Models\ShippingZone::orderBy('sort_order')
            ->get(['id', 'name', 'base_rate', 'free_above']);

        return ApiResponse::success($zones);
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVATE
    // ──────────────────────────────────────────────────────────────

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
