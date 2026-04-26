@extends('layouts.app')

@section('title', $selectedCategory ? $selectedCategory->name . ' Products' : 'Shop All Products')
@section('meta_description', $selectedCategory
    ? 'Browse ' . $selectedCategory->name . ' products — carefully sourced, naturally processed, and quality tested.'
    : 'Shop our full range of organic products — carefully sourced, naturally processed, and quality tested.')

@section('content')
<div class="max-w-8xl mx-auto px-4 py-6 md:py-10">

    {{-- Breadcrumbs --}}
    <nav class="flex text-gray-500 text-xs md:text-sm mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li><a href="/" class="hover:text-primary transition-colors">Home</a></li>
            <li><span class="mx-2">›</span></li>
            @if ($selectedCategory)
                <li><a href="{{ route('products.index') }}" class="hover:text-primary transition-colors">Shop</a></li>
                <li><span class="mx-2">›</span></li>
                <li class="text-gray-800 font-medium">{{ $selectedCategory->name }}</li>
            @else
                <li class="text-gray-800 font-medium">Shop</li>
            @endif
        </ol>
    </nav>

    {{-- Page Header --}}
    <header class="mb-8">
        <h1 class="text-2xl md:text-4xl font-bold text-gray-900 mb-2">
            {{ $selectedCategory ? $selectedCategory->name . ' Products' : 'Shop Pure Organic Products' }}
        </h1>
        <p class="text-gray-600 text-sm md:text-base">
            @if ($searchQuery)
                Search results for <span class="font-semibold text-gray-800">"{{ $searchQuery }}"</span>
            @else
                Carefully sourced, naturally processed, and quality tested.
            @endif
        </p>
    </header>

    {{-- Active Filter Tags Bar --}}
    <div id="activeFilterTags" class="hidden flex flex-wrap gap-2 mb-6">
        {{-- Populated by CatalogFilter JS --}}
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- ═══════════════════════════════════════
             LEFT SIDEBAR: FILTERS (Desktop)
        ════════════════════════════════════════ --}}
        <aside id="catalogFilterPanel" class="hidden lg:block w-64 shrink-0 space-y-5">

            {{-- Category Filter --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
                    </svg>
                    Category
                </h3>
                <div class="space-y-2.5">

                    {{-- All Categories --}}
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="radio"
                               name="catalogCategory"
                               data-filter-category="all"
                               class="w-4 h-4 accent-primary border-gray-300"
                               {{ ! request('category') ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700 group-hover:text-primary transition-colors font-medium">
                            All Categories
                        </span>
                    </label>

                    @foreach ($globalCategories as $cat)
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio"
                                   name="catalogCategory"
                                   data-filter-category="{{ $cat->slug }}"
                                   class="w-4 h-4 accent-primary border-gray-300"
                                   {{ request('category') === $cat->slug || $selectedCategory?->id === $cat->id ? 'checked' : '' }}>
                            <span class="text-sm text-gray-600 group-hover:text-primary transition-colors">
                                {{ $cat->name }}
                            </span>
                        </label>
                    @endforeach

                </div>
            </div>

            {{-- Price Range Filter --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Price Range
                </h3>

                {{-- Live display of selected range --}}
                <div class="flex justify-between items-center mb-4">
                    <span id="priceMinDisplay" class="text-sm font-bold text-gray-800 font-bengali bg-gray-50 px-2.5 py-1 rounded-lg">
                        ৳{{ number_format(request('price_min', $priceRange['min'])) }}
                    </span>
                    <span class="text-xs text-gray-400 font-medium">to</span>
                    <span id="priceMaxDisplay" class="text-sm font-bold text-gray-800 font-bengali bg-gray-50 px-2.5 py-1 rounded-lg">
                        ৳{{ number_format(request('price_max', $priceRange['max'])) }}
                    </span>
                </div>

                {{-- Dual Range Slider --}}
                <div class="catalog-range-wrapper mb-5">
                    <div class="range-track"></div>
                    <div id="rangeHighlight" class="range-highlight" style="left:0%;right:0%"></div>

                    <input type="range"
                           id="priceMin"
                           class="range-input"
                           min="{{ $priceRange['min'] }}"
                           max="{{ $priceRange['max'] }}"
                           step="50"
                           value="{{ request('price_min', $priceRange['min']) }}">

                    <input type="range"
                           id="priceMax"
                           class="range-input"
                           min="{{ $priceRange['min'] }}"
                           max="{{ $priceRange['max'] }}"
                           step="50"
                           value="{{ request('price_max', $priceRange['max']) }}">
                </div>

                <button id="applyPrice"
                        class="w-full py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 active:scale-95 transition-all duration-150 cursor-pointer">
                    Apply Price
                </button>
            </div>

            {{-- In Stock Filter --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Availability
                </h3>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox"
                           id="inStockFilter"
                           class="w-4 h-4 rounded accent-primary border-gray-300"
                           {{ request('in_stock') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-600 group-hover:text-primary transition-colors">In Stock Only</span>
                </label>
            </div>

            {{-- Clear All Filters --}}
            @if (request()->hasAny(['category', 'price_min', 'price_max', 'in_stock', 'sort']))
                <button id="clearFilters"
                        class="w-full py-2.5 text-sm font-semibold text-red-600 border border-red-200 rounded-xl hover:bg-red-50 transition-colors cursor-pointer">
                    ✕ Clear All Filters
                </button>
            @else
                <button id="clearFilters"
                        class="w-full py-2.5 text-sm font-semibold text-gray-400 border border-gray-100 rounded-xl cursor-pointer">
                    Clear Filters
                </button>
            @endif

        </aside>

        {{-- ═══════════════════════════════════════
             MAIN CONTENT: PRODUCTS
        ════════════════════════════════════════ --}}
        <main class="flex-1 min-w-0">

            {{-- Toolbar --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-gray-600 text-sm">
                    Showing <span class="font-bold text-gray-900">{{ $products->total() }}</span>
                    {{ Str::plural('product', $products->total()) }}
                    @if ($products->total() !== $products->count())
                        <span class="text-gray-400">(page {{ $products->currentPage() }} of {{ $products->lastPage() }})</span>
                    @endif
                </p>

                <div class="flex items-center gap-2">
                    {{-- Mobile filter button (moved into toolbar for UX) --}}
                    <button id="openFilters"
                            class="lg:hidden inline-flex items-center gap-2 bg-gray-50 border border-gray-200 text-gray-700 text-sm font-medium px-3 py-2.5 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer relative">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        Filters
                        <span id="filterBadge"
                              class="{{ request()->hasAny(['category','price_min','price_max','in_stock','sort']) ? '' : 'hidden' }} absolute -top-1.5 -right-1.5 bg-primary text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">
                            {{ collect(['category','price_min','price_max','in_stock','sort'])->filter(fn($k) => request()->filled($k))->count() }}
                        </span>
                    </button>

                    <span class="text-sm text-gray-500 whitespace-nowrap">Sort by:</span>
                    <select id="sortSelect"
                            class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary block px-3 py-2.5 outline-none cursor-pointer transition-all">
                        <option value="latest"     {{ request('sort', 'latest') === 'latest'     ? 'selected' : '' }}>Newest First</option>
                        <option value="price_asc"  {{ request('sort') === 'price_asc'            ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc'           ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </div>
            </div>

            {{-- Product Grid --}}
            @if ($products->isEmpty())
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h2 class="text-xl font-bold text-gray-700 mb-2">No products found</h2>
                    <p class="text-gray-500 text-sm mb-6">Try adjusting or clearing your filters.</p>
                    <a href="{{ url()->current() }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors">
                        Clear Filters
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            @endif

            {{-- Pagination --}}
            @if ($products->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $products->links() }}
                </div>
            @endif

        </main>
    </div>
</div>

{{-- ═══════════════════════════════════════
     MOBILE FILTER DRAWER
════════════════════════════════════════ --}}
<div id="filterDrawer" class="lg:hidden fixed inset-0 z-50 hidden">

    {{-- Overlay --}}
    <div id="filterOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    {{-- Drawer Panel --}}
    <aside class="-translate-x-full absolute left-0 top-0 bottom-0 w-80 max-w-[90vw] bg-white overflow-y-auto shadow-2xl flex flex-col">

        {{-- Drawer Header --}}
        <div class="flex items-center justify-between p-5 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h2 class="font-bold text-gray-900 text-lg">Filters</h2>
            <button id="closeFiltersBtn" class="p-2 hover:bg-gray-100 rounded-xl transition-colors cursor-pointer"
                    onclick="window.CatalogFilterInstance?.closeDrawer()">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 p-5 space-y-6 overflow-y-auto">

            {{-- Mobile: Category Filter --}}
            <div>
                <h3 class="font-bold text-gray-900 mb-3 text-sm uppercase tracking-wider">Category</h3>
                <div class="space-y-2.5">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="radio"
                               name="catalogCategoryMobile"
                               data-filter-category="all"
                               class="w-4 h-4 accent-primary"
                               {{ ! request('category') ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700 group-hover:text-primary transition-colors font-medium">All Categories</span>
                    </label>
                    @foreach ($globalCategories as $cat)
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio"
                                   name="catalogCategoryMobile"
                                   data-filter-category="{{ $cat->slug }}"
                                   class="w-4 h-4 accent-primary"
                                   {{ request('category') === $cat->slug || $selectedCategory?->id === $cat->id ? 'checked' : '' }}>
                            <span class="text-sm text-gray-600 group-hover:text-primary transition-colors">{{ $cat->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Mobile: Price Range --}}
            <div>
                <h3 class="font-bold text-gray-900 mb-3 text-sm uppercase tracking-wider">Price Range</h3>
                <div class="flex justify-between items-center mb-4">
                    <span id="priceMinDisplayMobile" class="text-sm font-bold text-gray-800 font-bengali bg-gray-50 px-2.5 py-1 rounded-lg">
                        ৳{{ number_format(request('price_min', $priceRange['min'])) }}
                    </span>
                    <span class="text-xs text-gray-400">to</span>
                    <span id="priceMaxDisplayMobile" class="text-sm font-bold text-gray-800 font-bengali bg-gray-50 px-2.5 py-1 rounded-lg">
                        ৳{{ number_format(request('price_max', $priceRange['max'])) }}
                    </span>
                </div>
                <div class="catalog-range-wrapper mb-5">
                    <div class="range-track"></div>
                    <div id="rangeHighlightMobile" class="range-highlight" style="left:0%;right:0%"></div>
                    <input type="range" data-mobile-price="min"
                           class="range-input"
                           min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}" step="50"
                           value="{{ request('price_min', $priceRange['min']) }}">
                    <input type="range" data-mobile-price="max"
                           class="range-input"
                           min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}" step="50"
                           value="{{ request('price_max', $priceRange['max']) }}">
                </div>
            </div>

            {{-- Mobile: In Stock --}}
            <div>
                <h3 class="font-bold text-gray-900 mb-3 text-sm uppercase tracking-wider">Availability</h3>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox"
                           data-filter-instock-mobile
                           class="w-4 h-4 rounded accent-primary"
                           {{ request('in_stock') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-600 group-hover:text-primary transition-colors">In Stock Only</span>
                </label>
            </div>

            {{-- Mobile: Sort --}}
            <div>
                <h3 class="font-bold text-gray-900 mb-3 text-sm uppercase tracking-wider">Sort By</h3>
                <select data-mobile-sort
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl px-3 py-2.5 outline-none cursor-pointer">
                    <option value="latest"     {{ request('sort', 'latest') === 'latest'    ? 'selected' : '' }}>Newest First</option>
                    <option value="price_asc"  {{ request('sort') === 'price_asc'           ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ request('sort') === 'price_desc'          ? 'selected' : '' }}>Price: High to Low</option>
                </select>
            </div>

        </div>

        {{-- Drawer Footer: Apply + Clear --}}
        <div class="p-5 border-t border-gray-100 space-y-3 sticky bottom-0 bg-white">
            <button id="applyMobileFilters"
                    class="w-full py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary/90 active:scale-95 transition-all cursor-pointer"
                    onclick="window.CatalogFilterInstance?.applyMobileFilters()">
                Apply Filters
            </button>
            @if (request()->hasAny(['category', 'price_min', 'price_max', 'in_stock', 'sort']))
                <button id="clearFilters" {{-- shares the same ID so JS can find it --}}
                        class="w-full py-2.5 text-sm font-semibold text-red-600 border border-red-200 rounded-xl hover:bg-red-50 transition-colors cursor-pointer">
                    ✕ Clear All Filters
                </button>
            @endif
        </div>

    </aside>
</div>

@endsection
