@extends('layouts.admin')

@section('title', 'Edit Landing Page')

@section('content')

    <div x-data="landingPageEditForm()" x-init="init()">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="{{ route('admin.landing-pages') }}" class="text-sm text-gray-500 hover:text-green-700 transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Back to Landing Pages
                </a>
                <h2 class="text-lg font-bold text-gray-800 mt-1">Edit Landing Page</h2>
            </div>
            <a :href="'/landing/' + form.slug" target="_blank"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                <i class="fa-solid fa-external-link text-xs"></i> Preview
            </a>
        </div>

        {{-- Loading State --}}
        <div x-show="loading" class="text-center py-12 text-gray-400">
            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
        </div>

        {{-- Form --}}
        <div x-show="!loading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Main Fields --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Basic Info --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">Basic Information</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Title <span class="text-red-400">*</span></label>
                                <input x-model="form.title" type="text"
                                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Slug <span class="text-red-400">*</span></label>
                                <input x-model="form.slug" type="text"
                                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-300">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Type</label>
                                <input :value="form.type" type="text" disabled
                                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-gray-50 text-gray-500">
                                <p class="text-xs text-gray-400 mt-1">Type cannot be changed after creation</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Blade Template</label>
                                <input x-model="form.blade_template" type="text"
                                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-300">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">Content</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Hero Image Path</label>
                            <input x-model="form.hero_image" type="text"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Page Content (HTML)</label>
                            <textarea x-model="form.content" rows="8"
                                      class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-300"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Sales Items --}}
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
                                <select x-model="item.type" class="rounded-lg border border-gray-200 px-2 py-1.5 text-xs">
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
                        <span x-text="submitting ? 'Saving...' : 'Save Changes'"></span>
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
                            <input x-model="form.meta_title" type="text"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Meta Description</label>
                            <textarea x-model="form.meta_description" rows="3"
                                      class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Pixel Event Name</label>
                            <input x-model="form.pixel_event_name" type="text"
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
                            <input x-model.number="form.config.free_delivery_amount" type="number" min="0" step="1"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Free delivery above quantity</label>
                            <input x-model.number="form.config.free_delivery_qty" type="number" min="1" step="1"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function landingPageEditForm() {
        return {
            landingPageId: {{ $landingPageId }},
            loading: true,
            submitting: false,
            successMessage: '',
            errorMessage: '',

            form: {
                title: '', slug: '', type: 'product',
                product_id: null, combo_id: null,
                hero_image: '', blade_template: '', content: '',
                meta_title: '', meta_description: '', pixel_event_name: '',
                is_active: false,
                config: { free_delivery_amount: null, free_delivery_qty: null },
                items: [],
            },

            async init() {
                try {
                    const res = await fetch(`/api/admin/landing-pages/${this.landingPageId}`, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    const json = await res.json();
                    if (json.success && json.data) {
                        const d = json.data;
                        this.form = {
                            title: d.title || '',
                            slug: d.slug || '',
                            type: d.type || 'product',
                            product_id: d.product_id,
                            combo_id: d.combo_id,
                            hero_image: d.hero_image || '',
                            blade_template: d.blade_template || '',
                            content: d.content || '',
                            meta_title: d.meta_title || '',
                            meta_description: d.meta_description || '',
                            pixel_event_name: d.pixel_event_name || '',
                            is_active: d.is_active || false,
                            config: d.config || { free_delivery_amount: null, free_delivery_qty: null },
                            items: (d.items || []).map(item => ({
                                type: item.product_variant_id ? 'variant' : 'combo',
                                product_variant_id: item.product_variant_id,
                                combo_id: item.combo_id,
                                is_preselected: item.is_preselected || false,
                                sort_order: item.sort_order || 0,
                            })),
                        };
                    }
                } catch (e) {
                    console.error('Failed to load landing page:', e);
                }
                this.loading = false;
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

                const config = {};
                if (this.form.config.free_delivery_amount) config.free_delivery_amount = this.form.config.free_delivery_amount;
                if (this.form.config.free_delivery_qty) config.free_delivery_qty = this.form.config.free_delivery_qty;

                const items = this.form.type === 'sales'
                    ? this.form.items.map((item, idx) => ({
                        product_variant_id: item.type === 'variant' ? item.product_variant_id : null,
                        combo_id: item.type === 'combo' ? item.combo_id : null,
                        is_preselected: item.is_preselected || false,
                        sort_order: idx,
                    }))
                    : [];

                const payload = {
                    title: this.form.title,
                    slug: this.form.slug,
                    hero_image: this.form.hero_image || null,
                    blade_template: this.form.blade_template || null,
                    content: this.form.content || null,
                    meta_title: this.form.meta_title || null,
                    meta_description: this.form.meta_description || null,
                    pixel_event_name: this.form.pixel_event_name || null,
                    is_active: this.form.is_active,
                    config: Object.keys(config).length > 0 ? config : null,
                    items: this.form.type === 'sales' ? items : undefined,
                };

                try {
                    const res = await fetch(`/api/admin/landing-pages/${this.landingPageId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(payload),
                    });

                    const json = await res.json();

                    if (json.success) {
                        this.successMessage = 'Landing page updated successfully!';
                        setTimeout(() => { this.successMessage = ''; }, 3000);
                    } else {
                        this.errorMessage = json.message || 'Failed to update landing page.';
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
