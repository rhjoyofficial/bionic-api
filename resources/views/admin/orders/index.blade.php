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
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500" x-show="meta.total !== undefined" x-text="meta.total + ' orders'"></span>
            @can('order.create')
            <button @click="showImportModal = true"
               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition cursor-pointer">
                <i class="fas fa-file-import text-xs"></i> Import
            </button>
            <a href="{{ route('admin.orders.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-plus text-xs"></i> Create Order
            </a>
            @endcan
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
            <button @click="exportSelected()"
                    class="mr-2 inline-flex items-center gap-1.5 px-4 py-1.5 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition cursor-pointer">
                <i class="fas fa-file-export text-xs"></i> Export Data
            </button>
            <button @click="openBulkPathaoModal()"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition cursor-pointer">
                <i class="fas fa-paper-plane text-xs"></i> Send to Pathao
            </button>
        </div>
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

    {{-- Import Modal --}}
    <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
        <div @click.away="if(!importing) showImportModal = false" class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-800">Import Orders</h3>
                <button @click="if(!importing) showImportModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">&times;</button>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Upload a CSV file containing order data. Ensure you match the required column formats.</p>
                    <a href="/api/v1/admin/orders/import-template" class="text-xs font-semibold text-blue-600 hover:text-blue-800 underline">
                        <i class="fas fa-download mr-1"></i> Download CSV Template
                    </a>
                </div>
                
                <div class="mb-5">
                    <input type="file" x-ref="importFileInput" accept=".csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                </div>

                <div x-show="importResult" class="mb-4 text-xs p-3 rounded-lg bg-gray-50 border border-gray-200" x-html="importResult"></div>

                <button @click="importOrders" :disabled="importing" class="w-full py-2.5 bg-purple-600 text-white font-medium rounded-xl hover:bg-purple-700 transition disabled:opacity-50 flex justify-center items-center gap-2 cursor-pointer">
                    <i class="fas bg-transparent" :class="importing ? 'fa-spinner fa-spin' : 'fa-upload'"></i>
                    <span x-text="importing ? 'Importing...' : 'Upload & Import'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Bulk Pathao Modal (one-by-one) ── --}}
    <div x-show="showBulkPathaoModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @keydown.escape.window="showBulkPathaoModal = false">
        <div class="absolute inset-0 bg-black/50" @click="showBulkPathaoModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto" @click.stop>

            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-800"><i class="fas fa-paper-plane text-green-600 mr-2"></i>Send to Pathao</h2>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Order <span class="font-semibold text-gray-600" x-text="bulkQueue[bulkQueueIdx]?.order_number ?? ''"></span>
                        — <span x-text="(bulkQueueIdx + 1) + ' of ' + bulkQueue.length"></span>
                    </p>
                </div>
                <button @click="showBulkPathaoModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer"><i class="fas fa-xmark text-lg"></i></button>
            </div>

            <div class="p-6 space-y-4">
                {{-- City --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">City <span class="text-red-500">*</span></label>
                    <select x-model="bulkPathao.city_id" @change="loadBulkZones()" :disabled="bulkPathao.loadingCities"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                        <option value="">-- Select City --</option>
                        <template x-for="c in bulkPathao.cities" :key="c.city_id">
                            <option :value="c.city_id" x-text="c.city_name"></option>
                        </template>
                    </select>
                </div>

                {{-- Zone --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Zone <span class="text-red-500">*</span></label>
                    <select x-model="bulkPathao.zone_id" @change="loadBulkAreas()" :disabled="!bulkPathao.city_id || bulkPathao.loadingZones"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                        <option value="">-- Select Zone --</option>
                        <template x-for="z in bulkPathao.zones" :key="z.zone_id">
                            <option :value="z.zone_id" x-text="z.zone_name"></option>
                        </template>
                    </select>
                </div>

                {{-- Area --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Area</label>
                    <select x-model="bulkPathao.area_id" :disabled="!bulkPathao.zone_id || bulkPathao.loadingAreas"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                        <option value="">-- Select Area (optional) --</option>
                        <template x-for="a in bulkPathao.areas" :key="a.area_id">
                            <option :value="a.area_id" x-text="a.area_name"></option>
                        </template>
                    </select>
                </div>

                {{-- Address + Phone --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Shipping Address <span class="text-red-500">*</span></label>
                    <input type="text" x-model="bulkPathao.shipping_address"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Shipping Mobile <span class="text-red-500">*</span></label>
                        <input type="text" x-model="bulkPathao.shipping_phone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Alternative Mobile</label>
                        <input type="text" x-model="bulkPathao.alternative_phone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="optional">
                    </div>
                </div>

                {{-- Weight --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Weight (kg) <span class="text-red-500">*</span></label>
                    <select x-model="bulkPathao.item_weight"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                        <option value="0.5">0.5 kg</option>
                        <option value="1">1 kg</option>
                        <option value="1.5">1.5 kg</option>
                        <option value="2">2 kg</option>
                        <option value="3">3 kg</option>
                        <option value="5">5 kg</option>
                        <option value="10">10 kg</option>
                    </select>
                </div>

                {{-- Note --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Shipping Note</label>
                    <textarea x-model="bulkPathao.shipping_note" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none resize-none"></textarea>
                </div>

                {{-- Progress --}}
                <div x-show="bulkResults.length > 0" class="text-xs space-y-1 max-h-28 overflow-y-auto border border-gray-100 rounded-lg p-2 bg-gray-50">
                    <template x-for="(r, i) in bulkResults" :key="i">
                        <div :class="r.ok ? 'text-green-700' : 'text-red-600'" x-text="r.msg"></div>
                    </template>
                </div>

                <div x-show="bulkPathao.error" x-cloak class="text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2" x-text="bulkPathao.error"></div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex justify-between gap-3">
                <button @click="skipBulkOrder()"
                    class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition cursor-pointer">
                    Skip this order
                </button>
                <div class="flex gap-2">
                    <button @click="showBulkPathaoModal = false"
                        class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition cursor-pointer">
                        Close
                    </button>
                    <button @click="submitBulkPathaoOrder()" :disabled="bulkPathao.submitting || !bulkPathao.city_id || !bulkPathao.zone_id"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 transition cursor-pointer">
                        <i class="fas" :class="bulkPathao.submitting ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                        <span x-text="bulkPathao.submitting ? 'Sending…' : (bulkQueueIdx < bulkQueue.length - 1 ? 'Submit & Next' : 'Submit & Finish')" ></span>
                    </button>
                </div>
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

        // Bulk selection
        selectedOrders: [],
        canUpdate: @can('order.update') true @else false @endcan,

        // Bulk Pathao (one-by-one)
        showBulkPathaoModal: false,
        bulkQueue: [],
        bulkQueueIdx: 0,
        bulkResults: [],
        bulkPathao: {
            city_id: '', zone_id: '', area_id: '',
            shipping_address: '', shipping_phone: '', alternative_phone: '',
            item_weight: '0.5', shipping_note: '',
            cities: [], zones: [], areas: [],
            loadingCities: false, loadingZones: false, loadingAreas: false,
            submitting: false, error: null,
        },

        // Import State
        showImportModal: false,
        importing: false,
        importResult: null,

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

        exportSelected() {
            if (this.selectedOrders.length === 0) return;
            const ids = this.selectedOrders.join(',');
            window.location.href = `/api/v1/admin/orders/export-bulk?ids=${ids}`;
        },

        async importOrders() {
            const fileInput = this.$refs.importFileInput;
            if (!fileInput.files || fileInput.files.length === 0) {
                this.importResult = '<span class="text-red-500">Please select a file to upload.</span>';
                return;
            }

            this.importing = true;
            this.importResult = 'Uploading and processing, please wait...';

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            try {
                const r = await fetch('/api/v1/admin/orders/import-bulk', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                
                const data = await r.json();
                if (r.ok) {
                    let resHtml = `<p class="text-green-600 font-bold mb-1">${data.message}</p>`;
                    if (data.data?.errors?.length > 0) {
                        resHtml += `<ul class="text-red-500 list-disc list-inside mt-2 max-h-32 overflow-y-auto">`;
                        data.data.errors.forEach(e => resHtml += `<li>${e}</li>`);
                        resHtml += `</ul>`;
                    }
                    this.importResult = resHtml;
                    this.loadOrders(1);
                    fileInput.value = ''; // clear input
                } else {
                    this.importResult = `<span class="text-red-500">${data.message || 'Import failed'}</span>`;
                }
            } catch (e) {
                this.importResult = '<span class="text-red-500">Network error during upload.</span>';
            } finally {
                this.importing = false;
            }
        },

        openBulkPathaoModal() {
            if (this.selectedOrders.length === 0) return;
            this.bulkQueue = this.orders.filter(o => this.selectedOrders.includes(o.id));
            this.bulkQueueIdx = 0;
            this.bulkResults = [];
            const first = this.bulkQueue[0];
            this.bulkPathao.city_id = '';
            this.bulkPathao.zone_id = '';
            this.bulkPathao.area_id = '';
            this.bulkPathao.shipping_address = first?.shipping_address ?? '';
            this.bulkPathao.shipping_phone = first?.customer_phone ?? '';
            this.bulkPathao.alternative_phone = '';
            this.bulkPathao.item_weight = '0.5';
            this.bulkPathao.shipping_note = '';
            this.bulkPathao.zones = [];
            this.bulkPathao.areas = [];
            this.bulkPathao.error = null;
            this.showBulkPathaoModal = true;
            if (this.bulkPathao.cities.length === 0) this.loadBulkCities();
        },

        async loadBulkCities() {
            this.bulkPathao.loadingCities = true;
            try {
                const r = await fetch('/api/v1/admin/courier/pathao/cities', { headers: { 'Accept': 'application/json' } });
                const data = await r.json();
                this.bulkPathao.cities = data.data ?? [];
            } catch (e) {
                this.bulkPathao.error = 'Failed to load cities.';
            } finally {
                this.bulkPathao.loadingCities = false;
            }
        },

        async loadBulkZones() {
            if (!this.bulkPathao.city_id) return;
            this.bulkPathao.zone_id = '';
            this.bulkPathao.area_id = '';
            this.bulkPathao.zones = [];
            this.bulkPathao.areas = [];
            this.bulkPathao.loadingZones = true;
            try {
                const r = await fetch(`/api/v1/admin/courier/pathao/zones/${this.bulkPathao.city_id}`, { headers: { 'Accept': 'application/json' } });
                const data = await r.json();
                this.bulkPathao.zones = data.data ?? [];
            } catch (e) {
                this.bulkPathao.error = 'Failed to load zones.';
            } finally {
                this.bulkPathao.loadingZones = false;
            }
        },

        async loadBulkAreas() {
            if (!this.bulkPathao.zone_id) return;
            this.bulkPathao.area_id = '';
            this.bulkPathao.areas = [];
            this.bulkPathao.loadingAreas = true;
            try {
                const r = await fetch(`/api/v1/admin/courier/pathao/areas/${this.bulkPathao.zone_id}`, { headers: { 'Accept': 'application/json' } });
                const data = await r.json();
                this.bulkPathao.areas = data.data ?? [];
            } catch (e) {
                this.bulkPathao.error = 'Failed to load areas.';
            } finally {
                this.bulkPathao.loadingAreas = false;
            }
        },

        async submitBulkPathaoOrder() {
            const order = this.bulkQueue[this.bulkQueueIdx];
            if (!order) return;
            this.bulkPathao.submitting = true;
            this.bulkPathao.error = null;
            try {
                const r = await fetch('/api/v1/admin/courier/assign', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: order.id,
                        courier: 'pathao',
                        pathao_city_id: this.bulkPathao.city_id,
                        pathao_zone_id: this.bulkPathao.zone_id,
                        pathao_area_id: this.bulkPathao.area_id || null,
                        shipping_address: this.bulkPathao.shipping_address,
                        shipping_phone: this.bulkPathao.shipping_phone,
                        alternative_phone: this.bulkPathao.alternative_phone || null,
                        item_weight: parseFloat(this.bulkPathao.item_weight),
                        shipping_note: this.bulkPathao.shipping_note || null,
                    }),
                });
                const data = await r.json();
                if (r.ok) {
                    this.bulkResults.push({ ok: true, msg: `✓ ${order.order_number}: ${data.message ?? 'Assigned'}` });
                    window.triggerFlash?.(data.message ?? 'Shipment created', 'success');
                    this.advanceBulkQueue();
                } else {
                    this.bulkPathao.error = data.message ?? 'Failed to assign';
                    this.bulkResults.push({ ok: false, msg: `✗ ${order.order_number}: ${data.message ?? 'Failed'}` });
                }
            } catch (e) {
                this.bulkPathao.error = 'Network error.';
                this.bulkResults.push({ ok: false, msg: `✗ ${order.order_number}: Network error` });
            } finally {
                this.bulkPathao.submitting = false;
            }
        },

        skipBulkOrder() {
            const order = this.bulkQueue[this.bulkQueueIdx];
            if (order) this.bulkResults.push({ ok: false, msg: `— ${order.order_number}: Skipped` });
            this.advanceBulkQueue();
        },

        advanceBulkQueue() {
            if (this.bulkQueueIdx < this.bulkQueue.length - 1) {
                this.bulkQueueIdx++;
                const next = this.bulkQueue[this.bulkQueueIdx];
                this.bulkPathao.shipping_address = next?.shipping_address ?? '';
                this.bulkPathao.shipping_phone = next?.customer_phone ?? '';
                this.bulkPathao.alternative_phone = '';
                this.bulkPathao.error = null;
            } else {
                this.showBulkPathaoModal = false;
                this.selectedOrders = [];
                this.loadOrders(this.meta.current_page || 1);
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
