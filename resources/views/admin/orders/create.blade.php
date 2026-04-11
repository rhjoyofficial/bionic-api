@extends('layouts.admin')

@section('title', 'Create New Order')

@section('content')

<div x-data="createOrder()" x-init="init()" x-cloak>

    {{-- Page Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.orders') }}" class="text-gray-400 hover:text-gray-700 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-lg font-bold text-gray-800">Create New Order</h2>
            <p class="text-sm text-gray-500">Manual order creation from admin panel</p>
        </div>
    </div>

    {{-- Success Banner --}}
    <div x-show="createdOrder" x-transition class="mb-6 bg-green-50 border border-green-200 rounded-xl p-5 flex items-start gap-4">
        <i class="fas fa-circle-check text-green-500 text-xl mt-0.5"></i>
        <div>
            <p class="font-semibold text-green-800">Order created successfully!</p>
            <p class="text-sm text-green-700 mt-1">
                Order <span class="font-mono font-bold" x-text="createdOrder?.order_number"></span>
                — Total <span class="font-bold" x-text="'৳' + (createdOrder?.grand_total ?? 0).toLocaleString()"></span>
            </p>
            <div class="mt-3 flex gap-3">
                <a :href="`/admin/orders/${createdOrder?.id}`"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-eye"></i> View Order
                </a>
                <button @click="resetForm()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-plus"></i> Create Another
                </button>
            </div>
        </div>
    </div>

    <div x-show="!createdOrder">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- ══════════════════════════════
                 LEFT: Customer + Items
            ══════════════════════════════ --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- Customer Information --}}
                <div class="bg-white border border-gray-200 rounded-xl">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-bold text-gray-700"><i class="fas fa-user mr-2 text-gray-400"></i>Customer Information</h3>
                    </div>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" x-model="form.customer_name"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Customer full name">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Phone Number <span class="text-red-500">*</span></label>
                            <input type="text" x-model="form.customer_phone"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="01XXXXXXXXX">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email Address</label>
                            <input type="email" x-model="form.customer_email"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Optional">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Link to Registered Customer</label>
                            <input type="text" x-model="userSearch"
                                   @input.debounce.400ms="searchUsers()"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Search by phone / name…">
                            <div x-show="userResults.length" class="mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-40 overflow-y-auto z-30 relative">
                                <template x-for="u in userResults" :key="u.id">
                                    <div @click="selectUser(u)" class="px-3 py-2 text-sm cursor-pointer hover:bg-blue-50 flex items-center gap-2">
                                        <i class="fas fa-user text-gray-400 text-xs"></i>
                                        <span x-text="u.name"></span>
                                        <span class="text-gray-400 text-xs" x-text="u.phone"></span>
                                    </div>
                                </template>
                            </div>
                            <p x-show="form.linked_user_id" class="text-xs text-green-600 mt-1">
                                <i class="fas fa-link"></i> Linked to: <span class="font-semibold" x-text="selectedUserName"></span>
                                <button @click="clearUser()" class="ml-2 text-red-500 hover:text-red-700"><i class="fas fa-xmark"></i></button>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Shipping Address --}}
                <div class="bg-white border border-gray-200 rounded-xl">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-bold text-gray-700"><i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>Shipping Address</h3>
                    </div>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Address Line <span class="text-red-500">*</span></label>
                            <textarea x-model="form.address_line" rows="2"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="House, Road, Block…"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Area / Thana</label>
                            <input type="text" x-model="form.area"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Mirpur, Uttara…">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">City</label>
                            <input type="text" x-model="form.city"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Dhaka">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Postal Code</label>
                            <input type="text" x-model="form.postal_code"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="1216">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Shipping Zone <span class="text-red-500">*</span></label>
                            <select x-model="form.zone_id" @change="recalculate()"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">— Select zone —</option>
                                <template x-for="zone in zones" :key="zone.id">
                                    <option :value="zone.id" x-text="zone.name + ' (৳' + zone.base_charge + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Products --}}
                <div class="bg-white border border-gray-200 rounded-xl">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-700"><i class="fas fa-box mr-2 text-gray-400"></i>Order Items</h3>
                        <span class="text-xs text-gray-400" x-text="items.length + ' item(s)'"></span>
                    </div>

                    {{-- Product search --}}
                    <div class="p-5 border-b border-gray-100">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Search &amp; Add Product</label>
                        <div class="relative">
                            <input type="text" x-model="productSearch"
                                   @input.debounce.350ms="searchProducts()"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm pl-9 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Type product name or SKU…">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                        </div>
                        <div x-show="searchResults.length" class="mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto z-30 relative">
                            <template x-for="result in searchResults" :key="(result.variant_id ?? 'c'+result.combo_id)">
                                <div @click="addItem(result)"
                                     class="px-4 py-3 text-sm cursor-pointer hover:bg-blue-50 flex items-center gap-3 border-b border-gray-50 last:border-0">
                                    <img :src="result.thumbnail || '/images/placeholder.png'" class="w-9 h-9 rounded-lg object-cover border border-gray-200">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-800 truncate" x-text="result.product_name"></p>
                                        <p class="text-xs text-gray-500" x-text="result.variant_title + (result.sku ? ' · ' + result.sku : '')"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-800" x-text="'৳' + result.price.toLocaleString()"></p>
                                        <p class="text-xs" :class="result.available_stock > 0 ? 'text-green-600' : 'text-red-500'"
                                           x-text="result.available_stock > 0 ? result.available_stock + ' in stock' : 'Out of stock'"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Item list --}}
                    <div class="divide-y divide-gray-50">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="px-5 py-4 flex items-center gap-4">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate" x-text="item.product_name"></p>
                                    <p class="text-xs text-gray-500" x-text="item.variant_title + (item.sku ? ' · ' + item.sku : '')"></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="changeQty(idx, -1)"
                                            class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 text-sm transition">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    <input type="number" x-model.number="item.quantity"
                                           @input.debounce.400ms="recalculate()"
                                           :min="1" :max="item.available_stock"
                                           class="w-14 text-center border border-gray-300 rounded-lg py-1 text-sm font-semibold">
                                    <button @click="changeQty(idx, 1)"
                                            class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 text-sm transition">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>
                                <div class="w-24 text-right">
                                    <p class="text-sm font-bold text-gray-800" x-text="'৳' + (item.price * item.quantity).toLocaleString()"></p>
                                    <p class="text-xs text-gray-400" x-text="'৳' + item.price.toLocaleString() + ' ea'"></p>
                                </div>
                                <button @click="removeItem(idx)" class="text-gray-300 hover:text-red-500 transition ml-1">
                                    <i class="fas fa-trash-can text-sm"></i>
                                </button>
                            </div>
                        </template>
                        <div x-show="items.length === 0" class="px-5 py-10 text-center text-gray-400">
                            <i class="fas fa-box-open text-3xl mb-2 block"></i>
                            <p class="text-sm">Search and add products above</p>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="bg-white border border-gray-200 rounded-xl">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-bold text-gray-700"><i class="fas fa-note-sticky mr-2 text-gray-400"></i>Order Notes</h3>
                    </div>
                    <div class="p-5">
                        <textarea x-model="form.notes" rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Internal notes (optional)…"></textarea>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════
                 RIGHT: Order Summary & Actions
            ══════════════════════════════ --}}
            <div class="space-y-5">

                {{-- Payment --}}
                <div class="bg-white border border-gray-200 rounded-xl">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-bold text-gray-700"><i class="fas fa-credit-card mr-2 text-gray-400"></i>Payment</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Payment Method <span class="text-red-500">*</span></label>
                            <select x-model="form.payment_method"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="cod">Cash on Delivery</option>
                                <option value="online">Online Payment</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Coupon Code</label>
                            <div class="flex gap-2">
                                <input type="text" x-model="form.coupon_code"
                                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Optional">
                                <button @click="recalculate()"
                                        class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm rounded-lg transition">
                                    Apply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Order Totals --}}
                <div class="bg-white border border-gray-200 rounded-xl">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-bold text-gray-700"><i class="fas fa-calculator mr-2 text-gray-400"></i>Order Summary</h3>
                    </div>
                    <div x-show="calculating" class="px-5 py-6 text-center text-gray-400">
                        <i class="fas fa-spinner fa-spin text-blue-500 mr-2"></i>Calculating…
                    </div>
                    <div x-show="!calculating" class="p-5">
                        <div x-show="!preview && items.length === 0" class="text-center text-gray-400 py-4 text-sm">
                            Add items to see pricing
                        </div>
                        <template x-if="preview || items.length > 0">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal</span>
                                    <span class="font-medium" x-text="'৳' + ((preview?.subtotal ?? itemSubtotal()).toLocaleString())"></span>
                                </div>
                                <template x-if="preview?.tier_discount > 0">
                                    <div class="flex justify-between text-green-600">
                                        <span>Tier Discount</span>
                                        <span class="font-medium" x-text="'−৳' + preview.tier_discount.toLocaleString()"></span>
                                    </div>
                                </template>
                                <template x-if="preview?.coupon_discount > 0">
                                    <div class="flex justify-between text-green-600">
                                        <span>Coupon (<span x-text="preview.coupon_code"></span>)</span>
                                        <span class="font-medium" x-text="'−৳' + preview.coupon_discount.toLocaleString()"></span>
                                    </div>
                                </template>
                                <div class="flex justify-between text-gray-600">
                                    <span>Shipping</span>
                                    <span class="font-medium" x-text="preview ? '৳' + preview.shipping_cost.toLocaleString() : (form.zone_id ? 'Calculating…' : '—')"></span>
                                </div>
                                <div class="border-t border-gray-200 pt-2 flex justify-between font-bold text-gray-800 text-base">
                                    <span>Grand Total</span>
                                    <span x-text="preview ? '৳' + preview.grand_total.toLocaleString() : '৳' + itemSubtotal().toLocaleString()"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Error --}}
                <div x-show="errorMsg" class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <p class="text-sm text-red-700" x-text="errorMsg"></p>
                </div>

                {{-- Submit --}}
                <button @click="submitOrder()"
                        :disabled="saving || items.length === 0 || !form.customer_name || !form.customer_phone || !form.address_line || !form.zone_id"
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fas fa-spinner fa-spin" x-show="saving"></i>
                    <i class="fas fa-check" x-show="!saving"></i>
                    <span x-text="saving ? 'Creating Order…' : 'Create Order'"></span>
                </button>
                <p class="text-xs text-center text-gray-400">Order will be created with status: <strong>Pending</strong></p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function createOrder() {
    return {
        // Form data
        form: {
            customer_name: '',
            customer_phone: '',
            customer_email: '',
            address_line: '',
            area: '',
            city: '',
            postal_code: '',
            zone_id: '',
            payment_method: 'cod',
            coupon_code: '',
            notes: '',
            linked_user_id: null,
        },

        // Item list
        items: [],

        // Zone list
        zones: [],

        // User search
        userSearch: '',
        userResults: [],
        selectedUserName: '',

        // Product search
        productSearch: '',
        searchResults: [],

        // Pricing preview
        preview: null,
        calculating: false,

        // UI state
        saving: false,
        errorMsg: '',
        createdOrder: null,

        async init() {
            await this.loadZones();
        },

        async loadZones() {
            try {
                const r = await fetch('/api/v1/admin/orders/shipping-zones', {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
                });
                const j = await r.json();
                this.zones = j.data ?? [];
            } catch(e) {}
        },

        async searchUsers() {
            if (this.userSearch.length < 2) { this.userResults = []; return; }
            try {
                const r = await fetch(`/api/v1/admin/customers?q=${encodeURIComponent(this.userSearch)}&per_page=5`, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
                });
                const j = await r.json();
                this.userResults = (j.data ?? []).slice(0, 6);
            } catch(e) {}
        },

        selectUser(u) {
            this.form.linked_user_id = u.id;
            this.selectedUserName = u.name + ' (' + u.phone + ')';
            // Auto-fill customer fields if empty
            if (!this.form.customer_name)  this.form.customer_name  = u.name;
            if (!this.form.customer_phone) this.form.customer_phone = u.phone;
            if (!this.form.customer_email && u.email) this.form.customer_email = u.email;
            this.userSearch = '';
            this.userResults = [];
        },

        clearUser() {
            this.form.linked_user_id = null;
            this.selectedUserName = '';
        },

        async searchProducts() {
            if (this.productSearch.length < 2) { this.searchResults = []; return; }
            try {
                const r = await fetch(`/api/v1/admin/orders/search-products?q=${encodeURIComponent(this.productSearch)}`, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
                });
                const j = await r.json();
                this.searchResults = j.data ?? [];
            } catch(e) {}
        },

        addItem(result) {
            const key = result.variant_id ?? ('c' + result.combo_id);
            const existing = this.items.find(i => (i.variant_id ?? ('c' + i.combo_id)) === key);

            if (existing) {
                if (existing.quantity < result.available_stock) existing.quantity++;
            } else {
                this.items.push({
                    variant_id:      result.variant_id,
                    combo_id:        result.combo_id,
                    product_name:    result.product_name,
                    variant_title:   result.variant_title,
                    sku:             result.sku,
                    price:           result.price,
                    available_stock: result.available_stock,
                    quantity:        1,
                });
            }

            this.productSearch = '';
            this.searchResults = [];
            this.recalculate();
        },

        removeItem(idx) {
            this.items.splice(idx, 1);
            this.recalculate();
        },

        changeQty(idx, delta) {
            const item = this.items[idx];
            const newQty = item.quantity + delta;
            if (newQty < 1) { this.removeItem(idx); return; }
            if (newQty > item.available_stock) return;
            item.quantity = newQty;
            this.recalculate();
        },

        itemSubtotal() {
            return this.items.reduce((s, i) => s + (i.price * i.quantity), 0);
        },

        async recalculate() {
            if (this.items.length === 0 || !this.form.zone_id) { this.preview = null; return; }
            this.calculating = true;
            this.errorMsg = '';
            try {
                const payload = {
                    items: this.items.map(i => ({
                        variant_id: i.variant_id ?? undefined,
                        combo_id:   i.combo_id   ?? undefined,
                        quantity:   i.quantity,
                    })),
                    zone_id:     this.form.zone_id,
                };
                // Use preview-edit on a fake order is not possible — build preview from items/zones
                // We send to a generic preview endpoint that matches checkout preview logic
                const r = await fetch('/api/v1/checkout/preview', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                    body: JSON.stringify({
                        ...payload,
                        coupon_code:    this.form.coupon_code || null,
                        payment_method: this.form.payment_method,
                        customer_name:  this.form.customer_name || 'Admin',
                        customer_phone: this.form.customer_phone || '01000000000',
                    }),
                });
                const j = await r.json();
                if (j.success !== false) {
                    this.preview = j.data ?? j;
                } else {
                    this.errorMsg = j.message ?? 'Pricing calculation failed.';
                    this.preview = null;
                }
            } catch(e) {
                this.errorMsg = 'Could not calculate pricing.';
            } finally {
                this.calculating = false;
            }
        },

        async submitOrder() {
            this.errorMsg = '';

            if (!this.form.customer_name)  return (this.errorMsg = 'Customer name is required.');
            if (!this.form.customer_phone) return (this.errorMsg = 'Customer phone is required.');
            if (!this.form.address_line)   return (this.errorMsg = 'Address is required.');
            if (!this.form.zone_id)        return (this.errorMsg = 'Shipping zone is required.');
            if (this.items.length === 0)   return (this.errorMsg = 'Add at least one product.');

            this.saving = true;
            try {
                const r = await fetch('/api/v1/admin/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                    body: JSON.stringify({
                        ...this.form,
                        items: this.items.map(i => ({
                            variant_id: i.variant_id ?? undefined,
                            combo_id:   i.combo_id   ?? undefined,
                            quantity:   i.quantity,
                        })),
                    }),
                });
                const j = await r.json();
                if (r.ok && j.success !== false) {
                    this.createdOrder = j.data;
                } else {
                    this.errorMsg = j.message ?? 'Failed to create order.';
                }
            } catch(e) {
                this.errorMsg = 'Network error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        resetForm() {
            this.createdOrder = null;
            this.items = [];
            this.preview = null;
            this.errorMsg = '';
            this.form = {
                customer_name: '', customer_phone: '', customer_email: '',
                address_line: '', area: '', city: '', postal_code: '',
                zone_id: '', payment_method: 'cod', coupon_code: '', notes: '',
                linked_user_id: null,
            };
        },
    };
}
</script>
@endpush

@endsection
