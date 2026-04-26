<?php

namespace App\Domains\Admin\Services;

use App\Domains\Order\Models\Order;
use App\Domains\Product\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Aggregates real-time stats for the admin dashboard.
 *
 * Every method returns a simple array/scalar — the controller decides
 * what to pass to the view. No caching here; queries are lightweight
 * index scans and the dashboard isn't hit at storefront scale.
 */
class DashboardStatsService
{
    /**
     * Top-level KPI cards: revenue, orders, customers, products.
     */
    public function kpiCards(): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        return [
            'revenue_today'    => Order::whereDate('placed_at', $today)->whereNotIn('order_status', ['cancelled', 'returned'])->sum('grand_total'),
            'revenue_month'    => Order::where('placed_at', '>=', $startOfMonth)->whereNotIn('order_status', ['cancelled', 'returned'])->sum('grand_total'),
            'orders_today'     => Order::whereDate('placed_at', $today)->count(),
            'orders_month'     => Order::where('placed_at', '>=', $startOfMonth)->count(),
            'customers_total'  => User::role('Customer')->count(),
            'products_active'  => Product::where('is_active', true)->count(),
        ];
    }

    /**
     * Orders grouped by status for the status breakdown widget.
     */
    public function ordersByStatus(): array
    {
        return Order::select('order_status', DB::raw('COUNT(*) as count'))
            ->groupBy('order_status')
            ->pluck('count', 'order_status')
            ->toArray();
    }

    /**
     * Recent orders (last 10) for the dashboard table.
     */
    public function recentOrders(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Order::with('user:id,name,phone')
            ->latest('placed_at')
            ->limit($limit)
            ->get(['id', 'order_number', 'user_id', 'customer_name', 'grand_total', 'order_status', 'payment_method', 'placed_at']);
    }

    /**
     * Daily revenue for the last N days (for a chart widget).
     */
    public function dailyRevenue(int $days = 14): array
    {
        $from = Carbon::today()->subDays($days - 1);

        $rows = Order::where('placed_at', '>=', $from)
            ->select(
                DB::raw('DATE(placed_at) as date'),
                DB::raw('SUM(grand_total) as revenue'),
                DB::raw('COUNT(*) as orders'),
            )
            ->groupBy(DB::raw('DATE(placed_at)'))
            ->orderBy('date')
            ->get();

        // Fill in zero-days so the chart has a continuous x-axis.
        $filled = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $from->copy()->addDays($i)->toDateString();
            $row  = $rows->firstWhere('date', $date);
            $filled[] = [
                'date'    => $date,
                'revenue' => $row ? (float) $row->revenue : 0,
                'orders'  => $row ? (int) $row->orders : 0,
            ];
        }

        return $filled;
    }

    /**
     * Orders that have been pending for more than 48 hours (late to ship).
     */
    public function lateOrders(): int
    {
        return Order::where('order_status', 'pending')
            ->where('placed_at', '<', Carbon::now()->subHours(48))
            ->count();
    }
}
