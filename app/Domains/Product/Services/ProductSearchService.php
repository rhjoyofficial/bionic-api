<?php

namespace App\Domains\Product\Services;

use App\Domains\Product\Models\Product;

class ProductSearchService
{

    public function search(array $filters)
    {
        $query = Product::query()
            ->with(['variants', 'category'])
            ->where('is_active', true);

        // Use collect() for cleaner filter handling
        $filters = collect($filters);

        // Keyword search
        if ($filters->get('q')) {
            $query->where(function ($q) use ($filters) {
                $searchTerm = "%{$filters->get('q')}%";
                $q->where('name', 'LIKE', $searchTerm)
                    ->orWhere('short_description', 'LIKE', $searchTerm);
            });
        }

        // Category filter
        if ($filters->get('category_id')) {
            $query->where('category_id', $filters->get('category_id'));
        }

        // Price filtering
        if ($filters->has('min_price')) {
            $query->where('base_price', '>=', $filters->get('min_price'));
        }

        if ($filters->has('max_price')) {
            $query->where('base_price', '<=', $filters->get('max_price'));
        }

        // Featured
        if ($filters->get('featured')) {
            $query->where('is_featured', true);
        }

        // Sorting - match is great here
        $sort = $filters->get('sort', 'latest');
        match ($sort) {
            'price_asc'  => $query->orderBy('base_price', 'asc'),
            'price_desc' => $query->orderBy('base_price', 'desc'),
            'latest'     => $query->latest(),
            default      => $query->latest(),
        };

        return $query->paginate(12)->withQueryString();
        // .withQueryString() ensures pagination links include your search terms
    }
}
