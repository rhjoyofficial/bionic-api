@extends('layouts.admin')

@section('title', 'Create Landing Page')

@section('content')

    <div x-data="landingPageForm()" x-init="init()">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="{{ route('admin.landing-pages') }}" class="text-sm text-gray-500 hover:text-green-700 transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Back to Landing Pages
                </a>
                <h2 class="text-lg font-bold text-gray-800 mt-1">Create Landing Page</h2>
            </div>
        </div>

        {{-- Form --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Main Fields --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Basic Info --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">Basic Information</h3>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Title <span class="text-red-400">*</span></label>
                                <input x-model="form.title" @input="autoSlug()" type="text" placeholder="e.g. Premium Medjool Dates"
                                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Slug <span class="text-red-400">*</span></label>
                                <input x-model="form.slug" type="text" placeholder="premium-medjool-dates"
                                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-300">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Type <span class="text-red-400">*</span></label>
                                <select x-model="form.type" @change="onTypeChange()"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                                    <option value="product">Product</option>
                                    <option value="combo">Combo</option>
                                    <option value="sales">Sales (Multiple Items)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Blade Template</label>
                                <input x-model="form.blade_template" type="text" placeholder="Auto: product-default"
                                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-300">
                                <p class="text-xs text-gray-400 mt-1">Leave empty for default. Custom: create <code>resources/views/landing/templates/{name}.blade.php</code></p>
                            </div>
                        </div>

                        {{-- Product/Combo selector --}}
                        <div x-show="form.type === 'product'">
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Product <span class="text-red-400">*</span></label>
                            <input x-model="productSearch" @input.debounce.300ms="searchProducts()" type="text" placeholder="Search products..."
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                            <div x-show="productResults.length > 0" class="mt-2 bg-white border border-gray-200 rounded-lg max-h-40 overflow-y-auto">
                                <template x-for="p in productResults" :key="p.id">
                                    <button @click="selectProduct(p)" type="button"
                                            class="w-full text-left px-3 py-2 hover:bg-green-50 text-sm cursor-pointer"
                                            :class="form.product_id === p.id ? 'bg-green-50 font-semibold' : ''">
                                        <span x-text="p.name"></span>
                                    </button>
                                </template>
                            </div>
                            <p x-show="selectedProductName" class="text-xs text-green-600 mt-1">
                                Selected: <span x-text="selectedProductName" class="font-semibold"></span>
                            </p>
                        </div>

                        <div x-show="form.type === 'combo'">
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Combo <span class="text-red-400">*</span></label>
                            <input x-model="comboSearch" @input.debounce.300ms="searchCombos()" type="text" placeholder="Search combos..."
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                            <div x-show="comboResults.length > 0" class="mt-2 bg-white border border-gray-200 rounded-lg max-h-40 overflow-y-auto">
                                <template x-for="c in comboResults" :key="c.id">
                                    <button @click="selectCombo(c)" type="button"
                                            class="w-full text-left px-3 py-2 hover:bg-green-50 text-sm cursor-pointer"
                                            :class="form.combo_id === c.id ? 'bg-green-50 font-semibold' : ''">
                                        <span x-text="c.name"></span>
                                    </button>
                                </template>
                            </div>
                            <p x-show="selectedComboName" class="text-xs text-green-600 mt-1">
                                Selected: <span x-text="selectedComboName" class="font-semibold"></span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">Content</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Hero Image Path</label>
                            <input x-model="form.hero_image" type="text" placeholder="landing/hero-image.jpg"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Page Content (HTML)</label>
                            <textarea x-model="form.content" rows="6" placeholder="HTML content for the landing page body..."
                                      class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-300"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Sales Items (only for type=sales) --}}
                <div x-show="form.type === 'sales'" class="bg-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800">Sales Items</h3>
                        <button @click="addSalesItem()" type="button"
                                class="text-sm text-green-700 hover:text-green-800 font-semibold cursor-pointer">
                            <i class="fa-solid fa-plus mr-1"></i> Add Item
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(item, idx) in form.items" :key="idx">
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                <select x-model="item.type" @change="item.product_variant_id = null; item.combo_id = null;"
                                        class="rounded-lg border border-gray-200 px-2 py-1.5 text-xs">
                                    <option value="variant">Variant</option>
                                    <option value="combo">Combo</option>
                                </select>
                                <input x-show="item.type === 'variant'" x-model.number="item.product_variant_id" type="number" placeholder="Variant ID"
                                       class="flex-1 rounded-lg border border-gray-200 px-2 py-1.5 text-xs">
                                <input x-show="item.type === 'combo'" x-model.number="item.combo_id" type="number" placeholder="Combo ID"
                                       class="flex-1 rounded-lg border border-gray-200 px-2 py-1.5 text-xs">
                                <label class="flex items-center gap-1 text-xs text-gray-500">
                                    <input type="checkbox" x-model="item.is_preselected" class="accent-green-700">
                                    Pre-select
                                </label>
                                <button @click="form.items.splice(idx, 1)" type="button"
                                        class="text-red-400 hover:text-red-600 cursor-pointer">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Right: SEO + Config + Actions --}}
            <div class="space-y-5">

                {{-- Actions --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" x-model="form.is_active" class="accent-green-700 w-4 h-4">
                            <span class="font-semibold text-gray-700">Active</span>
                        </label>
                    </div>

                    <button @click="submit()" :disabled="submitting" type="button"
                            class="w-full bg-green-700 text-white py-2.5 rounded-lg font-semibold text-sm hover:bg-green-800 transition disabled:opacity-70 cursor-pointer">
                        <span x-text="submitting ? 'Creating...' : 'Create Landing Page'"></span>
                    </button>

                    <p x-show="successMessage" class="text-green-600 text-xs mt-2 font-medium" x-text="successMessage"></p>
                    <p x-show="errorMessage" class="text-red-500 text-xs mt-2 font-medium" x-text="errorMessage"></p>
                </div>

                {{-- SEO --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">SEO</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Meta Title</label>
                            <input x-model="form.meta_title" type="text" placeholder="Page title for search engines"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Meta Description</label>
                            <textarea x-model="form.meta_description" rows="3" placeholder="Short description for search engines"
                                      class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Pixel Event Name</label>
                            <input x-model="form.pixel_event_name" type="text" placeholder="e.g. ViewContent"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                        </div>
                    </div>
                </div>

                {{-- Free Delivery Config --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">Free Delivery Rules</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Free delivery above amount</label>
                            <input x-model.number="form.config.free_delivery_amount" type="number" min="0" step="1" placeholder="e.g. 1500"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                            <p class="text-xs text-gray-400 mt-1">Leave empty to use zone default</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Free delivery above quantity</label>
                            <input x-model.number="form.config.free_delivery_qty" type="number" min="1" step="1" placeholder="e.g. 3"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function landingPageForm() {
        return {
            form: {
                title: '',
                slug: '',
                type: 'product',
                product_id: null,
                combo_id: null,
                hero_image: '',
                blade_template: '',
                content: '',
                meta_title: '',
                meta_description: '',
                pixel_event_name: '',
                is_active: false,
                config: { free_delivery_amount: null, free_delivery_qty: null },
                items: [],
            },

            productSearch: '',
            comboSearch: '',
            productResults: [],
            comboResults: [],
            selectedProductName: '',
            selectedComboName: '',
            submitting: false,
            successMessage: '',
            errorMessage: '',

            init() {},

            autoSlug() {
                this.form.slug = this.form.title
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            },

            onTypeChange() {
                this.form.product_id = null;
                this.form.combo_id = null;
                this.selectedProductName = '';
                this.selectedComboName = '';
                if (this.form.type !== 'sales') {
                    this.form.items = [];
                }
            },

            async searchProducts() {
                if (this.productSearch.length < 2) { this.productResults = []; return; }
                try {
                    const res = await fetch(`/api/admin/orders/search-products?q=${encodeURIComponent(this.productSearch)}`, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    const json = await res.json();
                    this.productResults = json.data || [];
                } catch (e) { this.productResults = []; }
            },

            selectProduct(p) {
                this.form.product_id = p.id;
                this.selectedProductName = p.name;
                this.productSearch = p.name;
                this.productResults = [];
            },

            async searchCombos() {
                if (this.comboSearch.length < 2) { this.comboResults = []; return; }
                try {
                    const res = await fetch(`/api/admin/combos?search=${encodeURIComponent(this.comboSearch)}&per_page=10`, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    const json = await res.json();
                    this.comboResults = json.data?.data || json.data || [];
                } catch (e) { this.comboResults = []; }
            },

            selectCombo(c) {
                this.form.combo_id = c.id;
                this.selectedComboName = c.name;
                this.comboSearch = c.name;
                this.comboResults = [];
            },

            addSalesItem() {
                this.form.items.push({
                    type: 'variant',
                    product_variant_id: null,
                    combo_id: null,
                    is_preselected: false,
                    sort_order: this.form.items.length,
                });
            },

            async submit() {
                this.submitting = true;
                this.successMessage = '';
                this.errorMessage = '';

                // Clean config: remove empty values
                const config = {};
                if (this.form.config.free_delivery_amount) config.free_delivery_amount = this.form.config.free_delivery_amount;
                if (this.form.config.free_delivery_qty) config.free_delivery_qty = this.form.config.free_delivery_qty;

                // Clean items for sales type
                const items = this.form.type === 'sales'
                    ? this.form.items.map((item, idx) => ({
                        product_variant_id: item.type === 'variant' ? item.product_variant_id : null,
                        combo_id: item.type === 'combo' ? item.combo_id : null,
                        is_preselected: item.is_preselected || false,
                        sort_order: idx,
                    }))
                    : [];

                const payload = {
                    ...this.form,
                    config: Object.keys(config).length > 0 ? config : null,
                    items: items.length > 0 ? items : undefined,
                    blade_template: this.form.blade_template || (this.form.type + '-default'),
                };

                try {
                    const res = await fetch('/api/admin/landing-pages', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(payload),
                    });

                    const json = await res.json();

                    if (json.success) {
                        this.successMessage = 'Landing page created successfully!';
                        setTimeout(() => {
                            window.location.href = '{{ route("admin.landing-pages") }}';
                        }, 1000);
                    } else {
                        this.errorMessage = json.message || 'Failed to create landing page.';
                    }
                } catch (e) {
                    this.errorMessage = 'Network error. Please try again.';
                }

                this.submitting = false;
            },
        };
    }
    </script>

@endsection
