<?php

namespace App\Domains\Store\Controllers;

use App\Domains\Product\Models\Combo;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class ComboPageController extends Controller
{
    public function index(): View
    {
        $combos = Combo::query()
            ->where('is_active', true)
            ->with(['items.variant.product'])
            ->latest()
            ->paginate(12);

        return view('store.pages.combos', [
            'combos' => $combos,
        ]);
    }

    public function show(string $slug): View
    {
        /** @var Combo $combo */
        $combo = Cache::remember("combo:page:{$slug}", now()->addHours(2), fn () =>
            Combo::query()
                ->with([
                    'items.variant.product',
                    'items.variant.tierPrices',
                ])
                ->active()
                ->where('slug', $slug)
                ->firstOrFail()
        );

        // Fetch 4 other active combos (excluding current) as recommendations.
        // Prefer featured; fall back to latest. Keep it small for performance.
        $relatedCombos = Cache::remember("combo:related:{$combo->id}", now()->addHours(2), fn () =>
            Combo::query()
                ->active()
                ->with(['items.variant.product'])
                ->where('id', '!=', $combo->id)
                ->orderByDesc('is_featured')
                ->orderByDesc('created_at')
                ->limit(6)
                ->get()
        );

        return view('store.combo', [
            'combo'         => $combo,
            'relatedCombos' => $relatedCombos,
        ]);
    }
}

