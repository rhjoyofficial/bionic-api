@extends('layouts.admin')

@section('title', 'New Combo')

@section('content')

<div x-data="comboForm(null)" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.combos') }}" class="text-gray-400 hover:text-gray-700 transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-lg font-bold text-gray-800">New Combo</h2>
                <p class="text-sm text-gray-500 mt-0.5">Bundle products and define a combined price</p>
            </div>
        </div>
        <button @click="submit()" :disabled="saving"
            class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold bg-green-700 text-white rounded-lg hover:bg-green-800 disabled:opacity-50 transition cursor-pointer disabled:cursor-not-allowed">
            <i class="fa-solid fa-floppy-disk text-xs"></i>
            <span x-text="saving ? 'Creating…' : 'Create Combo'"></span>
        </button>
    </div>

    {{-- Global errors --}}
    <template x-if="errors._global">
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl">
            <span x-text="errors._global"></span>
        </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── LEFT / MAIN ─────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Basic Info --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
                <h3 class="font-semibold text-gray-700 text-sm">Basic Information</h3>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.title" @input="autoSlug()"
                        :class="errors.title ? 'border-red-400' : 'border-gray-200'"
                        placeholder="e.g. Skincare Starter Bundle"
                        class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                    <p x-show="errors.title" class="text-xs text-red-500 mt-1" x-text="errors.title?.[0]"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Slug</label>
                    <input type="text" x-model="form.slug"
                        :class="errors.slug ? 'border-red-400' : 'border-gray-200'"
                        placeholder="auto-generated from title"
                        class="w-full border rounded-lg px-3 py-2 text-sm font-mono outline-none focus:ring-2 focus:ring-green-600">
                    <p x-show="errors.slug" class="text-xs text-red-500 mt-1" x-text="errors.slug?.[0]"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
                    <textarea x-model="form.description" rows="3" placeholder="Describe what's included in this bundle…"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 resize-none">
                    </textarea>
                </div>

                {{-- Image --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Combo Image</label>
                    <div class="flex items-start gap-4">
                        <div class="w-24 h-24 rounded-xl border-2 border-dashed border-gray-200 overflow-hidden flex items-center justify-center bg-gray-50 flex-shrink-0 cursor-pointer"
                            @click="$refs.imageInput.click()">
                            <img x-show="imagePreview" :src="imagePreview" class="w-full h-full object-cover">
                            <div x-show="!imagePreview" class="text-center p-2">
                                <i class="fa-solid fa-image text-gray-300 text-xl"></i>
                                <p class="text-xs text-gray-400 mt-1">Upload</p>
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="file" x-ref="imageInput" accept="image/*" class="hidden"
                                @change="onImageChange($event)">
                            <button type="button" @click="$refs.imageInput.click()"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium cursor-pointer">
                                Choose image
                            </button>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — max 2 MB</p>
                            <p x-show="errors.image" class="text-xs text-red-500 mt-1" x-text="errors.image?.[0]"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Components ──────────────────────────────────── --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700 text-sm">
                        Components
                        <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-100 text-green-700 text-xs font-bold"
                            x-text="items.length">
                        </span>
                    </h3>
                </div>
                <p x-show="errors.items" class="text-xs text-red-500" x-text="errors.items?.[0]"></p>

                {{-- Search --}}
                <div class="relative" x-data="{ open: false }">
                    <div class="relative">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" x-model="variantSearch"
                            @focus="open = true"
                            @input.debounce.350ms="searchVariants()"
                            placeholder="Search products to add as components…"
                            class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-green-600">
                        <div x-show="variantSearching" class="absolute right-3 top-1/2 -translate-y-1/2">
                            <i class="fa-solid fa-spinner fa-spin text-gray-400 text-xs"></i>
                        </div>
                    </div>

                    {{-- Search Results Dropdown --}}
                    <div x-show="open && variantResults.length > 0" x-cloak
                        @click.outside="open = false"
                        class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-30 max-h-72 overflow-y-auto">
                        <template x-for="product in variantResults" :key="product.id">
                            <div class="border-b border-gray-50 last:border-0">
                                <div class="px-4 py-2 bg-gray-50 flex items-center gap-2">
                                    <div class="w-6 h-6 rounded overflow-hidden flex-shrink-0 bg-gray-200">
                                        <img x-show="product.thumbnail" :src="product.thumbnail" class="w-full h-full object-cover">
                                    </div>
                                    <span class="text-xs font-semibold text-gray-700" x-text="product.name"></span>
                                </div>
                                <template x-for="variant in (product.variants ?? [])" :key="variant.id">
                                    <button type="button"
                                        @click="addItem(product, variant); open = false; variantSearch = ''; variantResults = [];"
                                        :disabled="isAdded(variant.id)"
                                        class="w-full text-left px-5 py-2 flex items-center justify-between hover:bg-green-50 transition disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                                        <div>
                                            <span class="text-xs font-medium text-gray-800" x-text="variant.title"></span>
                                            <span class="ml-2 text-xs text-gray-400 font-mono" x-text="variant.sku ?? ''"></span>
                                        </div>
                                        <div class="flex items-center gap-3 text-xs text-right">
                                            <span class="text-gray-600">৳<span x-text="Number(variant.final_price ?? variant.price).toLocaleString()"></span></span>
                                            <span :class="variant.available_stock > 0 ? 'text-green-600' : 'text-red-500'"
                                                x-text="variant.available_stock > 0 ? variant.available_stock + ' in stock' : 'Out of stock'">
                                            </span>
                                            <span x-show="isAdded(variant.id)" class="text-green-600 font-bold">✓ Added</span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Added items list --}}
                <template x-if="items.length === 0">
                    <div class="text-center py-8 border-2 border-dashed border-gray-100 rounded-xl text-gray-400">
                        <i class="fa-solid fa-box-open text-2xl mb-2 block"></i>
                        <p class="text-sm">Search above to add product variants</p>
                    </div>
                </template>

                <template x-if="items.length > 0">
                    <div class="space-y-2">
                        <template x-for="(item, idx) in items" :key="item.variant_id">
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 bg-white border border-gray-200">
                                    <img x-show="item.product_thumbnail" :src="item.product_thumbnail" class="w-full h-full object-cover">
                                    <div x-show="!item.product_thumbnail" class="w-full h-full flex items-center justify-center text-gray-300">
                                        <i class="fa-solid fa-box text-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-800 truncate" x-text="item.product_name"></p>
                                    <p class="text-xs text-gray-500 truncate" x-text="item.variant_title"></p>
                                    <p class="text-xs text-gray-400">৳<span x-text="Number(item.unit_price).toLocaleString()"></span> each</p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <label class="text-xs text-gray-500">Qty</label>
                                    <input type="number" x-model.number="item.quantity" min="1" max="99"
                                        @input="computeAutoPrice()"
                                        class="w-16 border border-gray-200 rounded-lg px-2 py-1 text-sm text-center outline-none focus:ring-2 focus:ring-green-600">
                                </div>
                                <button type="button" @click="removeItem(idx)"
                                    class="text-red-400 hover:text-red-600 transition cursor-pointer ml-1">
                                    <i class="fa-solid fa-xmark text-sm"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- ── Pricing ──────────────────────────────────────── --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
                <h3 class="font-semibold text-gray-700 text-sm">Pricing</h3>

                {{-- Pricing mode --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Pricing Mode</label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" x-model="form.pricing_mode" value="auto" class="accent-green-700">
                            <span class="text-sm text-gray-700">
                                Auto
                                <span class="text-xs text-gray-400">(sum of component prices)</span>
                            </span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" x-model="form.pricing_mode" value="manual" class="accent-green-700">
                            <span class="text-sm text-gray-700">Manual</span>
                        </label>
                    </div>
                </div>

                {{-- Auto price preview --}}
                <div x-show="form.pricing_mode === 'auto'" class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3">
                    <p class="text-xs text-blue-600">
                        Auto price (sum of components):
                        <span class="font-bold text-blue-800 ml-1">৳<span x-text="Number(autoPrice).toLocaleString()"></span></span>
                    </p>
                </div>

                {{-- Manual price --}}
                <div x-show="form.pricing_mode === 'manual'">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Manual Price (৳) <span class="text-red-500">*</span></label>
                    <input type="number" x-model="form.manual_price" min="0" step="0.01"
                        :class="errors.manual_price ? 'border-red-400' : 'border-gray-200'"
                        class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                    <p x-show="errors.manual_price" class="text-xs text-red-500 mt-1" x-text="errors.manual_price?.[0]"></p>
                </div>

                {{-- Discount --}}
                <div class="border-t border-gray-100 pt-4">
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Discount (optional)</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <select x-model="form.discount_type"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
                                <option value="">No discount</option>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed (৳)</option>
                            </select>
                        </div>
                        <div>
                            <input type="number" x-model="form.discount_value" min="0" step="0.01"
                                :disabled="!form.discount_type"
                                :placeholder="form.discount_type === 'percentage' ? 'e.g. 10' : form.discount_type === 'fixed' ? 'e.g. 50' : 'Select type first'"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 disabled:bg-gray-50 disabled:text-gray-400">
                        </div>
                    </div>
                </div>

                {{-- Final price preview --}}
                <div x-show="finalPrice > 0" class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 flex items-center justify-between">
                    <span class="text-xs text-green-700 font-medium">Final selling price</span>
                    <span class="text-lg font-bold text-green-800">৳<span x-text="Number(finalPrice).toLocaleString()"></span></span>
                </div>
            </div>

        </div>

        {{-- ── RIGHT / SIDEBAR ──────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Publish --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
                <h3 class="font-semibold text-gray-700 text-sm">Publish</h3>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700">Active</span>
                    <button type="button" @click="form.is_active = !form.is_active"
                        class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors"
                        :class="form.is_active ? 'bg-green-600' : 'bg-gray-300'">
                        <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow transition"
                            :class="form.is_active ? 'translate-x-4' : 'translate-x-0'"></span>
                    </button>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700">Featured</span>
                    <button type="button" @click="form.is_featured = !form.is_featured"
                        class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors"
                        :class="form.is_featured ? 'bg-yellow-500' : 'bg-gray-300'">
                        <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow transition"
                            :class="form.is_featured ? 'translate-x-4' : 'translate-x-0'"></span>
                    </button>
                </div>

                <button @click="submit()" :disabled="saving"
                    class="w-full px-4 py-2.5 text-sm font-semibold bg-green-700 text-white rounded-lg hover:bg-green-800 disabled:opacity-50 transition cursor-pointer disabled:cursor-not-allowed">
                    <span x-text="saving ? 'Creating…' : 'Create Combo'"></span>
                </button>

                <a href="{{ route('admin.combos') }}"
                    class="block w-full text-center px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>

            {{-- Summary --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5" x-show="items.length > 0">
                <h3 class="font-semibold text-gray-700 text-sm mb-3">Summary</h3>
                <div class="space-y-2 text-xs text-gray-600">
                    <div class="flex justify-between">
                        <span>Components</span>
                        <span class="font-semibold" x-text="items.length"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Combined price</span>
                        <span class="font-semibold">৳<span x-text="Number(autoPrice).toLocaleString()"></span></span>
                    </div>
                    <template x-if="form.pricing_mode === 'manual' && form.manual_price">
                        <div class="flex justify-between">
                            <span>Manual price</span>
                            <span class="font-semibold">৳<span x-text="Number(form.manual_price).toLocaleString()"></span></span>
                        </div>
                    </template>
                    <template x-if="form.discount_type && form.discount_value">
                        <div class="flex justify-between text-orange-600">
                            <span>Discount</span>
                            <span class="font-semibold"
                                x-text="form.discount_type === 'percentage' ? '-' + form.discount_value + '%' : '-৳' + Number(form.discount_value).toLocaleString()">
                            </span>
                        </div>
                    </template>
                    <div class="flex justify-between border-t border-gray-100 pt-2 font-bold text-gray-800">
                        <span>Final Price</span>
                        <span>৳<span x-text="Number(finalPrice).toLocaleString()"></span></span>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
@include('admin.combos._combo_form_script')
</script>
@endpush
