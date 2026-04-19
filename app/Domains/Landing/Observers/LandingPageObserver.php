<?php

namespace App\Domains\Landing\Observers;

use App\Domains\Landing\Models\LandingPage;
use Illuminate\Support\Facades\Cache;

class LandingPageObserver
{
    private function clearCache(LandingPage $landingPage): void
    {
        Cache::forget("landing:meta:{$landingPage->slug}");
        Cache::forget("landing:data:{$landingPage->slug}:product");
        Cache::forget("landing:data:{$landingPage->slug}:combo");
        Cache::forget("landing:data:{$landingPage->slug}:sales");
    }

    public function updated(LandingPage $landingPage): void { $this->clearCache($landingPage); }
    public function deleted(LandingPage $landingPage): void { $this->clearCache($landingPage); }
}
