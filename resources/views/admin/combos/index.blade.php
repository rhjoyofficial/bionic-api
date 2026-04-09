@extends('layouts.admin')

@section('title', 'Combos')

@section('content')

<div x-data="comboList()" x-init="init()">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Combos</h2>
            <p class="text-sm text-gray-500 mt-0.5">Bundle products together and sell at a special price</p>
        </div>
        @can('product.create')
        <a href="{{ route('admin.combos.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-green-700 text-white rounded-lg hover:bg-green-800 transition">
            <i class="fa-solid fa-plus text-xs"></i> New Combo
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl mb-4 p-4 flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-52">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" x-model="search" @input.debounce.400ms="load(1)"
                placeholder="Search by title…"
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-green-600">
        </div>
        <select x-model="filterStatus" @change="load(1)"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="featured">Featured</option>
        </select>
        <button @click="search=''; filterStatus=''; load(1)" x-show="search || filterStatus"
            class="text-xs text-gray-500 hover:text-red-600 transition cursor-pointer px-2">
            <i class="fa-solid fa-xmark"></i> Clear
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Combo</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Components</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pricing</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Final Price</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stock</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">

                    <template x-if="loading">
                        <template x-for="i in 6" :key="i">
                            <tr><td colspan="7" class="px-5 py-4"><div class="h-4 bg-gray-100 rounded animate-pulse w-full"></div></td></tr>
                        </template>
                    </template>

                    <template x-if="!loading">
                        <template x-for="combo in combos" :key="combo.id">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                                            <img x-show="combo.image" :src="combo.image" :alt="combo.title"
                                                class="w-full h-full object-cover">
                                            <div x-show="!combo.image" class="w-full h-full flex items-center justify-center text-gray-400">
                                                <i class="fa-solid fa-cubes text-xs"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <a :href="`/admin/combos/${combo.id}/edit`"
                                                class="font-semibold text-xs text-gray-800 hover:text-green-700 transition"
                                                x-text="combo.title">
                                            </a>
                                            <p class="text-xs text-gray-400 font-mono" x-text="combo.slug"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-600">
                                    <div class="flex items-center gap-1.5">
                                        <template x-for="item in (combo.items ?? []).slice(0, 3)" :key="item.id">
                                            <div class="w-6 h-6 rounded bg-gray-100 overflow-hidden flex-shrink-0" :title="item.variant?.product?.name + ' — ' + item.variant?.title">
                                                <img x-show="item.variant?.product?.thumbnail" :src="item.variant?.product?.thumbnail"
                                                    class="w-full h-full object-cover">
                                                <div x-show="!item.variant?.product?.thumbnail" class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
                                                    <i class="fa-solid fa-box"></i>
                                                </div>
                                            </div>
                                        </template>
                                        <span class="text-xs text-gray-500 ml-1"
                                            x-text="(combo.items_count ?? combo.items?.length ?? 0) + ' component' + ((combo.items_count ?? combo.items?.length ?? 0) !== 1 ? 's' : '')">
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                                        :class="combo.pricing_mode === 'manual' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'"
                                        x-text="combo.pricing_mode">
                                    </span>
                                    <span x-show="combo.discount_type" class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700">
                                        <span x-text="combo.discount_type === 'percentage' ? combo.discount_value + '% off' : '৳' + combo.discount_value + ' off'"></span>
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-xs font-semibold text-gray-900">
                                    <template x-if="combo.final_price != null">
                                        <span>৳<span x-text="Number(combo.final_price).toLocaleString()"></span></span>
                                    </template>
                                    <template x-if="combo.final_price == null">
                                        <span class="text-gray-400">—</span>
                                    </template>
                                </td>
                                <td class="px-5 py-3 text-xs">
                                    <template x-if="combo.available_stock != null">
                                        <span :class="combo.available_stock === 0 ? 'text-red-600 font-semibold' : combo.available_stock <= 5 ? 'text-yellow-600 font-semibold' : 'text-gray-700'"
                                            x-text="combo.available_stock === 0 ? 'Out of stock' : combo.available_stock + ' sets'">
                                        </span>
                                    </template>
                                    <template x-if="combo.available_stock == null">
                                        <span class="text-gray-400">—</span>
                                    </template>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        @can('product.update')
                                        <button @click="toggleActive(combo)"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium transition cursor-pointer"
                                            :class="combo.is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                            x-text="combo.is_active ? 'Active' : 'Inactive'">
                                        </button>
                                        @endcan
                                        <span x-show="combo.is_featured"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fa-solid fa-star text-xs mr-1"></i> Featured
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        @can('product.update')
                                        <a :href="`/admin/combos/${combo.id}/edit`"
                                            class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        @endcan
                                        @can('product.delete')
                                        <button @click="confirmDelete(combo)"
                                            class="text-xs text-red-500 hover:text-red-700 transition cursor-pointer">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>

                    <template x-if="!loading && combos.length === 0">
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                <i class="fa-solid fa-cubes text-2xl mb-2 block"></i>
                                No combos found
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
                &bull; <span x-text="meta.total"></span> combos
            </p>
            <div class="flex gap-2">
                <button @click="load(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                    class="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition cursor-pointer disabled:cursor-not-allowed">
                    &larr; Prev
                </button>
                <button @click="load(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
                    class="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition cursor-pointer disabled:cursor-not-allowed">
                    Next &rarr;
                </button>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div x-show="showDeleteModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @keydown.escape.window="showDeleteModal = false">
        <div @click.outside="showDeleteModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-trash text-red-600"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Delete Combo</h3>
                    <p class="text-xs text-gray-500">This cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-700 mb-5">Delete <span class="font-semibold" x-text="deleteTarget?.title"></span>?</p>
            <div class="flex justify-end gap-3">
                <button @click="showDeleteModal = false"
                    class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                    Cancel
                </button>
                <button @click="doDelete()" :disabled="deleting"
                    class="px-5 py-2 text-sm font-semibold bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 transition cursor-pointer disabled:cursor-not-allowed">
                    <span x-text="deleting ? 'Deleting…' : 'Delete'"></span>
                </button>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
function comboList() {
    return {
        combos: [], meta: {}, loading: true, search: '', filterStatus: '',
        showDeleteModal: false, deleteTarget: null, deleting: false,

        async init() { await this.load(); },

        async load(page = 1) {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page });
                if (this.search) params.set('q', this.search);
                if (this.filterStatus) params.set('status', this.filterStatus);
                const r = await fetch(`/api/v1/admin/combos?${params}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await r.json();
                this.combos = data.data ?? [];
                this.meta = data.meta ?? {};
            } catch (e) { console.error(e); } finally { this.loading = false; }
        },

        async toggleActive(combo) {
            try {
                const r = await fetch(`/api/v1/admin/combos/${combo.id}/toggle-active`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await r.json();
                if (r.ok) combo.is_active = data.data?.is_active ?? !combo.is_active;
            } catch (e) { console.error(e); }
        },

        confirmDelete(combo) { this.deleteTarget = combo; this.showDeleteModal = true; },

        async doDelete() {
            this.deleting = true;
            try {
                const r = await fetch(`/api/v1/admin/combos/${this.deleteTarget.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                if (r.ok) { this.showDeleteModal = false; await this.load(this.meta.current_page ?? 1); }
            } finally { this.deleting = false; }
        },
    };
}
</script>
@endpush
