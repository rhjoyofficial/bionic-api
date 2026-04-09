@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')

<div x-data="productForm({{ $productId }})" x-init="init()">

    {{-- Page Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.products') }}" class="text-gray-400 hover:text-gray-700 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-lg font-bold text-gray-800">Edit Product</h2>
            <p class="text-sm text-gray-500 mt-0.5" x-text="form.name ? 'Editing: ' + form.name : 'Loading…'"></p>
        </div>
    </div>

    {{-- Loading overlay --}}
    <div x-show="loading" class="flex items-center justify-center py-20">
        <div class="text-center text-gray-400">
            <i class="fa-solid fa-spinner fa-spin text-3xl mb-3 block text-green-600"></i>
            <p class="text-sm">Loading product data…</p>
        </div>
    </div>

    {{-- Global error banner --}}
    <div x-show="submitError" x-cloak class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
        <i class="fa-solid fa-circle-exclamation mr-2"></i>
        <span x-text="submitError"></span>
    </div>

    <form @submit.prevent="submit()" x-show="!loading">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- ==============================
                 LEFT COLUMN (main content)
            ============================== --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- Basic Info --}}
                <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
                    <h3 class="text-sm font-bold text-gray-700">Basic Information</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.name"
                            class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600"
                            :class="errors.name ? 'border-red-400' : 'border-gray-200'">
                        <p x-show="errors.name" class="mt-1 text-xs text-red-600" x-text="errors.name?.[0]"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                        <input type="text" x-model="form.short_description"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Description</label>
                        <textarea x-model="form.description" rows="6"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 resize-y"></textarea>
                    </div>
                </div>

                {{-- Variants --}}
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-700">Variants</h3>
                        <button type="button" @click="addVariant()"
                            class="inline-flex items-center gap-1.5 text-sm text-green-700 font-medium hover:text-green-900 transition cursor-pointer">
                            <i class="fa-solid fa-plus text-xs"></i> Add Variant
                        </button>
                    </div>

                    <p x-show="errors.variants" class="mb-3 text-xs text-red-600" x-text="errors.variants?.[0]"></p>

                    <div class="space-y-4">
                        <template x-for="(variant, index) in variants" :key="variant.id ?? 'new-' + index">
                            <div class="border border-gray-200 rounded-xl p-4 relative"
                                :class="!variant.is_active ? 'opacity-60 bg-gray-50' : ''">

                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                            x-text="'Variant ' + (index + 1) + (variant.title ? ' — ' + variant.title : '')"></span>
                                        <template x-if="variant.id">
                                            <span class="text-xs text-gray-400 font-mono" x-text="'#' + variant.id"></span>
                                        </template>
                                    </div>
                                    <button type="button" @click="removeVariant(index)"
                                        x-show="variants.length > 1"
                                        class="text-red-400 hover:text-red-600 transition cursor-pointer text-xs">
                                        <i class="fa-solid fa-xmark"></i> Remove
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Title <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="variant.title"
                                            class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600"
                                            :class="errors['variants.' + index + '.title'] ? 'border-red-400' : 'border-gray-200'">
                                        <p x-show="errors['variants.' + index + '.title']" class="mt-1 text-xs text-red-600"
                                            x-text="errors['variants.' + index + '.title']?.[0]"></p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">SKU <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="variant.sku"
                                            class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 font-mono"
                                            :class="errors['variants.' + index + '.sku'] ? 'border-red-400' : 'border-gray-200'">
                                        <p x-show="errors['variants.' + index + '.sku']" class="mt-1 text-xs text-red-600"
                                            x-text="errors['variants.' + index + '.sku']?.[0]"></p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Price (৳) <span class="text-red-500">*</span></label>
                                        <input type="number" x-model.number="variant.price" min="0" step="0.01"
                                            class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600"
                                            :class="errors['variants.' + index + '.price'] ? 'border-red-400' : 'border-gray-200'">
                                        <p x-show="errors['variants.' + index + '.price']" class="mt-1 text-xs text-red-600"
                                            x-text="errors['variants.' + index + '.price']?.[0]"></p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Stock</label>
                                        <div class="flex gap-2 items-center">
                                            <input type="number" x-model.number="variant.stock" min="0"
                                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                                            <span class="text-xs text-gray-400 whitespace-nowrap"
                                                x-show="variant.reserved_stock > 0"
                                                x-text="variant.reserved_stock + ' reserved'"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Weight (grams)</label>
                                        <input type="number" x-model.number="variant.weight_grams" min="0"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Discount Type</label>
                                        <select x-model="variant.discount_type"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
                                            <option value="">No Discount</option>
                                            <option value="percentage">Percentage (%)</option>
                                            <option value="fixed">Fixed Amount (৳)</option>
                                        </select>
                                    </div>
                                    <template x-if="variant.discount_type">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1"
                                                x-text="variant.discount_type === 'percentage' ? 'Discount %' : 'Discount Amount (৳)'"></label>
                                            <input type="number" x-model.number="variant.discount_value" min="0" step="0.01"
                                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                                        </div>
                                    </template>
                                    <template x-if="variant.discount_type">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Sale Ends At</label>
                                            <input type="datetime-local" x-model="variant.sale_ends_at"
                                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                                        </div>
                                    </template>
                                </div>

                                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" x-model="variant.is_active" class="sr-only peer">
                                            <div class="w-8 h-5 bg-gray-200 peer-checked:bg-green-600 rounded-full transition"></div>
                                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition peer-checked:translate-x-3"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-600">Variant Active</span>
                                    </label>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- SEO --}}
                <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
                    <h3 class="text-sm font-bold text-gray-700">SEO</h3>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Meta Title</label>
                        <input type="text" x-model="form.meta_title"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Meta Description</label>
                        <textarea x-model="form.meta_description" rows="2"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Meta Keywords</label>
                        <input type="text" x-model="form.meta_keywords"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                    </div>
                </div>

                {{-- Landing Page --}}
                <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-700">Landing Page</h3>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" x-model="form.is_landing_enabled" class="sr-only peer">
                                <div class="w-10 h-6 bg-gray-200 peer-checked:bg-green-600 rounded-full transition"></div>
                                <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition peer-checked:translate-x-4"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Enabled</span>
                        </label>
                    </div>
                    <div x-show="form.is_landing_enabled">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Landing Slug</label>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-400">/landing/</span>
                            <input type="text" x-model="form.landing_slug"
                                class="flex-1 border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600"
                                :class="errors.landing_slug ? 'border-red-400' : 'border-gray-200'">
                        </div>
                        <p x-show="errors.landing_slug" class="mt-1 text-xs text-red-600" x-text="errors.landing_slug?.[0]"></p>
                    </div>
                </div>

            </div>

            {{-- ==============================
                 RIGHT COLUMN (sidebar)
            ============================== --}}
            <div class="space-y-6">

                {{-- Publish box --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
                    <h3 class="text-sm font-bold text-gray-700">Publish</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <select x-model="form.category_id"
                            class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer"
                            :class="errors.category_id ? 'border-red-400' : 'border-gray-200'">
                            <option value="">Select category…</option>
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                        <p x-show="errors.category_id" class="mt-1 text-xs text-red-600" x-text="errors.category_id?.[0]"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Base Price (৳) <span class="text-red-500">*</span></label>
                        <input type="number" x-model.number="form.base_price" min="0" step="0.01"
                            class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600"
                            :class="errors.base_price ? 'border-red-400' : 'border-gray-200'">
                        <p x-show="errors.base_price" class="mt-1 text-xs text-red-600" x-text="errors.base_price?.[0]"></p>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-gray-100">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" x-model="form.is_active" class="sr-only peer">
                                <div class="w-10 h-6 bg-gray-200 peer-checked:bg-green-600 rounded-full transition"></div>
                                <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition peer-checked:translate-x-4"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Published (Active)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" x-model="form.is_featured" class="sr-only peer">
                                <div class="w-10 h-6 bg-gray-200 peer-checked:bg-yellow-500 rounded-full transition"></div>
                                <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition peer-checked:translate-x-4"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Featured</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" x-model="form.is_trending" class="sr-only peer">
                                <div class="w-10 h-6 bg-gray-200 peer-checked:bg-orange-500 rounded-full transition"></div>
                                <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition peer-checked:translate-x-4"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Trending</span>
                        </label>
                    </div>

                    <div class="pt-2 border-t border-gray-100 flex gap-3">
                        <a href="{{ route('admin.products') }}"
                            class="flex-1 text-center text-sm font-medium text-gray-600 border border-gray-200 rounded-lg py-2 hover:bg-gray-50 transition">
                            Cancel
                        </a>
                        <button type="submit" :disabled="saving"
                            class="flex-1 inline-flex items-center justify-center gap-2 bg-green-700 hover:bg-green-800 disabled:opacity-60 text-white text-sm font-medium py-2 rounded-lg transition cursor-pointer">
                            <i x-show="saving" class="fa-solid fa-spinner fa-spin text-xs"></i>
                            <span x-text="saving ? 'Saving…' : 'Save Changes'"></span>
                        </button>
                    </div>
                </div>

                {{-- Thumbnail --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="text-sm font-bold text-gray-700 mb-3">Thumbnail</h3>

                    <template x-if="thumbnailPreview">
                        <div class="mb-3 relative group">
                            <img :src="thumbnailPreview" class="w-full h-40 object-cover rounded-lg border border-gray-200">
                            <button type="button" @click="thumbnailPreview = null; thumbnail = null"
                                class="absolute top-2 right-2 w-6 h-6 bg-red-600 text-white rounded-full text-xs opacity-0 group-hover:opacity-100 transition cursor-pointer flex items-center justify-center">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </template>

                    <label class="cursor-pointer flex flex-col items-center gap-2 border-2 border-dashed border-gray-200 rounded-xl px-4 py-5 hover:border-green-400 hover:bg-green-50 transition text-center">
                        <i class="fa-solid fa-image text-gray-300 text-2xl"></i>
                        <span class="text-sm text-gray-500" x-text="thumbnailPreview ? 'Replace thumbnail' : 'Upload thumbnail'"></span>
                        <span class="text-xs text-gray-400">JPG, PNG, WebP · Max 2MB</span>
                        <input type="file" accept="image/*" class="sr-only" @change="handleThumbnail($event)">
                    </label>
                    <p x-show="errors.thumbnail" class="mt-1 text-xs text-red-600" x-text="errors.thumbnail?.[0]"></p>
                </div>

                {{-- Gallery --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-gray-700">Gallery</h3>
                        <span class="text-xs text-gray-400"
                            x-text="(galleryExisting.length + galleryFiles.length) + ' images'"></span>
                    </div>

                    {{-- Existing gallery images --}}
                    <template x-if="galleryExisting.length > 0">
                        <div class="mb-3">
                            <p class="text-xs text-gray-500 mb-2">Current images</p>
                            <div class="grid grid-cols-3 gap-2">
                                <template x-for="(img, i) in galleryExisting" :key="i">
                                    <div class="relative group">
                                        <img :src="'/storage/' + img" class="w-full h-20 object-cover rounded-lg border border-gray-100">
                                        <button type="button" @click="removeGalleryExisting(img)"
                                            class="absolute top-1 right-1 w-5 h-5 bg-red-600 text-white rounded-full text-xs opacity-0 group-hover:opacity-100 transition cursor-pointer flex items-center justify-center">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- New gallery images to upload --}}
                    <template x-if="galleryPreviews.length > 0">
                        <div class="mb-3">
                            <p class="text-xs text-gray-500 mb-2">New images to add</p>
                            <div class="grid grid-cols-3 gap-2">
                                <template x-for="(img, i) in galleryPreviews" :key="i">
                                    <div class="relative group">
                                        <img :src="img" class="w-full h-20 object-cover rounded-lg border border-blue-100">
                                        <button type="button" @click="removeGalleryNew(i)"
                                            class="absolute top-1 right-1 w-5 h-5 bg-red-600 text-white rounded-full text-xs opacity-0 group-hover:opacity-100 transition cursor-pointer flex items-center justify-center">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <label class="cursor-pointer flex items-center gap-2 border border-dashed border-gray-200 rounded-lg px-4 py-3 hover:border-green-400 hover:bg-green-50 transition text-sm text-gray-500">
                        <i class="fa-solid fa-plus text-xs"></i>
                        Add more images
                        <input type="file" accept="image/*" multiple class="sr-only" @change="handleGallery($event)">
                    </label>
                </div>

            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
function productForm(productId) {
    return {
        productId,
        isEdit: productId !== null,
        loading: productId !== null,
        saving: false,
        errors: {},
        submitError: null,
        categories: [],

        form: {
            name: '',
            short_description: '',
            description: '',
            category_id: '',
            base_price: '',
            is_active: true,
            is_featured: false,
            is_trending: false,
            meta_title: '',
            meta_description: '',
            meta_keywords: '',
            landing_slug: '',
            is_landing_enabled: false,
        },

        thumbnail: null,
        thumbnailPreview: null,
        galleryFiles: [],
        galleryPreviews: [],
        galleryExisting: [],
        galleryRemove: [],

        variants: [],

        async init() {
            await this.loadCategories();
            if (this.isEdit) {
                await this.loadProduct();
            } else {
                this.addVariant();
            }
        },

        async loadCategories() {
            try {
                const r = await fetch('/api/v1/admin/categories?per_page=100', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await r.json();
                this.categories = data.data ?? [];
            } catch (e) { console.error(e); }
        },

        async loadProduct() {
            try {
                const r = await fetch(`/api/v1/admin/products/${this.productId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await r.json();

                if (!r.ok) {
                    this.submitError = data.message ?? 'Failed to load product.';
                    return;
                }

                const p = data.data;

                this.form = {
                    name: p.name ?? '',
                    short_description: p.short_description ?? '',
                    description: p.description ?? '',
                    category_id: p.category?.id ?? '',
                    base_price: p.base_price ?? '',
                    is_active: p.is_active ?? true,
                    is_featured: p.is_featured ?? false,
                    is_trending: p.is_trending ?? false,
                    meta_title: p.meta_title ?? '',
                    meta_description: p.meta_description ?? '',
                    meta_keywords: p.meta_keywords ?? '',
                    landing_slug: p.landing_slug ?? '',
                    is_landing_enabled: p.is_landing_enabled ?? false,
                };

                this.thumbnailPreview = p.image_url ?? null;
                this.galleryExisting = p.gallery ?? [];

                this.variants = (p.variants ?? []).map(v => ({
                    id: v.id,
                    title: v.title ?? '',
                    sku: v.sku ?? '',
                    price: v.price ?? '',
                    stock: v.stock ?? 0,
                    reserved_stock: v.reserved_stock ?? 0,
                    weight_grams: v.weight_grams ?? '',
                    discount_type: v.discount_type ?? '',
                    discount_value: v.discount_value ?? '',
                    sale_ends_at: v.sale_ends_at
                        ? new Date(v.sale_ends_at).toISOString().slice(0, 16)
                        : '',
                    is_active: v.is_active !== false,
                }));

                if (this.variants.length === 0) {
                    this.addVariant();
                }
            } catch (e) {
                this.submitError = 'Network error loading product data.';
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        addVariant() {
            this.variants.push({
                id: null,
                title: '',
                sku: '',
                price: '',
                stock: 0,
                reserved_stock: 0,
                weight_grams: '',
                discount_type: '',
                discount_value: '',
                sale_ends_at: '',
                is_active: true,
            });
        },

        removeVariant(index) {
            if (this.variants.length > 1) {
                this.variants.splice(index, 1);
            }
        },

        handleThumbnail(e) {
            this.thumbnail = e.target.files[0] ?? null;
            if (this.thumbnail) {
                const reader = new FileReader();
                reader.onload = (ev) => { this.thumbnailPreview = ev.target.result; };
                reader.readAsDataURL(this.thumbnail);
            }
        },

        handleGallery(e) {
            const files = Array.from(e.target.files);
            files.forEach(file => {
                this.galleryFiles.push(file);
                const reader = new FileReader();
                reader.onload = (ev) => { this.galleryPreviews.push(ev.target.result); };
                reader.readAsDataURL(file);
            });
            e.target.value = '';
        },

        removeGalleryNew(index) {
            this.galleryFiles.splice(index, 1);
            this.galleryPreviews.splice(index, 1);
        },

        removeGalleryExisting(path) {
            this.galleryExisting = this.galleryExisting.filter(p => p !== path);
            this.galleryRemove.push(path);
        },

        buildFormData() {
            const fd = new FormData();

            const boolFields = ['is_active', 'is_featured', 'is_trending', 'is_landing_enabled'];
            Object.entries(this.form).forEach(([key, val]) => {
                if (val === null || val === undefined || val === '') return;
                fd.append(key, boolFields.includes(key) ? (val ? '1' : '0') : val);
            });
            boolFields.forEach(key => {
                if (!fd.has(key)) fd.append(key, '0');
            });

            if (this.thumbnail) fd.append('thumbnail', this.thumbnail);
            this.galleryFiles.forEach(f => fd.append('gallery[]', f));
            this.galleryRemove.forEach(p => fd.append('gallery_remove[]', p));

            this.variants.forEach((v, i) => {
                if (v.id) fd.append(`variants[${i}][id]`, v.id);
                fd.append(`variants[${i}][title]`, v.title ?? '');
                fd.append(`variants[${i}][sku]`, v.sku ?? '');
                fd.append(`variants[${i}][price]`, v.price ?? 0);
                fd.append(`variants[${i}][stock]`, v.stock ?? 0);
                fd.append(`variants[${i}][is_active]`, v.is_active ? '1' : '0');
                if (v.weight_grams) fd.append(`variants[${i}][weight_grams]`, v.weight_grams);
                if (v.discount_type) fd.append(`variants[${i}][discount_type]`, v.discount_type);
                if (v.discount_value) fd.append(`variants[${i}][discount_value]`, v.discount_value);
                if (v.sale_ends_at) fd.append(`variants[${i}][sale_ends_at]`, v.sale_ends_at);
            });

            return fd;
        },

        async submit() {
            this.saving = true;
            this.errors = {};
            this.submitError = null;

            const fd = this.buildFormData();
            const url = this.isEdit
                ? `/api/v1/admin/products/${this.productId}`
                : '/api/v1/admin/products';

            if (this.isEdit) fd.append('_method', 'PUT');

            try {
                const r = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: fd,
                });

                const data = await r.json();

                if (r.ok) {
                    window.location.href = '/admin/products';
                } else if (r.status === 422) {
                    this.errors = data.errors ?? {};
                    this.$nextTick(() => {
                        const el = document.querySelector('.border-red-400');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                } else {
                    this.submitError = data.message ?? 'An unexpected error occurred.';
                }
            } catch (e) {
                this.submitError = 'Network error. Please check your connection.';
            } finally {
                this.saving = false;
            }
        },
    };
}
</script>
@endpush
