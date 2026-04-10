@extends('layouts.admin')

@section('title', 'Orders')

@section('content')

<div x-data="orderList()" x-init="init()">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Orders</h2>
            <p class="text-sm text-gray-500 mt-0.5">Manage and track all customer orders</p>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-500" x-show="meta.total !== undefined">
            <span x-text="meta.total + ' orders'"></span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl mb-4 p-4 flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-52">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" x-model="search" @input.debounce.400ms="loadOrders(1)"
                placeholder="Order #, phone, or name…"
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-green-600">
        </div>

        <select x-model="filterStatus" @change="loadOrders(1)"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
            <option value="returned">Returned</option>
        </select>

        <select x-model="filterPayment" @change="loadOrders(1)"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
            <option value="">All Payment</option>
            <option value="cod">COD</option>
            <option value="sslcommerz">SSLCommerz</option>
        </select>

        <select x-model="filterPaymentStatus" @change="loadOrders(1)"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
            <option value="">All Payment Status</option>
            <option value="unpaid">Unpaid</option>
            <option value="paid">Paid</option>
            <option value="failed">Failed</option>
        </select>

        <div class="flex gap-2">
            <input type="date" x-model="dateFrom" @change="loadOrders(1)"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600"
                placeholder="From">
            <input type="date" x-model="dateTo" @change="loadOrders(1)"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600"
                placeholder="To">
        </div>

        <button @click="clearFilters()" x-show="hasFilters"
            class="text-xs text-gray-500 hover:text-red-600 transition cursor-pointer px-2">
            <i class="fa-solid fa-xmark"></i> Clear
        </button>
    </div>

    {{-- Bulk Courier Assignment Bar --}}
    @can('order.update')
    <div x-show="selectedOrders.length > 0" x-cloak
         class="bg-blue-50 border border-blue-200 rounded-xl mb-4 px-5 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-sm font-semibold text-blue-800">
                <i class="fas fa-check-circle mr-1"></i>
                <span x-text="selectedOrders.length"></span> order(s) selected
            </span>
            <button @click="selectedOrders = []" class="text-xs text-blue-600 hover:text-blue-800 underline">
                Clear selection
            </button>
        </div>
        <div class="flex items-center gap-2">
            <select x-model="bulkCourier"
                    class="border border-blue-300 rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select courier…</option>
                <option value="pathao">Pathao</option>
                <option value="steadfast">Steadfast</option>
                <option value="carrybee">CarryBee</option>
            </select>
            <button @click="bulkAssignCourier()" :disabled="!bulkCourier || bulkAssigning"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition">
                <i class="fas" :class="bulkAssigning ? 'fa-spinner fa-spin' : 'fa-truck-fast'"></i>
                <span x-text="bulkAssigning ? 'Assigning…' : 'Assign Courier'"></span>
            </button>
        </div>
    </div>

    {{-- Bulk Result --}}
    <div x-show="bulkResult" x-cloak
         class="mb-4 rounded-xl border px-5 py-3 text-sm"
         :class="bulkResult?.errors?.length > 0 ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-green-50 border-green-200 text-green-800'">
        <div class="flex items-center justify-between">
            <span x-text="bulkResult?.message || ''"></span>
            <button @click="bulkResult = null" class="text-xs opacity-60 hover:opacity-100">&times;</button>
        </div>
        <template x-if="bulkResult?.errors?.length > 0">
            <ul class="mt-2 space-y-0.5 text-xs">
                <template x-for="err in bulkResult.errors" :key="err.order_id">
                    <li><span class="font-mono" x-text="err.order_number"></span>: <span x-text="err.message"></span></li>
                </template>
            </ul>
        </template>
    </div>
    @endcan

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        @can('order.update')
                        <th class="px-3 py-3 w-10">
                            <input type="checkbox" @change="toggleSelectAll($event)" :checked="allSelected"
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer">
                        </th>
                        @endcan
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Payment</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Courier</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">

                    <template x-if="loading">
                        <template x-for="i in 10" :key="i">
                            <tr>
                                <td :colspan="canUpdate ? 11 : 10" class="px-5 py-4"><div class="h-4 bg-gray-100 rounded animate-pulse w-full"></div></td>
                            </tr>
                        </template>
                    </template>

                    <template x-if="!loading">
                        <template x-for="order in orders" :key="order.id">
                            <tr class="hover:bg-gray-50 transition">
                                @can('order.update')
                                <td class="px-3 py-3">
                                    <input type="checkbox" :value="order.id"
                                           @change="toggleSelect(order.id)"
                                           :checked="selectedOrders.includes(order.id)"
                                           class="rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer">
                                </td>
                                @endcan
                                <td class="px-5 py-3">
                                    <a :href="`/admin/orders/${order.id}`"
                                        class="font-mono text-xs font-semibold text-green-700 hover:underline"
                                        x-text="'#' + order.order_number">
                                    </a>
                                </td>
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-800 text-xs" x-text="order.customer_name"></p>
                                    <p class="text-xs text-gray-400" x-text="order.customer_phone"></p>
                                </td>
                                <td class="px-5 py-3 text-gray-600 text-xs" x-text="(order.items_count ?? '—') + ' item' + (order.items_count !== 1 ? 's' : '')"></td>
                                <td class="px-5 py-3 font-semibold text-gray-900 text-xs">
                                    ৳<span x-text="Number(order.grand_total).toLocaleString()"></span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                                        :class="statusColor(order.order_status)"
                                        x-text="order.order_status">
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-xs text-gray-500 uppercase" x-text="order.payment_method"></span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                            :class="paymentStatusColor(order.payment_status)"
                                            x-text="order.payment_status">
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <template x-if="order.shipments && order.shipments.length > 0">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-xs font-medium text-gray-700" x-text="order.shipments[0].courier_label"></span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium"
                                                  :class="shipmentStatusColor(order.shipments[0].status)"
                                                  x-text="order.shipments[0].status_label"></span>
                                        </div>
                                    </template>
                                    <template x-if="!order.shipments || order.shipments.length === 0">
                                        <span class="text-xs text-gray-400">—</span>
                                    </template>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-400" x-text="order.placed_at ? new Date(order.placed_at).toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'2-digit'}) : '—'"></td>
                                <td class="px-5 py-3">
                                    <a :href="`/admin/orders/${order.id}`"
                                        class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        </template>
                    </template>

                    <template x-if="!loading && orders.length === 0">
                        <tr>
                            <td :colspan="canUpdate ? 11 : 10" class="px-5 py-12 text-center text-gray-400">
                                <i class="fa-solid fa-box text-2xl mb-2 block"></i>
                                No orders found
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
                &bull; <span x-text="meta.total"></span> orders
            </p>
            <div class="flex gap-2">
                <button @click="loadOrders(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                    class="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition cursor-pointer disabled:cursor-not-allowed">
                    &larr; Prev
                </button>
                <button @click="loadOrders(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
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
function orderList() {
    return {
        orders: [],
        meta: {},
        loading: true,
        search: '',
        filterStatus: '',
        filterPayment: '',
        filterPaymentStatus: '',
        dateFrom: '',
        dateTo: '',
        customerId: null,

        // Bulk courier
        selectedOrders: [],
        bulkCourier: '',
        bulkAssigning: false,
        bulkResult: null,
        canUpdate: @can('order.update') true @else false @endcan,

        get hasFilters() {
            return this.search || this.filterStatus || this.filterPayment || this.filterPaymentStatus || this.dateFrom || this.dateTo;
        },

        get allSelected() {
            return this.orders.length > 0 && this.orders.every(o => this.selectedOrders.includes(o.id));
        },

        async init() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('customer_id')) this.customerId = urlParams.get('customer_id');
            await this.loadOrders();
        },

        async loadOrders(page = 1) {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page });
                if (this.search) params.set('q', this.search);
                if (this.filterStatus) params.set('status', this.filterStatus);
                if (this.filterPayment) params.set('payment', this.filterPayment);
                if (this.filterPaymentStatus) params.set('payment_status', this.filterPaymentStatus);
                if (this.dateFrom) params.set('date_from', this.dateFrom);
                if (this.dateTo) params.set('date_to', this.dateTo);
                if (this.customerId) params.set('customer_id', this.customerId);

                const r = await fetch(`/api/v1/admin/orders?${params}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await r.json();
                this.orders = data.data ?? [];
                this.meta = data.meta ?? {};
                this.selectedOrders = [];
            } catch (e) {
                console.error('Failed to load orders', e);
            } finally {
                this.loading = false;
            }
        },

        toggleSelect(orderId) {
            const idx = this.selectedOrders.indexOf(orderId);
            if (idx === -1) {
                this.selectedOrders.push(orderId);
            } else {
                this.selectedOrders.splice(idx, 1);
            }
        },

        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedOrders = this.orders.map(o => o.id);
            } else {
                this.selectedOrders = [];
            }
        },

        async bulkAssignCourier() {
            if (!this.bulkCourier || this.selectedOrders.length === 0) return;
            this.bulkAssigning = true;
            this.bulkResult = null;
            try {
                const r = await fetch('/api/v1/admin/courier/bulk-assign', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        order_ids: this.selectedOrders,
                        courier: this.bulkCourier,
                    }),
                });
                const data = await r.json();
                this.bulkResult = {
                    message: data.message,
                    errors: data.data?.errors ?? [],
                };
                this.selectedOrders = [];
                this.bulkCourier = '';
                await this.loadOrders(this.meta.current_page || 1);
            } catch (e) {
                this.bulkResult = { message: 'Network error.', errors: [] };
            } finally {
                this.bulkAssigning = false;
            }
        },

        clearFilters() {
            this.search = '';
            this.filterStatus = '';
            this.filterPayment = '';
            this.filterPaymentStatus = '';
            this.dateFrom = '';
            this.dateTo = '';
            this.loadOrders(1);
        },

        statusColor(s) {
            const c = {
                pending: 'bg-yellow-100 text-yellow-800',
                confirmed: 'bg-blue-100 text-blue-800',
                processing: 'bg-indigo-100 text-indigo-800',
                shipped: 'bg-cyan-100 text-cyan-800',
                delivered: 'bg-green-100 text-green-800',
                cancelled: 'bg-red-100 text-red-800',
                returned: 'bg-gray-100 text-gray-700',
            };
            return c[s] ?? 'bg-gray-100 text-gray-700';
        },

        paymentStatusColor(s) {
            return s === 'paid' ? 'bg-green-100 text-green-800'
                : s === 'failed' ? 'bg-red-100 text-red-800'
                : 'bg-yellow-100 text-yellow-800';
        },

        shipmentStatusColor(s) {
            const c = {
                pending:          'bg-yellow-100 text-yellow-700',
                picked_up:        'bg-blue-100 text-blue-700',
                in_transit:       'bg-indigo-100 text-indigo-700',
                out_for_delivery: 'bg-cyan-100 text-cyan-700',
                delivered:        'bg-green-100 text-green-700',
                partial_delivery: 'bg-amber-100 text-amber-700',
                cancelled:        'bg-red-100 text-red-700',
                returned:         'bg-gray-100 text-gray-600',
                on_hold:          'bg-orange-100 text-orange-700',
            };
            return c[s] ?? 'bg-gray-100 text-gray-600';
        },
    };
}
</script>
@endpush
