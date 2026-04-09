@extends('layouts.admin')

@section('title', 'Customers')

@section('content')

<div x-data="customerList()" x-init="init()">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Customers</h2>
            <p class="text-sm text-gray-500 mt-0.5">Manage registered customers and their accounts</p>
        </div>
        <div class="text-sm text-gray-500" x-show="meta.total !== undefined">
            <span x-text="meta.total + ' customers'"></span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl mb-4 p-4 flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-52">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" x-model="search" @input.debounce.400ms="load(1)"
                placeholder="Name, email, or phone…"
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-green-600">
        </div>

        <select x-model="filterStatus" @change="load(1)"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
            <option value="">All Customers</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="guest">Guest</option>
        </select>

        <button @click="clearFilters()" x-show="search || filterStatus"
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
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Phone</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Orders</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Spent</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Joined</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">

                    <template x-if="loading">
                        <template x-for="i in 10" :key="i">
                            <tr>
                                <td colspan="7" class="px-5 py-4">
                                    <div class="h-4 bg-gray-100 rounded animate-pulse w-full"></div>
                                </td>
                            </tr>
                        </template>
                    </template>

                    <template x-if="!loading">
                        <template x-for="c in customers" :key="c.id">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-700 text-xs font-bold uppercase flex-shrink-0"
                                            x-text="c.name.charAt(0)">
                                        </div>
                                        <div>
                                            <a :href="`/admin/customers/${c.id}`"
                                                class="font-medium text-gray-800 text-xs hover:text-green-700 transition"
                                                x-text="c.name">
                                            </a>
                                            <p class="text-xs text-gray-400" x-text="c.email ?? '—'"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-600" x-text="c.phone"></td>
                                <td class="px-5 py-3 text-xs text-gray-600" x-text="c.orders_count ?? 0"></td>
                                <td class="px-5 py-3 text-xs font-semibold text-gray-900">
                                    ৳<span x-text="Number(c.orders_sum_grand_total ?? 0).toLocaleString()"></span>
                                </td>
                                <td class="px-5 py-3">
                                    <template x-if="c.is_guest">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Guest</span>
                                    </template>
                                    <template x-if="!c.is_guest">
                                        <button @click="toggleActive(c)"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium transition cursor-pointer"
                                            :class="c.is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200'"
                                            x-text="c.is_active ? 'Active' : 'Inactive'">
                                        </button>
                                    </template>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-400"
                                    x-text="c.created_at ? new Date(c.created_at).toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'2-digit'}) : '—'">
                                </td>
                                <td class="px-5 py-3">
                                    <a :href="`/admin/customers/${c.id}`"
                                        class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        </template>
                    </template>

                    <template x-if="!loading && customers.length === 0">
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                <i class="fa-solid fa-users text-2xl mb-2 block"></i>
                                No customers found
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
                &bull; <span x-text="meta.total"></span> customers
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

</div>

@endsection

@push('scripts')
<script>
function customerList() {
    return {
        customers: [],
        meta: {},
        loading: true,
        search: '',
        filterStatus: '',

        async init() {
            await this.load();
        },

        async load(page = 1) {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page });
                if (this.search) params.set('q', this.search);
                if (this.filterStatus) params.set('status', this.filterStatus);

                const r = await fetch(`/api/v1/admin/customers?${params}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await r.json();
                this.customers = data.data ?? [];
                this.meta = data.meta ?? {};
            } catch (e) {
                console.error('Failed to load customers', e);
            } finally {
                this.loading = false;
            }
        },

        clearFilters() {
            this.search = '';
            this.filterStatus = '';
            this.load(1);
        },

        async toggleActive(customer) {
            try {
                const r = await fetch(`/api/v1/admin/customers/${customer.id}/toggle-active`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await r.json();
                if (data.data?.is_active !== undefined) {
                    customer.is_active = data.data.is_active;
                }
            } catch (e) {
                console.error('Failed to toggle active', e);
            }
        },
    };
}
</script>
@endpush
