<?php

namespace App\Domains\Admin\Controllers;

use App\Domains\Admin\Services\DashboardStatsService;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function __construct(
        private DashboardStatsService $stats,
    ) {}

    public function __invoke()
    {
        return view('admin.dashboard', [
            'kpi'           => $this->stats->kpiCards(),
            'ordersByStatus' => $this->stats->ordersByStatus(),
            'recentOrders'  => $this->stats->recentOrders(),
            'dailyRevenue'  => $this->stats->dailyRevenue(),
            'lateOrders'    => $this->stats->lateOrders(),
        ]);
    }
}
