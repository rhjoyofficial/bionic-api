<?php

namespace App\Domains\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AdminActivityLogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = Activity::query()
            ->with('causer')
            ->latest();

        if ($request->filled('log')) {
            $query->where('log_name', $request->query('log'));
        }

        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', $term)
                    ->orWhere('log_name', 'like', $term);
            });
        }

        $activities = $query->paginate(25)->withQueryString();

        return view('admin.activity-log.index', [
            'activities' => $activities,
        ]);
    }
}
