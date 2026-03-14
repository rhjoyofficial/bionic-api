<section class="py-16 px-4 md:px-8 bg-white">
    <div class="max-w-8xl mx-auto text-center">

        <h2 class="font-heading text-2xl md:text-4xl font-bold text-gray-900 mb-8">
            Shop By Categories
        </h2>

        @php
            $categories = [
                ['name' => 'Dates (খেজুর)', 'active' => true],
                ['name' => 'Honey (মধু)', 'active' => false],
                ['name' => 'Oil (তেল)', 'active' => false],
                ['name' => 'Seed (বীজ)', 'active' => false],
                ['name' => 'Jaggery (গুড়)', 'active' => false],
                ['name' => 'Salt (লবণ)', 'active' => false],
                ['name' => 'Mix (মিশ্রণ)', 'active' => false],
            ];
        @endphp


        {{-- MOBILE CATEGORY SELECT --}}
        <div class="mb-8 md:hidden px-2">
            <select
                class="w-full border border-gray-200 rounded-full px-5 py-3 text-sm font-medium text-primary bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary">

                @foreach ($categories as $cat)
                    <option {{ $cat['active'] ? 'selected' : '' }}>
                        {{ $cat['name'] }}
                    </option>
                @endforeach

            </select>
        </div>


        {{-- DESKTOP CAPSULE CATEGORY --}}
        <div class="mb-12 hidden md:block">
            <div class="flex flex-wrap items-center justify-center gap-3">

                @foreach ($categories as $cat)
                    <button
                        class="px-6 py-2 rounded-full text-sm font-medium transition-all cursor-pointer whitespace-nowrap
                        {{ $cat['active']
                            ? 'bg-primary text-white shadow-md'
                            : 'bg-gray-50 text-primary hover:bg-primary/80 hover:text-white! border border-transparent' }}">
                        {{ $cat['name'] }}
                    </button>
                @endforeach

            </div>
        </div>


        {{-- PRODUCTS GRID --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 md:gap-6 mb-12">
            @foreach (range(1, 8) as $i)
                <x-product-card :i="$i" />
            @endforeach
        </div>

        <a href="/products"
            class="inline-block px-10 py-3 bg-primary text-white font-heading font-bold rounded-full shadow-lg hover:shadow-primary/30 transition-all active:scale-95">
            View All Products
        </a>

    </div>
</section>
