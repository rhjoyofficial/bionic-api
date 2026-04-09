<section class="py-16 px-4 md:px-8 bg-white overflow-hidden" id="allProducts">
    <div class="max-w-8xl mx-auto text-center">

        <h2 id="" class="font-heading text-2xl md:text-4xl font-bold text-gray-900 mb-8">
            Shop By Categories
        </h2>

        {{-- MOBILE CATEGORY SELECT --}}
        <div class="mb-8 md:hidden px-2">
            <select onchange="filterCategory(this.value, this)"
                class="w-full border border-gray-200 rounded-full px-5 py-3 text-sm font-medium text-primary bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="all">All Products</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- DESKTOP CAPSULE CATEGORY --}}
        <div class="mb-12 hidden md:block">
            <div class="flex flex-wrap items-center justify-center gap-3">
                {{-- Default "All" Button --}}
                <button onclick="filterCategory('all', this)" data-category="all"
                    class="category-tab px-6 py-2 rounded-full text-sm font-medium transition-all cursor-pointer whitespace-nowrap bg-primary text-white shadow-md border border-primary">
                    All Products
                </button>

                @foreach ($categories as $cat)
                    <button onclick="filterCategory({{ $cat->id }}, this)" data-category="{{ $cat->id }}"
                        class="category-tab px-6 py-2 rounded-full text-sm font-medium transition-all cursor-pointer whitespace-nowrap bg-gray-50 text-primary border border-transparent hover:bg-gray-100">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- PRODUCTS GRID --}}
        <div id="categoryProductGrid" class="grid grid-cols-2 lg:grid-cols-4 gap-2 md:gap-6 mb-12 min-h-112.5">
            @forelse ($categoryProducts as $product)
                <div class="product-item-wrapper transition-all duration-500 ease-in-out transform hidden opacity-0 scale-95"
                    data-category="{{ $product->category['id'] ?? 0 }}">
                    <x-product-card :product="$product" />
                </div>
            @empty
                <div class="col-span-full py-12 text-gray-400">
                    No products found.
                </div>
            @endforelse
        </div>

        {{-- <a href="{{ route('products.index') }}"
            class="inline-block px-10 py-3 bg-primary text-white font-heading font-bold rounded-full shadow-lg hover:shadow-primary/30 transition-all active:scale-95">
            View All Products
        </a> --}}
    </div>
</section>
