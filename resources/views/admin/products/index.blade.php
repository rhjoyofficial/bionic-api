@extends('layouts.admin')

@section('title', 'Products')

@section('content')

    <div x-data="productList()" x-init="init()">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Products</h2>
                <p class="text-sm text-gray-500 mt-0.5">Manage all products, variants and inventory</p>
            </div>
            @can('product.create')
                <a href="{{ route('admin.products.create') }}"
                    class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <i class="fa-solid fa-plus text-xs"></i>
                    Add Product
                </a>
            @endcan
        </div>

        {{-- Filters --}}
        <div class="bg-white border border-gray-200 rounded-xl mb-4 p-4 flex flex-col sm:flex-row gap-3">
            {{-- Search --}}
            <div class="relative flex-1 max-w-sm">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" x-model="search" @input.debounce.400ms="loadProducts(1)"
                    placeholder="Search products…"
                    class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-green-600">
            </div>

            {{-- Category filter --}}
            <select x-model="filterCategory" @change="loadProducts(1)"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
                <option value="">All Categories</option>
                <template x-for="cat in allCategories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.name"></option>
                </template>
            </select>

            {{-- Status filter --}}
            <select x-model="filterStatus" @change="loadProducts(1)"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <p class="text-sm text-gray-500 self-center ml-auto"
                x-text="meta.total !== undefined ? meta.total + ' products' : ''"></p>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-10"></th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Variants</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Base Price</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Landing Status
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">

                        {{-- Loading skeleton --}}
                        <template x-if="loading">
                            <template x-for="i in 8" :key="i">
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg animate-pulse"></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="h-4 bg-gray-100 rounded animate-pulse w-40"></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="h-4 bg-gray-100 rounded animate-pulse w-24"></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="h-4 bg-gray-100 rounded animate-pulse w-10"></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="h-4 bg-gray-100 rounded animate-pulse w-16"></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="h-4 bg-gray-100 rounded animate-pulse w-16"></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="h-4 bg-gray-100 rounded animate-pulse w-20"></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="h-4 bg-gray-100 rounded animate-pulse w-20"></div>
                                    </td>
                                </tr>
                            </template>
                        </template>

                        {{-- Rows --}}
                        <template x-if="!loading">
                            <template x-for="product in products" :key="product.id">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-3">
                                        <template x-if="product.image_url">
                                            <img :src="product.image_url"
                                                class="w-10 h-10 rounded-lg object-cover border border-gray-100">
                                        </template>
                                        <template x-if="!product.image_url">
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                                <i class="fa-solid fa-image text-gray-400 text-xs"></i>
                                            </div>
                                        </template>
                                    </td>
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-gray-800" x-text="product.name"></p>
                                        <p class="text-xs text-gray-400 font-mono" x-text="product.slug"></p>
                                    </td>
                                    <td class="px-5 py-3 text-gray-600" x-text="product.category?.name ?? '—'"></td>
                                    <td class="px-5 py-3">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs font-medium"
                                            x-text="(product.variants?.length ?? 0) + ' variant' + (product.variants?.length !== 1 ? 's' : '')">
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 font-medium text-gray-900">
                                        ৳<span x-text="Number(product.base_price).toLocaleString()"></span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <button @click="toggleActive(product)"
                                            :class="product.is_active ?
                                                'bg-green-100 text-green-800 hover:bg-green-200' :
                                                'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition cursor-pointer">
                                            <span x-text="product.is_active ? 'Active' : 'Inactive'"></span>
                                        </button>
                                    </td>
                                    <td class="px-5 py-3">
                                        <button @click="confirmLandingPageEnable(product)"
                                            :class="product.is_landing_enabled ?
                                                'bg-green-100 text-green-800 hover:bg-green-200' :
                                                'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition cursor-pointer">
                                            <span x-text="product.is_landing_enabled ? 'Active' : 'Inactive'"></span>
                                        </button>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            @can('product.update')
                                                <a :href="`/admin/products/${product.id}/edit`"
                                                    class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                                </a>
                                            @endcan
                                            @can('product.delete')
                                                <button @click="confirmDelete(product)"
                                                    class="inline-flex items-center gap-1 text-xs text-red-500 hover:text-red-700 font-medium transition cursor-pointer">
                                                    <i class="fa-solid fa-trash"></i> Delete
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>

                        {{-- Empty state --}}
                        <template x-if="!loading && products.length === 0">
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                    <i class="fa-solid fa-leaf text-2xl mb-2 block"></i>
                                    No products found
                                </td>
                            </tr>
                        </template>

                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between" x-show="meta.last_page > 1">
                <p class="text-xs text-gray-500">
                    Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span>
                    &bull; <span x-text="meta.total"></span> total
                </p>
                <div class="flex gap-2">
                    <button @click="loadProducts(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                        class="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition cursor-pointer disabled:cursor-not-allowed">
                        &larr; Prev
                    </button>
                    <button @click="loadProducts(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
                        class="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition cursor-pointer disabled:cursor-not-allowed">
                        Next &rarr;
                    </button>
                </div>
            </div>
        </div>

        {{-- Landing Page Enable Modal --}}
        <div x-show="showLandingEnableModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">

            <div class="absolute inset-0 bg-black/50" @click="showLandingEnableModal = false"></div>

            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-globe text-green-600"></i>
                </div>

                <h3 class="text-base font-bold text-gray-800 mb-1 text-center">Enable Landing Page</h3>
                <p class="text-sm text-gray-500 mb-4 text-center">
                    For: <strong x-text="enableTarget?.name"></strong>
                </p>

                {{-- Slug input --}}
                <label class="block text-xs font-semibold text-gray-600 mb-1">Landing Page Slug</label>
                <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-green-600 mb-1">
                    <span class="px-3 py-2 text-xs text-gray-400 bg-gray-50 border-r border-gray-200 whitespace-nowrap select-none">/product-page/</span>
                    <input
                        type="text"
                        x-model="enableSlug"
                        @input="slugError = ''"
                        placeholder="e.g. mangrove-gold-honey"
                        class="flex-1 px-3 py-2 text-sm outline-none bg-white"
                    >
                </div>
                <p class="text-xs text-red-500 mb-4 min-h-[1rem]" x-text="slugError"></p>

                <p class="text-xs text-gray-400 mb-5">
                    Use lowercase letters, numbers and hyphens only.
                    <template x-if="enableTarget?.landing_slug">
                        <span> Existing slug: <code class="font-mono bg-gray-100 px-1 rounded" x-text="enableTarget.landing_slug"></code></span>
                    </template>
                </p>

                <div class="flex gap-3">
                    <button @click="showLandingEnableModal = false"
                        class="flex-1 px-4 py-2 text-sm font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                        Cancel
                    </button>
                    <button @click="confirmEnableLanding()"
                        :disabled="landingLoading"
                        class="flex-1 px-4 py-2 text-sm font-medium bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white rounded-lg transition cursor-pointer">
                        <span x-show="!landingLoading">Enable</span>
                        <span x-show="landingLoading">Saving…</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Delete Confirm Modal --}}
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div class="absolute inset-0 bg-black/50" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center"
                x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                </div>
                <h3 class="text-base font-bold text-gray-800 mb-1">Delete Product?</h3>
                <p class="text-sm text-gray-500 mb-1">
                    You are about to delete: <strong x-text="deleteTarget?.name"></strong>
                </p>
                <p class="text-xs text-gray-400 mb-5">This will remove the product, all variants, and images permanently.
                </p>
                <div class="flex gap-3 justify-center">
                    <button @click="showDeleteModal = false"
                        class="px-4 py-2 text-sm font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                        Cancel
                    </button>
                    <button @click="deleteProduct()"
                        class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg transition cursor-pointer">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        function productList() {
            return {
                products: [],
                allCategories: [],
                meta: {},
                loading: true,
                search: '',
                filterCategory: '',
                filterStatus: '',
                showDeleteModal: false,
                deleteTarget: null,
                showLandingEnableModal: false,
                enableTarget: null,
                enableSlug: '',
                slugError: '',
                landingLoading: false,

                async init() {
                    await Promise.all([this.loadCategories(), this.loadProducts(1)]);
                },

                async loadCategories() {
                    try {
                        const r = await fetch('/api/v1/admin/categories?per_page=100', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await r.json();
                        this.allCategories = data.data ?? [];
                    } catch (e) {
                        console.error(e);
                    }
                },

                async loadProducts(page = 1) {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({
                            page
                        });
                        if (this.search) params.set('q', this.search);
                        if (this.filterCategory) params.set('category_id', this.filterCategory);
                        if (this.filterStatus) params.set('status', this.filterStatus);

                        const r = await fetch(`/api/v1/admin/products?${params}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await r.json();
                        this.products = data.data ?? [];
                        this.meta = data.meta ?? {};
                    } catch (e) {
                        console.error('Failed to load products', e);
                    } finally {
                        this.loading = false;
                    }
                },

                async toggleActive(product) {
                    try {
                        const r = await fetch(`/api/v1/admin/products/${product.id}/toggle-active`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });
                        const data = await r.json();
                        if (r.ok) {
                            product.is_active = data.data.is_active;
                            window.flash?.('Product status updated Successfully', 'success');
                        }
                    } catch (e) {
                        console.error(e);
                        window.flash?.('Product status updated Unsuccessfully', 'error');
                    }
                },

                /**
                 * Called when the landing-status badge is clicked.
                 * - If currently ACTIVE  → disable immediately (no modal, slug kept).
                 * - If currently INACTIVE → open the slug modal so admin can set/update the slug.
                 */
                async confirmLandingPageEnable(product) {
                    // Disabling: fire immediately, no modal needed.
                    if (product.is_landing_enabled) {
                        await this._sendLandingToggle(product, null);
                        return;
                    }
                    // Enabling: open modal with existing slug pre-filled.
                    this.enableTarget = product;
                    this.enableSlug = product.landing_slug ?? '';
                    this.slugError = '';
                    this.showLandingEnableModal = true;
                },

                /**
                 * Called by the "Enable" button inside the modal.
                 * Validates slug client-side then fires the API call.
                 */
                async confirmEnableLanding() {
                    const slug = this.enableSlug.trim();
                    if (!slug) {
                        this.slugError = 'A landing slug is required.';
                        return;
                    }
                    if (!/^[a-z0-9\-]+$/.test(slug)) {
                        this.slugError = 'Only lowercase letters, numbers, and hyphens allowed.';
                        return;
                    }
                    this.landingLoading = true;
                    await this._sendLandingToggle(this.enableTarget, slug);
                    this.landingLoading = false;
                },

                /**
                 * Shared PATCH call. Handles both enable (with slug) and disable (slug = null).
                 */
                async _sendLandingToggle(product, slug) {
                    try {
                        const body = slug ? { landing_slug: slug } : {};
                        const r = await fetch(`/api/v1/admin/products/${product.id}/toggle-landing-status`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(body),
                        });
                        const data = await r.json();
                        if (r.ok) {
                            // Update the row in-place so the badge flips reactively.
                            product.is_landing_enabled = data.data.is_landing_enabled;
                            product.landing_slug = data.data.landing_slug;
                            this.showLandingEnableModal = false;
                            window.flash?.(
                                `Landing page ${data.data.is_landing_enabled ? 'enabled' : 'disabled'} successfully`,
                                'success'
                            );
                        } else {
                            window.flash?.(data.message ?? 'Update failed', 'error');
                        }
                    } catch (e) {
                        console.error(e);
                        window.flash?.('Landing page update failed', 'error');
                    }
                },

                confirmDelete(product) {
                    this.deleteTarget = product;
                    this.showDeleteModal = true;
                },

                async deleteProduct() {
                    try {
                        const r = await fetch(`/api/v1/admin/products/${this.deleteTarget.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });
                        if (r.ok) {
                            this.showDeleteModal = false;
                            await this.loadProducts(this.meta.current_page ?? 1);
                        } else {
                            const data = await r.json();
                            alert(data.message ?? 'Delete failed.');
                        }
                    } catch (e) {
                        alert('Network error. Please try again.');
                    }
                },
            };
        }
    </script>
@endpush
