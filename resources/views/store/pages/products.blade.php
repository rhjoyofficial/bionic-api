@extends('layouts.app')

@section('title', 'Healthy Products')

@section('content')
    <div class="max-w-8xl mx-auto px-4 py-6 md:py-10">
        {{-- Breadcrumbs --}}
        <nav class="flex text-gray-500 text-xs md:text-sm mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li><a href="/" class="hover:text-primary">Home</a></li>
                <li><span class="mx-2">›</span></li>
                <li class="text-gray-800 font-medium">Shop</li>
            </ol>
        </nav>

        <header class="mb-8">
            <h1 class="text-2xl md:text-4xl font-bold text-gray-900 mb-2">
                {{ $selectedCategory ? $selectedCategory->name . ' Products' : 'Shop Pure Organic Products' }}
            </h1>
            <p class="text-gray-600 text-sm md:text-base">
                {{ $searchQuery ? 'Search results for "' . $searchQuery . '"' : 'Carefully sourced, naturally processed, and quality tested.' }}
            </p>
        </header>

        <div class="flex flex-col lg:flex-row gap-8">

            {{-- LEFT SIDEBAR: FILTERS --}}
            <aside class="w-full lg:w-64 shrink-0 space-y-8">
                {{-- Category Filter --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 border-b pb-2">Category</h3>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="text-sm text-gray-700 group-hover:text-primary transition-colors">All
                                Categories</span>
                        </label>
                        {{-- Loop through your globalCategories --}}
                        @foreach ($globalCategories as $category)
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                    class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"
                                    {{ $selectedCategory?->id === $category->id ? 'checked' : '' }}>
                                <span class="text-sm text-gray-600 group-hover:text-primary transition-colors">
                                    {{ $category->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Price Range Filter --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 border-b pb-2">Price Range</h3>
                    <input type="range" min="500" max="4000"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary">
                    <div class="flex justify-between mt-2 text-xs font-medium text-gray-500">
                        <span>৳500</span>
                        <span>৳4000</span>
                    </div>
                </div>

                {{-- Availability --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 border-b pb-2">Availability</h3>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="text-sm text-gray-600 group-hover:text-primary transition-colors">In Stock Only</span>
                    </label>
                </div>
            </aside>

            {{-- MAIN CONTENT: PRODUCTS --}}
            <main class="flex-1">
                {{-- Toolbar --}}
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                    <p class="text-gray-600 text-sm">Showing <span
                            class="font-bold text-gray-900">{{ $products->total() }}</span> Products Found</p>

                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 whitespace-nowrap">Sort by:</span>
                        <select
                            class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block p-2.5 outline-none">
                            <option selected>Best Selling</option>
                            <option value="new">Newest First</option>
                            <option value="low">Price: Low to High</option>
                            <option value="high">Price: High to Low</option>
                        </select>
                    </div>
                </div>

                {{-- Product Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-12 flex justify-center">
                    {{ $products->links() }}
                </div>
            </main>
        </div>
    </div>

@endsection
