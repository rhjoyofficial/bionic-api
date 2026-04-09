<?php

namespace App\Domains\Store\Controllers;

use App\Domains\Product\Models\Combo;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

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
}
