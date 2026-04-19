<?php

namespace App\Domains\Category\Observers;

use App\Domains\Category\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    private function clearCache(): void
    {
        Cache::forget('categories:active');
        Cache::forget('api:categories:active');
    }

    public function created(Category $category): void { $this->clearCache(); }
    public function updated(Category $category): void { $this->clearCache(); }
    public function deleted(Category $category): void { $this->clearCache(); }
}
