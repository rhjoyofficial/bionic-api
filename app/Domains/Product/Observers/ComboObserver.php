<?php

namespace App\Domains\Product\Observers;

use App\Domains\Product\Models\Combo;
use Illuminate\Support\Facades\Cache;

class ComboObserver
{
    private function clearCache(Combo $combo): void
    {
        Cache::forget('home:combos');
        Cache::forget("landing:data:combo:{$combo->id}");
    }

    public function created(Combo $combo): void { $this->clearCache($combo); }
    public function updated(Combo $combo): void { $this->clearCache($combo); }
    public function deleted(Combo $combo): void { $this->clearCache($combo); }
}
