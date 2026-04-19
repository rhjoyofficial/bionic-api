<?php

namespace App\Domains\Store\Observers;

use App\Domains\Store\Models\HeroBanner;
use Illuminate\Support\Facades\Cache;

class HeroBannerObserver
{
    private function clearCache(): void
    {
        Cache::forget('home:hero_banners');
    }

    public function created(HeroBanner $heroBanner): void { $this->clearCache(); }
    public function updated(HeroBanner $heroBanner): void { $this->clearCache(); }
    public function deleted(HeroBanner $heroBanner): void { $this->clearCache(); }
}
