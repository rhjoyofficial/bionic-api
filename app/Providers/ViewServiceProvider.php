<?php

namespace App\Providers;

use App\Domains\Category\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view) {

            $globalCategories = Cache::remember('global_categories', now()->addHours(6), function () {
                return Category::query()->active()->ordered()->get();
            });

            $view->with('globalCategories', $globalCategories);
        });
    }
}
