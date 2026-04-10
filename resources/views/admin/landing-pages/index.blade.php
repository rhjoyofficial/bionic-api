@extends('layouts.admin')

@section('title', 'Landing Pages')

@section('content')

    <div x-data="landingPageManager()" x-init="init()">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Landing Pages</h2>
                <p class="text-sm text-gray-500 mt-0.5">Manage product, combo, and sales landing pages</p>
            </div>
            @can('product.create')
                <a href="{{ route('admin.landing-pages.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-green-700 text-white rounded-lg hover:bg-green-800 transition cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i> New Landing Page
                </a>
            @endcan
        </div>

        {{-- Filters --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 mb-5">
            <div class="flex flex-col sm:flex-row gap-3">
                <input x-model.debounce.300ms="filters.search" @input="fetchPages()"
                       type="text" placeholder="Search by title or slug..."
                       class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                <select x-model="filters.type" @change="fetchPages()"
                        class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                    <option value="">All Types</option>
                    <option value="product">Product</option>
                    <option value="combo">Combo</option>
                    <option value="sales">Sales</option>
                </select>
                <select x-model="filters.is_active" @change="fetchPages()"
                        class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Title</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Slug</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Type</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Linked To</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="loading">
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-400">
                                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                                </td>
                            </tr>
                        </template>
                        <template x-if="!loading && pages.length === 0">
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-400">No landing pages found.</td>
                            </tr>
                        </template>
                        <template x-for="page in pages" :key="page.id">
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-800" x-text="page.title"></p>
                                </td>
                                <td class="px-4 py-3">
                                    <a :href="'/landing/' + page.slug" target="_blank"
                                       class="text-green-700 hover:underline text-xs font-mono" x-text="page.slug"></a>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                          :class="{
                                              'bg-blue-100 text-blue-700': page.type === 'product',
                                              'bg-purple-100 text-purple-700': page.type === 'combo',
                                              'bg-amber-100 text-amber-700': page.type === 'sales',
                                          }"
                                          x-text="page.type.charAt(0).toUpperCase() + page.type.slice(1)"></span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">
                                    <template x-if="page.product">
                                        <span x-text="page.product.name"></span>
                                    </template>
                                    <template x-if="page.combo">
                                        <span x-text="page.combo.name"></span>
                                    </template>
                                    <template x-if="page.type === 'sales'">
                                        <span class="text-gray-400">Multiple items</span>
                                    </template>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button @click="toggleActive(page)"
                                            class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors cursor-pointer"
                                            :class="page.is_active ? 'bg-green-500' : 'bg-gray-300'">
                                        <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform shadow"
                                              :class="page.is_active ? 'translate-x-4' : 'translate-x-0.5'"></span>
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a :href="'/admin/landing-pages/' + page.id + '/edit'"
                                           class="text-gray-400 hover:text-green-700 transition">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        @can('product.delete')
                                            <button @click="deletePage(page)"
                                                    class="text-gray-400 hover:text-red-600 transition cursor-pointer">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div x-show="pagination.lastPage > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-100">
                <p class="text-xs text-gray-500">
                    Showing <span x-text="pagination.from"></span>-<span x-text="pagination.to"></span>
                    of <span x-text="pagination.total"></span>
                </p>
                <div class="flex gap-1">
                    <button @click="goToPage(pagination.currentPage - 1)" :disabled="pagination.currentPage <= 1"
                            class="px-3 py-1 text-xs rounded border border-gray-200 hover:bg-gray-50 disabled:opacity-50 cursor-pointer">
                        Prev
                    </button>
                    <button @click="goToPage(pagination.currentPage + 1)" :disabled="pagination.currentPage >= pagination.lastPage"
                            class="px-3 py-1 text-xs rounded border border-gray-200 hover:bg-gray-50 disabled:opacity-50 cursor-pointer">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function landingPageManager() {
        return {
            pages: [],
            loading: true,
            filters: { search: '', type: '', is_active: '' },
            pagination: { currentPage: 1, lastPage: 1, from: 0, to: 0, total: 0 },

            async init() {
                await this.fetchPages();
            },

            async fetchPages(page = 1) {
                this.loading = true;
                const params = new URLSearchParams({
                    page,
                    per_page: 15,
                    ...Object.fromEntries(Object.entries(this.filters).filter(([_, v]) => v !== '')),
                });

                try {
                    const res = await fetch(`/api/admin/landing-pages?${params}`, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    const json = await res.json();
                    if (json.success) {
                        this.pages = json.data.data;
                        this.pagination = {
                            currentPage: json.data.current_page,
                            lastPage: json.data.last_page,
                            from: json.data.from || 0,
                            to: json.data.to || 0,
                            total: json.data.total || 0,
                        };
                    }
                } catch (e) {
                    console.error('Failed to fetch landing pages:', e);
                }
                this.loading = false;
            },

            goToPage(page) {
                if (page >= 1 && page <= this.pagination.lastPage) {
                    this.fetchPages(page);
                }
            },

            async toggleActive(page) {
                try {
                    const res = await fetch(`/api/admin/landing-pages/${page.id}/toggle-active`, {
                        method: 'PATCH',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    const json = await res.json();
                    if (json.success) {
                        page.is_active = json.data.is_active;
                    }
                } catch (e) {
                    console.error('Failed to toggle landing page:', e);
                }
            },

            async deletePage(page) {
                if (!confirm(`Delete landing page "${page.title}"? This cannot be undone.`)) return;
                try {
                    const res = await fetch(`/api/admin/landing-pages/${page.id}`, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    const json = await res.json();
                    if (json.success) {
                        this.pages = this.pages.filter(p => p.id !== page.id);
                    }
                } catch (e) {
                    console.error('Failed to delete landing page:', e);
                }
            },
        };
    }
    </script>

@endsection
