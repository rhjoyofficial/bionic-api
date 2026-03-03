<?php

namespace App\Domains\Category\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Category\Models\Category;
use App\Domains\Category\Resources\CategoryResource;

class PublicCategoryController extends Controller
{
    public function index()
    {
        return CategoryResource::collection(
            Category::where('is_active', true)->orderBy('sort_order')->get()
        );
    }
}
