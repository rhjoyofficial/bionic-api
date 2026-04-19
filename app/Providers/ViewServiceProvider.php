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
        // Bind global categories only to store-facing and shared layout views.
        // Using '*' would fire on every admin view, sub-partial, and email —
        // causing unnecessary cache lookups on requests where the data is unused.
        View::composer(
            ['store.*', 'layouts.*', 'components.*', 'auth.*', 'customer.*', 'pages.*'],
            function ($view) {
                // Shared cache key — also invalidated by CategoryObserver.
                $view->with('globalCategories', Cache::remember(
                    'categories:active',
                    now()->addHours(24),
                    fn () => Category::query()->active()->ordered()->get()
                ));
            }
        );
    }
}
