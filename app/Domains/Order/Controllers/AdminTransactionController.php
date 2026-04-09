<?php

namespace App\Domains\Order\Controllers;

use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderTransaction;
use App\Domains\Order\Requests\StoreTransactionRequest;
use App\Domains\Order\Requests\UpdatePaymentStatusRequest;
use App\Domains\Order\Resources\TransactionResource;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminTransactionController extends Controller
{
    // ── Revenue Summary & Chart Data ──────────────────────────────────────────

    public function summary(): JsonResponse
    {
        try {
            // Core revenue totals
            $totalRevenue = (float) Order::where('payment_status', 'paid')->sum('grand_total');
            $todayRevenue = (float) Order::where('payment_status', 'paid')
                ->whereDate('placed_at', today())->sum('grand_total');
            $weekRevenue  = (float) Order::where('payment_status', 'paid')
                ->whereBetween('placed_at', [now()->startOfWeek(), now()])->sum('grand_total');
            $monthRevenue = (float) Order::where('payment_status', 'paid')
                ->whereBetween('placed_at', [now()->startOfMonth(), now()])->sum('grand_total');

            // Refunds & net
            $totalRefunds = (float) OrderTransaction::where('type', 'refund')->sum('amount');
            $netRevenue   = $totalRevenue - $totalRefunds;

            // Unpaid exposure
            $unpaidCount  = Order::where('payment_status', 'unpaid')->count();
            $unpaidTotal  = (float) Order::where('payment_status', 'unpaid')->sum('grand_total');
            $failedCount  = Order::where('payment_status', 'failed')->count();

            // Transactions by type
            $byType = OrderTransaction::select(
                'type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )->groupBy('type')->get()
             ->mapWithKeys(fn($r) => [$r->type => [
                 'count' => (int) $r->count,
                 'total' => (float) $r->total,
             ]]);

            // Revenue split by payment method
            $byMethod = Order::where('payment_status', 'paid')
                ->select(
                    'payment_method',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(grand_total) as total')
                )->groupBy('payment_method')->get()
                ->map(fn($r) => [
                    'method' => $r->payment_method,
                    'count'  => (int) $r->count,
                    'total'  => (float) $r->total,
                ])->values();

            // Daily revenue – last 30 days (fill missing dates with 0)
            $rawDaily = Order::where('payment_status', 'paid')
                ->where('placed_at', '>=', now()->subDays(29)->startOfDay())
                ->select(
                    DB::raw('DATE(placed_at) as date'),
                    DB::raw('SUM(grand_total) as total'),
                    DB::raw('COUNT(*) as count')
                )->groupBy('date')->orderBy('date')->get()
                ->keyBy('date');

            $dailyRevenue = collect();
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $row  = $rawDaily->get($date);
                $dailyRevenue->push([
                    'date'  => $date,
                    'total' => $row ? (float) $row->total : 0,
                    'count' => $row ? (int)   $row->count : 0,
                ]);
            }

            // Reconciliation discrepancy count
            $discrepancies = $this->getDiscrepancyCount();

            return ApiResponse::success([
                'totals' => [
                    'revenue'        => $totalRevenue,
                    'today'          => $todayRevenue,
                    'this_week'      => $weekRevenue,
                    'this_month'     => $monthRevenue,
                    'total_refunds'  => $totalRefunds,
                    'net_revenue'    => $netRevenue,
                    'unpaid_count'   => $unpaidCount,
                    'unpaid_total'   => $unpaidTotal,
                    'failed_count'   => $failedCount,
                ],
                'by_type'      => $byType,
                'by_method'    => $byMethod,
                'daily'        => $dailyRevenue->values(),
                'discrepancies'=> $discrepancies,
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to load transaction summary');
        }
    }

    // ── Global Transaction Ledger ─────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $query = OrderTransaction::with(['order:id,order_number,customer_name,payment_status,payment_method'])
                ->orderByDesc('created_at');

            if ($type = $request->input('type')) {
                $query->where('type', $type);
            }

            if ($from = $request->input('from')) {
                $query->whereDate('created_at', '>=', $from);
            }

            if ($to = $request->input('to')) {
                $query->whereDate('created_at', '<=', $to);
            }

            if ($q = $request->input('q')) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('description', 'like', "%{$q}%")
                        ->orWhereHas('order', fn($o) => $o->where('order_number', 'like', "%{$q}%")
                            ->orWhere('customer_name', 'like', "%{$q}%"));
                });
            }

            $transactions = $query->paginate(25);

            return ApiResponse::success([
                'data' => TransactionResource::collection($transactions->items()),
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page'    => $transactions->lastPage(),
                    'per_page'     => $transactions->perPage(),
                    'total'        => $transactions->total(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to load transaction ledger');
        }
    }

    // ── Per-Order Transactions ────────────────────────────────────────────────

    public function orderTransactions(Order $order): JsonResponse
    {
        try {
            $transactions = $order->transactions()->get();

            $charged   = (float) $transactions->where('type', 'charge')->sum('amount');
            $refunded  = (float) $transactions->where('type', 'refund')->sum('amount');
            $discounts = (float) $transactions->whereIn('type', ['discount', 'coupon'])->sum('amount');
            $shipping  = (float) $transactions->where('type', 'shipping')->sum('amount');
            $commission= (float) $transactions->where('type', 'commission')->sum('amount');

            // Reconciliation health for this order
            $reconciled = match (true) {
                $order->payment_status === 'paid' && $charged >= $order->grand_total => 'ok',
                $order->payment_status === 'paid' && $charged === 0.0                => 'missing_charge',
                $order->payment_status === 'paid' && $charged < $order->grand_total  => 'underpaid',
                $order->payment_status === 'unpaid' && $charged > 0                  => 'unrecorded_payment',
                default                                                               => 'ok',
            };

            return ApiResponse::success([
                'order' => [
                    'id'                     => $order->id,
                    'order_number'           => $order->order_number,
                    'customer_name'          => $order->customer_name,
                    'grand_total'            => (float) $order->grand_total,
                    'payment_method'         => $order->payment_method,
                    'payment_status'         => $order->payment_status,
                    'gateway_transaction_id' => $order->gateway_transaction_id,
                    'placed_at'              => $order->placed_at?->toISOString(),
                ],
                'transactions' => TransactionResource::collection($transactions),
                'summary' => [
                    'charged'    => $charged,
                    'refunded'   => $refunded,
                    'discounts'  => $discounts,
                    'shipping'   => $shipping,
                    'commission' => $commission,
                    'net'        => $charged - $refunded,
                ],
                'reconciliation_status' => $reconciled,
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to load order transactions');
        }
    }

    // ── Add Manual Transaction ────────────────────────────────────────────────

    public function store(StoreTransactionRequest $request, Order $order): JsonResponse
    {
        try {
            $data = array_merge($request->validated(), [
                'order_id' => $order->id,
                'metadata' => array_merge($request->input('metadata', []), [
                    'recorded_by' => auth()->id(),
                    'manual'      => true,
                ]),
            ]);

            $transaction = OrderTransaction::create($data);
            $transaction->load('order:id,order_number');

            return ApiResponse::success(
                new TransactionResource($transaction),
                'Transaction recorded successfully',
                201
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to record transaction');
        }
    }

    // ── Update Payment Status ─────────────────────────────────────────────────

    public function updatePaymentStatus(UpdatePaymentStatusRequest $request, Order $order): JsonResponse
    {
        try {
            $data            = $request->validated();
            $previousStatus  = $order->payment_status;
            $newStatus       = $data['payment_status'];

            $updateData = ['payment_status' => $newStatus];
            if (! empty($data['gateway_transaction_id'])) {
                $updateData['gateway_transaction_id'] = $data['gateway_transaction_id'];
            }
            $order->update($updateData);

            // When manually marking as paid, auto-create a charge transaction if none exists
            if ($newStatus === 'paid' && $previousStatus !== 'paid') {
                $hasCharge = $order->transactions()->where('type', 'charge')->exists();
                if (! $hasCharge) {
                    OrderTransaction::create([
                        'order_id'    => $order->id,
                        'type'        => 'charge',
                        'amount'      => $order->grand_total,
                        'description' => $data['note'] ?? 'Manual payment confirmation',
                        'metadata'    => [
                            'manual'          => true,
                            'confirmed_by'    => auth()->id(),
                            'previous_status' => $previousStatus,
                            'gateway_ref'     => $data['gateway_transaction_id'] ?? null,
                        ],
                    ]);
                }
            }

            return ApiResponse::success([
                'id'                     => $order->id,
                'payment_status'         => $order->payment_status,
                'gateway_transaction_id' => $order->gateway_transaction_id,
            ], 'Payment status updated successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to update payment status');
        }
    }

    // ── Payment Reconciliation ────────────────────────────────────────────────

    public function reconciliation(Request $request): JsonResponse
    {
        try {
            // Build charged/refunded aggregates per order
            $txSub = DB::table('order_transactions')
                ->select(
                    'order_id',
                    DB::raw("SUM(CASE WHEN type = 'charge' THEN amount ELSE 0 END) as charged"),
                    DB::raw("SUM(CASE WHEN type = 'refund' THEN amount ELSE 0 END) as refunded")
                )
                ->groupBy('order_id');

            $query = DB::table('orders')
                ->leftJoinSub($txSub, 'tx', 'tx.order_id', '=', 'orders.id')
                ->leftJoin('users', 'users.id', '=', 'orders.user_id')
                ->select(
                    'orders.id',
                    'orders.order_number',
                    'orders.customer_name',
                    'orders.customer_email',
                    'orders.grand_total',
                    'orders.payment_method',
                    'orders.payment_status',
                    'orders.placed_at',
                    'orders.gateway_transaction_id',
                    'users.email as registered_email',
                    DB::raw('COALESCE(tx.charged, 0) as charged'),
                    DB::raw('COALESCE(tx.refunded, 0) as refunded'),
                    DB::raw("ROUND(COALESCE(tx.charged, 0) - COALESCE(tx.refunded, 0), 2) as net_received"),
                    DB::raw("
                        CASE
                            WHEN orders.payment_status = 'paid'   AND COALESCE(tx.charged, 0) = 0
                                THEN 'missing_charge'
                            WHEN orders.payment_status = 'paid'   AND COALESCE(tx.charged, 0) < orders.grand_total
                                THEN 'underpaid'
                            WHEN orders.payment_status = 'unpaid' AND COALESCE(tx.charged, 0) > 0
                                THEN 'unrecorded_payment'
                            WHEN orders.payment_status = 'failed'
                                THEN 'payment_failed'
                            ELSE 'ok'
                        END AS issue_type
                    ")
                )
                ->where(function ($q) {
                    $q->where(function ($s) {
                          $s->where('orders.payment_status', 'paid')
                            ->whereRaw('COALESCE(tx.charged, 0) < orders.grand_total');
                      })
                      ->orWhere(function ($s) {
                          $s->where('orders.payment_status', 'unpaid')
                            ->whereRaw('COALESCE(tx.charged, 0) > 0');
                      })
                      ->orWhere('orders.payment_status', 'failed');
                })
                ->orderByDesc('orders.placed_at');

            // Filter by issue type
            if ($issueType = $request->input('issue_type')) {
                $query->where(DB::raw("
                    CASE
                        WHEN orders.payment_status = 'paid'   AND COALESCE(tx.charged, 0) = 0          THEN 'missing_charge'
                        WHEN orders.payment_status = 'paid'   AND COALESCE(tx.charged, 0) < orders.grand_total THEN 'underpaid'
                        WHEN orders.payment_status = 'unpaid' AND COALESCE(tx.charged, 0) > 0          THEN 'unrecorded_payment'
                        WHEN orders.payment_status = 'failed'                                          THEN 'payment_failed'
                        ELSE 'ok'
                    END
                "), $issueType);
            }

            // Filter by payment method
            if ($method = $request->input('method')) {
                $query->where('orders.payment_method', $method);
            }

            $results = $query->paginate(20);

            // Issue type counts for filter badges
            $counts = DB::table('orders')
                ->leftJoinSub($txSub, 'tx', 'tx.order_id', '=', 'orders.id')
                ->selectRaw("
                    SUM(CASE WHEN orders.payment_status = 'paid'   AND COALESCE(tx.charged,0) = 0                   THEN 1 ELSE 0 END) as missing_charge,
                    SUM(CASE WHEN orders.payment_status = 'paid'   AND COALESCE(tx.charged,0) > 0 AND COALESCE(tx.charged,0) < orders.grand_total THEN 1 ELSE 0 END) as underpaid,
                    SUM(CASE WHEN orders.payment_status = 'unpaid' AND COALESCE(tx.charged,0) > 0                   THEN 1 ELSE 0 END) as unrecorded_payment,
                    SUM(CASE WHEN orders.payment_status = 'failed'                                                   THEN 1 ELSE 0 END) as payment_failed
                ")->first();

            return ApiResponse::success([
                'data'   => $results->items(),
                'meta'   => [
                    'current_page' => $results->currentPage(),
                    'last_page'    => $results->lastPage(),
                    'per_page'     => $results->perPage(),
                    'total'        => $results->total(),
                ],
                'counts' => [
                    'missing_charge'     => (int) ($counts->missing_charge     ?? 0),
                    'underpaid'          => (int) ($counts->underpaid          ?? 0),
                    'unrecorded_payment' => (int) ($counts->unrecorded_payment ?? 0),
                    'payment_failed'     => (int) ($counts->payment_failed     ?? 0),
                ],
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to load reconciliation data');
        }
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private function getDiscrepancyCount(): int
    {
        try {
            $txSub = DB::table('order_transactions')
                ->select(
                    'order_id',
                    DB::raw("SUM(CASE WHEN type = 'charge' THEN amount ELSE 0 END) as charged")
                )->groupBy('order_id');

            return DB::table('orders')
                ->leftJoinSub($txSub, 'tx', 'tx.order_id', '=', 'orders.id')
                ->where(function ($q) {
                    $q->where(function ($s) {
                          $s->where('payment_status', 'paid')
                            ->whereRaw('COALESCE(tx.charged, 0) < grand_total');
                      })
                      ->orWhere(function ($s) {
                          $s->where('payment_status', 'unpaid')
                            ->whereRaw('COALESCE(tx.charged, 0) > 0');
                      })
                      ->orWhere('payment_status', 'failed');
                })->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function handleError(Exception $e, string $msg, int $code = 500): JsonResponse
    {
        Log::error("{$msg}: {$e->getMessage()}", [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return ApiResponse::error(
            $msg,
            config('app.debug') ? $e->getMessage() : null,
            $code
        );
    }
}
