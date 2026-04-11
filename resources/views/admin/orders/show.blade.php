@extends('layouts.admin')

@section('title', 'Order Detail')

@section('content')

    <div x-data="orderDetail({{ $orderId }})" x-init="init()">

        {{-- Loading --}}
        <div x-show="loading" class="flex items-center justify-center py-20">
            <div class="text-center text-gray-400">
                <i class="fa-solid fa-spinner fa-spin text-3xl mb-3 block text-green-600"></i>
                <p class="text-sm">Loading order…</p>
            </div>
        </div>

        <div x-show="!loading && order">

            {{-- Page Header --}}
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.orders') }}" class="text-gray-400 hover:text-gray-700 transition">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-bold text-gray-800 font-mono"
                                x-text="order ? '#' + order.order_number : ''"></h2>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold capitalize"
                                :class="order ? statusColor(order.order_status) : ''" x-text="order?.order_status ?? ''">
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold capitalize"
                                :class="order ? paymentStatusColor(order.payment_status) : ''"
                                x-text="order?.payment_status ?? ''">
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5"
                            x-text="order?.placed_at ? 'Placed ' + new Date(order.placed_at).toLocaleString('en-GB', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : ''">
                        </p>
                    </div>
                </div>

                {{-- Edit Mode Toggle (only for pending/confirmed) --}}
                @can('order.update')
                    <div class="flex items-center gap-2">
                        <button x-show="order?.is_editable && !editMode" @click="enterEditMode()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition cursor-pointer">
                            <i class="fas fa-pen-to-square"></i> Edit Order
                        </button>
                        <button x-show="editMode" @click="cancelEdit()"
                            class="inline-flex items-center gap-2 px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition cursor-pointer">
                            <i class="fas fa-xmark"></i> Cancel Edit
                        </button>
                    </div>
                @endcan
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                {{-- ============================
                 LEFT: Order details
            ============================= --}}
                <div class="xl:col-span-2 space-y-6">

                    {{-- ═══════════════════════════════
                     NORMAL VIEW: Items Table
                ═══════════════════════════════ --}}
                    <div x-show="!editMode" class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h3 class="text-sm font-bold text-gray-700">Order Items</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                            Product</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU
                                        </th>
                                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Qty
                                        </th>
                                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Unit
                                            Price</th>
                                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <template x-for="(item, i) in order?.items ?? []" :key="i">
                                        <tr>
                                            <td class="px-5 py-3">
                                                <p class="font-medium text-gray-800" x-text="item.product_name"></p>
                                                <p class="text-xs text-gray-400" x-text="item.variant_title"></p>
                                            </td>
                                            <td class="px-5 py-3 font-mono text-xs text-gray-500" x-text="item.sku ?? '—'">
                                            </td>
                                            <td class="px-5 py-3 text-center text-gray-700" x-text="item.qty"></td>
                                            <td class="px-5 py-3 text-right text-gray-700">
                                                ৳<span x-text="Number(item.unit_price).toLocaleString()"></span>
                                            </td>
                                            <td class="px-5 py-3 text-right font-semibold text-gray-900">
                                                ৳<span x-text="Number(item.total).toLocaleString()"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="bg-gray-50 border-t border-gray-100">
                                    <tr>
                                        <td colspan="4" class="px-5 py-2 text-right text-xs text-gray-500">Subtotal</td>
                                        <td class="px-5 py-2 text-right text-sm text-gray-700">৳<span
                                                x-text="Number(order?.subtotal ?? 0).toLocaleString()"></span></td>
                                    </tr>
                                    <template x-if="order?.discount_total > 0">
                                        <tr>
                                            <td colspan="4" class="px-5 py-1 text-right text-xs text-green-700">
                                                Discount <span x-show="order?.coupon_code"
                                                    x-text="'(' + order.coupon_code + ')'"></span>
                                            </td>
                                            <td class="px-5 py-1 text-right text-sm text-green-700">−৳<span
                                                    x-text="Number(order?.discount_total ?? 0).toLocaleString()"></span>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr>
                                        <td colspan="4" class="px-5 py-1 text-right text-xs text-gray-500">Shipping</td>
                                        <td class="px-5 py-1 text-right text-sm text-gray-700">৳<span
                                                x-text="Number(order?.shipping_cost ?? 0).toLocaleString()"></span></td>
                                    </tr>
                                    <tr class="border-t border-gray-200">
                                        <td colspan="4" class="px-5 py-2.5 text-right text-sm font-bold text-gray-800">
                                            Grand Total</td>
                                        <td class="px-5 py-2.5 text-right text-base font-bold text-gray-900">৳<span
                                                x-text="Number(order?.grand_total ?? 0).toLocaleString()"></span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════
                     EDIT MODE: Editable Items
                ═══════════════════════════════ --}}
                    <div x-show="editMode" x-cloak class="space-y-4">

                        {{-- Edit Header --}}
                        <div
                            class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-blue-800">
                                <i class="fas fa-pen-to-square"></i>
                                <span class="text-sm font-semibold">Edit Mode — Full order modification before courier
                                    pickup</span>
                            </div>
                        </div>

                        {{-- Customer Info Edit --}}
                        <div class="bg-white border border-gray-200 rounded-xl">
                            <div class="px-5 py-3 border-b border-gray-100">
                                <h4 class="text-sm font-bold text-gray-700"><i
                                        class="fas fa-user mr-2 text-gray-400"></i>Customer Information</h4>
                            </div>
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Full Name</label>
                                    <input type="text" x-model="editCustomer.customer_name"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Phone</label>
                                    <input type="text" x-model="editCustomer.customer_phone"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email</label>
                                    <input type="email" x-model="editCustomer.customer_email"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Optional">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Address Line</label>
                                    <textarea x-model="editCustomer.address_line" rows="2"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Area / Thana</label>
                                    <input type="text" x-model="editCustomer.area"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">City</label>
                                    <input type="text" x-model="editCustomer.city"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Postal Code</label>
                                    <input type="text" x-model="editCustomer.postal_code"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Shipping Zone</label>
                                    <select x-model="editCustomer.zone_id" @change="previewData = null"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">— No change —</option>
                                        <template x-for="zone in zones" :key="zone.id">
                                            <option :value="zone.id"
                                                x-text="zone.name + ' (৳' + zone.base_charge + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Order Notes</label>
                                    <textarea x-model="editCustomer.notes" rows="2"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Internal notes…"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Add Product Search --}}
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <label class="block text-xs font-medium text-gray-600 mb-2">Add Product</label>
                            <div class="relative">
                                <input type="text" x-model="productSearch" @input.debounce.400ms="searchProducts()"
                                    placeholder="Search by product name, SKU…"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <i class="fas fa-search absolute right-3 top-3 text-gray-400 text-sm"></i>
                            </div>

                            {{-- Search Results Dropdown --}}
                            <div x-show="searchResults.length > 0" x-cloak
                                class="mt-2 border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-60 overflow-y-auto bg-white shadow-lg">
                                <template x-for="result in searchResults"
                                    :key="(result.variant_id || '') + '-' + (result.combo_id || '')">
                                    <button @click="addItemFromSearch(result)"
                                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-blue-50 text-left transition">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-800 truncate"
                                                x-text="result.product_name"></p>
                                            <p class="text-xs text-gray-500"
                                                x-text="result.variant_title + (result.sku ? ' — ' + result.sku : '')"></p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-sm font-semibold text-gray-800">৳<span
                                                    x-text="Number(result.price).toLocaleString()"></span></p>
                                            <p class="text-xs"
                                                :class="result.available_stock > 0 ? 'text-green-600' : 'text-red-500'"
                                                x-text="result.available_stock + ' in stock'"></p>
                                        </div>
                                        <i class="fas fa-plus text-blue-500"></i>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Editable Items Table --}}
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-700">Order Items (Editing)</h3>
                                <span class="text-xs text-gray-400" x-text="editItems.length + ' item(s)'"></span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                Product</th>
                                            <th
                                                class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-32">
                                                Qty</th>
                                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">
                                                Unit Price</th>
                                            <th
                                                class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-16">
                                                Remove</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <template x-for="(item, idx) in editItems" :key="idx">
                                            <tr :class="item._removed ? 'bg-red-50 opacity-50' : ''">
                                                <td class="px-5 py-3">
                                                    <p class="font-medium text-gray-800"
                                                        x-text="item.product_name_snapshot || item.product_name"></p>
                                                    <p class="text-xs text-gray-400"
                                                        x-text="item.variant_title_snapshot || item.variant_title"></p>
                                                    <p x-show="item.sku_snapshot || item.sku"
                                                        class="text-xs text-gray-400 font-mono"
                                                        x-text="item.sku_snapshot || item.sku"></p>
                                                </td>
                                                <td class="px-5 py-3 text-center">
                                                    <div class="flex items-center justify-center gap-1">
                                                        <button @click="changeQty(idx, -1)" :disabled="item.quantity <= 1"
                                                            class="w-7 h-7 flex items-center justify-center rounded bg-gray-100 hover:bg-gray-200 disabled:opacity-30 text-xs transition">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <input type="number" x-model.number="item.quantity"
                                                            min="1" :max="item.max_quantity || 999"
                                                            class="w-14 text-center border border-gray-200 rounded py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                                        <button @click="changeQty(idx, 1)"
                                                            :disabled="item.quantity >= (item.max_quantity || 999)"
                                                            class="w-7 h-7 flex items-center justify-center rounded bg-gray-100 hover:bg-gray-200 disabled:opacity-30 text-xs transition">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    <p class="text-xs mt-1"
                                                        :class="item.quantity > (item.max_quantity || 999) ? 'text-red-500' :
                                                            'text-gray-400'"
                                                        x-text="'Max: ' + (item.max_quantity || '—')"></p>
                                                </td>
                                                <td class="px-5 py-3 text-right text-gray-700">
                                                    ৳<span
                                                        x-text="Number(item.unit_price || item.price || 0).toLocaleString()"></span>
                                                </td>
                                                <td class="px-5 py-3 text-center">
                                                    <button @click="removeEditItem(idx)"
                                                        class="w-7 h-7 flex items-center justify-center rounded bg-red-50 text-red-500 hover:bg-red-100 transition">
                                                        <i class="fas fa-trash-can text-xs"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="editItems.length === 0">
                                            <tr>
                                                <td colspan="4" class="px-5 py-8 text-center text-gray-400 text-sm">
                                                    No items. Add products using the search above.
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Preview & Apply --}}
                        <div class="flex items-center gap-3">
                            <button @click="previewEdit()" :disabled="editItems.length === 0 || previewing"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600 disabled:opacity-50 transition">
                                <i class="fas" :class="previewing ? 'fa-spinner fa-spin' : 'fa-eye'"></i>
                                <span x-text="previewing ? 'Calculating…' : 'Preview Changes'"></span>
                            </button>

                            <button x-show="previewData" @click="applyEdit()" :disabled="applying"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 transition">
                                <i class="fas" :class="applying ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                                <span x-text="applying ? 'Saving…' : 'Apply Changes'"></span>
                            </button>
                        </div>

                        {{-- Preview Result --}}
                        <template x-if="previewData">
                            <div class="bg-white border border-green-200 rounded-xl overflow-hidden">
                                <div class="px-5 py-3 bg-green-50 border-b border-green-100">
                                    <h4 class="text-sm font-bold text-green-800"><i class="fas fa-calculator mr-1"></i>
                                        Recalculated Totals</h4>
                                </div>
                                <div class="p-5 space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Subtotal</span>
                                        <span class="font-medium">৳<span
                                                x-text="Number(previewData.subtotal).toLocaleString()"></span></span>
                                    </div>
                                    <div x-show="previewData.tier_discount > 0"
                                        class="flex justify-between text-green-700">
                                        <span>Tier Discount</span>
                                        <span>−৳<span
                                                x-text="Number(previewData.tier_discount).toLocaleString()"></span></span>
                                    </div>
                                    <div x-show="previewData.coupon_discount > 0"
                                        class="flex justify-between text-green-700">
                                        <span>Coupon (<span x-text="previewData.coupon_code"></span>)</span>
                                        <span>−৳<span
                                                x-text="Number(previewData.coupon_discount).toLocaleString()"></span></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Shipping</span>
                                        <span class="font-medium">৳<span
                                                x-text="Number(previewData.shipping_cost).toLocaleString()"></span></span>
                                    </div>
                                    <div class="flex justify-between border-t border-gray-200 pt-2">
                                        <span class="font-bold text-gray-800">Grand Total</span>
                                        <span class="font-bold text-lg text-gray-900">৳<span
                                                x-text="Number(previewData.grand_total).toLocaleString()"></span></span>
                                    </div>
                                </div>

                                {{-- Changes Summary --}}
                                <template x-if="previewData.changes && previewData.changes.length > 0">
                                    <div class="px-5 pb-4">
                                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Changes</p>
                                        <div class="space-y-1">
                                            <template x-for="change in previewData.changes"
                                                :key="change.name + change.type">
                                                <div class="flex items-center gap-2 text-xs px-2 py-1 rounded"
                                                    :class="{
                                                        'bg-red-50 text-red-700': change.type === 'removed',
                                                        'bg-green-50 text-green-700': change.type === 'added',
                                                        'bg-blue-50 text-blue-700': change.type === 'quantity_changed'
                                                    }">
                                                    <i class="fas"
                                                        :class="{
                                                            'fa-minus-circle': change.type === 'removed',
                                                            'fa-plus-circle': change.type === 'added',
                                                            'fa-arrows-rotate': change.type === 'quantity_changed'
                                                        }"></i>
                                                    <span x-text="change.name + ' (' + change.variant + ')'"></span>
                                                    <span class="ml-auto font-mono"
                                                        x-text="change.type === 'removed' ? ('−' + change.old_qty) : (change.type === 'added' ? ('+' + change.new_qty) : (change.old_qty + ' → ' + change.new_qty))">
                                                    </span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Edit Error --}}
                        <div x-show="editError" x-cloak
                            class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                            <i class="fas fa-circle-xmark mr-1"></i> <span x-text="editError"></span>
                        </div>

                    </div><!-- /editMode -->

                    {{-- ═══════════════════════════════
                     COURIER / SHIPMENTS PANEL
                ═══════════════════════════════ --}}
                    @can('order.update')
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-700">
                                    <i class="fas fa-truck-fast text-green-600 mr-1"></i> Courier & Shipments
                                </h3>
                                <span class="text-xs px-2 py-0.5 rounded-full"
                                    :class="(order?.shipments?.length ?? 0) > 0 ? 'bg-green-100 text-green-700' :
                                        'bg-gray-100 text-gray-500'"
                                    x-text="(order?.shipments?.length ?? 0) + ' shipment(s)'"></span>
                            </div>

                            {{-- Assign Courier Form --}}
                            <div class="p-5 border-b border-gray-100">
                                <div class="flex items-end gap-3">
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Courier Service</label>
                                        <select x-model="selectedCourier"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                                            <option value="">Select courier…</option>
                                            <option value="pathao">Pathao Courier</option>
                                            <option value="steadfast">Steadfast Delivery</option>
                                            <option value="carrybee">CarryBee Logistics</option>
                                        </select>
                                    </div>
                                    <button @click="assignCourier()" :disabled="!selectedCourier || assigningCourier"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 transition whitespace-nowrap">
                                        <i class="fas"
                                            :class="assigningCourier ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                                        <span x-text="assigningCourier ? 'Creating…' : 'Create Shipment'"></span>
                                    </button>
                                </div>
                                <div x-show="courierError" x-cloak
                                    class="mt-2 text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"
                                    x-text="courierError"></div>
                            </div>

                            {{-- Shipment Cards --}}
                            <div class="divide-y divide-gray-100">
                                <template x-for="shipment in order?.shipments ?? []" :key="shipment.id">
                                    <div class="p-5">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-semibold text-gray-800"
                                                    x-text="shipment.courier_label"></span>
                                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                                    :class="shipmentStatusColor(shipment.status)"
                                                    x-text="shipment.status_label"></span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button @click="syncShipmentStatus(shipment.id)"
                                                    :disabled="shipment.is_terminal"
                                                    class="text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-600 disabled:opacity-30 transition"
                                                    title="Refresh status from courier">
                                                    <i class="fas fa-arrows-rotate"></i>
                                                </button>
                                                <button x-show="shipment.is_cancellable" @click="cancelShipment(shipment.id)"
                                                    class="text-xs px-2 py-1 rounded bg-red-50 hover:bg-red-100 text-red-600 transition"
                                                    title="Cancel shipment">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3 text-xs">
                                            <div>
                                                <span class="text-gray-500">Tracking</span>
                                                <p class="font-mono font-medium text-gray-800"
                                                    x-text="shipment.tracking_code || '—'"></p>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Consignment ID</span>
                                                <p class="font-mono font-medium text-gray-800"
                                                    x-text="shipment.consignment_id || '—'"></p>
                                            </div>
                                            <div x-show="shipment.delivery_fee !== null">
                                                <span class="text-gray-500">Delivery Fee</span>
                                                <p class="font-medium text-gray-800">৳<span
                                                        x-text="Number(shipment.delivery_fee || 0).toLocaleString()"></span>
                                                </p>
                                            </div>
                                            <div x-show="shipment.cod_amount > 0">
                                                <span class="text-gray-500">COD Amount</span>
                                                <p class="font-medium text-gray-800">৳<span
                                                        x-text="Number(shipment.cod_amount || 0).toLocaleString()"></span></p>
                                            </div>
                                        </div>

                                        <div class="mt-3 flex items-center justify-between text-xs text-gray-400">
                                            <span x-text="shipment.courier_status_message || ''"></span>
                                            <span x-show="shipment.status_synced_at"
                                                x-text="'Synced: ' + (shipment.status_synced_at ? new Date(shipment.status_synced_at).toLocaleString('en-GB', {day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'}) : '')"></span>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-400" x-show="shipment.created_by_name">
                                            Created by <span class="font-medium text-gray-600"
                                                x-text="shipment.created_by_name"></span>
                                            on <span
                                                x-text="new Date(shipment.created_at).toLocaleString('en-GB', {day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'})"></span>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="(order?.shipments ?? []).length === 0">
                                    <div class="p-5 text-center text-sm text-gray-400">
                                        <i class="fas fa-box-open text-2xl mb-2 block"></i>
                                        No shipments yet. Select a courier above to create one.
                                    </div>
                                </template>
                            </div>
                        </div>
                    @endcan

                    {{-- Timeline --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-5">
                        <h3 class="text-sm font-bold text-gray-700 mb-4">Order Timeline</h3>
                        <div class="relative">
                            <div class="absolute left-4 top-0 bottom-0 w-px bg-gray-200"></div>
                            <div class="space-y-4">
                                <template x-for="(event, i) in order?.timeline ?? []" :key="i">
                                    <div class="flex gap-4 items-start">
                                        <div class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                            :class="timelineColor(event.status)">
                                            <i class="fa-solid text-xs" :class="timelineIcon(event.status)"></i>
                                        </div>
                                        <div class="pt-1">
                                            <p class="text-sm font-semibold text-gray-800" x-text="event.label"></p>
                                            <p class="text-xs text-gray-400"
                                                x-text="new Date(event.at).toLocaleString('en-GB', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'})">
                                            </p>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!order?.timeline?.length">
                                    <p class="text-sm text-gray-400 pl-12">No timeline events yet</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Admin Notes --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-5">
                        <h3 class="text-sm font-bold text-gray-700 mb-4">Internal Notes</h3>

                        <div class="space-y-3 mb-4">
                            <template x-for="note in order?.admin_notes ?? []" :key="note.id">
                                <div class="bg-yellow-50 border border-yellow-100 rounded-lg px-4 py-3">
                                    <p class="text-sm text-gray-800" x-text="note.body"></p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <span x-text="note.admin_name"></span> &bull;
                                        <span
                                            x-text="new Date(note.created_at).toLocaleString('en-GB', {day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'})"></span>
                                    </p>
                                </div>
                            </template>
                            <template x-if="!order?.admin_notes?.length">
                                <p class="text-sm text-gray-400">No internal notes yet.</p>
                            </template>
                        </div>

                        @can('order.update')
                            <div class="flex gap-3 mt-4 border-t border-gray-100 pt-4">
                                <textarea x-model="noteBody" rows="2" placeholder="Add a note visible only to admins…"
                                    class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 resize-none"
                                    @keydown.meta.enter="addNote()"></textarea>
                                <button @click="addNote()" :disabled="addingNote || !noteBody.trim()"
                                    class="self-end px-4 py-2 bg-green-700 hover:bg-green-800 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition cursor-pointer">
                                    <i x-show="addingNote" class="fa-solid fa-spinner fa-spin mr-1"></i>
                                    Add Note
                                </button>
                            </div>
                        @endcan
                    </div>

                </div>

                {{-- ============================
                 RIGHT: Sidebar
            ============================= --}}
                <div class="space-y-5">

                    {{-- Status Change --}}
                    @can('order.update')
                        <div class="bg-white border border-gray-200 rounded-xl p-5">
                            <h3 class="text-sm font-bold text-gray-700 mb-3">Update Status</h3>

                            <div x-show="nextStatuses.length === 0" class="text-sm text-gray-400 italic">
                                No further transitions available.
                            </div>

                            <div x-show="nextStatuses.length > 0" class="space-y-3">
                                <select x-model="newStatus"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
                                    <option value="">Select next status…</option>
                                    <template x-for="s in nextStatuses" :key="s">
                                        <option :value="s" x-text="s.charAt(0).toUpperCase() + s.slice(1)">
                                        </option>
                                    </template>
                                </select>

                                <div x-show="statusError"
                                    class="text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"
                                    x-text="statusError"></div>

                                <button @click="updateStatus()" :disabled="!newStatus || changingStatus"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-green-700 hover:bg-green-800 disabled:opacity-50 text-white text-sm font-medium py-2 rounded-lg transition cursor-pointer">
                                    <i x-show="changingStatus" class="fa-solid fa-spinner fa-spin text-xs"></i>
                                    <span x-text="changingStatus ? 'Updating…' : 'Update Status'"></span>
                                </button>
                            </div>
                        </div>
                    @endcan

                    {{-- Customer Info --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-5">
                        <h3 class="text-sm font-bold text-gray-700 mb-3">Customer</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-start gap-2">
                                <i class="fa-solid fa-user text-gray-400 text-xs mt-1 w-4 text-center"></i>
                                <span class="text-gray-700" x-text="order?.customer_name ?? '—'"></span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="fa-solid fa-phone text-gray-400 text-xs mt-1 w-4 text-center"></i>
                                <span class="text-gray-700" x-text="order?.customer_phone ?? '—'"></span>
                            </div>
                            <template x-if="order?.customer_email">
                                <div class="flex items-start gap-2">
                                    <i class="fa-solid fa-envelope text-gray-400 text-xs mt-1 w-4 text-center"></i>
                                    <span class="text-gray-700 break-all" x-text="order.customer_email"></span>
                                </div>
                            </template>
                            <template x-if="order?.user?.id">
                                <div class="pt-2 border-t border-gray-100">
                                    <a :href="`/admin/customers/${order.user.id}`"
                                        class="text-xs text-blue-600 hover:underline">
                                        View customer profile &rarr;
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Shipping Address --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-5">
                        <h3 class="text-sm font-bold text-gray-700 mb-3">Delivery Address</h3>
                        <template x-if="order?.shipping_address">
                            <div class="text-sm text-gray-600 space-y-1">
                                <p x-text="order.shipping_address.address_line"></p>
                                <p x-show="order.shipping_address.area" x-text="order.shipping_address.area"></p>
                                <p><span x-text="order.shipping_address.city"></span><span
                                        x-show="order.shipping_address.postal_code"> &ndash; <span
                                            x-text="order.shipping_address.postal_code"></span></span></p>
                                <p class="text-xs text-gray-400 mt-1" x-show="order.zone">Zone: <span
                                        x-text="order.zone?.name"></span></p>
                            </div>
                        </template>
                        <template x-if="!order?.shipping_address">
                            <p class="text-sm text-gray-400">No address recorded.</p>
                        </template>
                    </div>

                    {{-- Payment --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-5">
                        <h3 class="text-sm font-bold text-gray-700 mb-3">Payment</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Method</span>
                                <span class="uppercase font-medium text-gray-700"
                                    x-text="order?.payment_method ?? '—'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Status</span>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                                    :class="paymentStatusColor(order?.payment_status)"
                                    x-text="order?.payment_status ?? '—'">
                                </span>
                            </div>
                            <template x-if="order?.coupon_code">
                                <div class="flex justify-between pt-2 border-t border-gray-100">
                                    <span class="text-gray-500">Coupon</span>
                                    <span class="font-mono text-xs font-medium text-green-700"
                                        x-text="order.coupon_code"></span>
                                </div>
                            </template>
                            <template x-if="order?.notes">
                                <div class="pt-2 border-t border-gray-100">
                                    <p class="text-xs text-gray-500 mb-1">Customer note:</p>
                                    <p class="text-xs text-gray-700 italic" x-text="order.notes"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Active Shipment Quick View --}}
                    <template x-if="(order?.shipments ?? []).length > 0">
                        <div class="bg-white border border-gray-200 rounded-xl p-5">
                            <h3 class="text-sm font-bold text-gray-700 mb-3">Latest Shipment</h3>
                            <div class="space-y-2 text-sm" x-data="{ s: order.shipments[0] }">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Courier</span>
                                    <span class="font-medium text-gray-700" x-text="s.courier_label"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Status</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="shipmentStatusColor(s.status)" x-text="s.status_label"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Tracking</span>
                                    <span class="font-mono text-xs font-medium text-gray-700"
                                        x-text="s.tracking_code || '—'"></span>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        function orderDetail(orderId) {
            return {
                orderId,
                order: null,
                loading: true,

                newStatus: '',
                changingStatus: false,
                statusError: null,

                noteBody: '',
                addingNote: false,

                // Edit mode state
                editMode: false,
                editItems: [],
                editCustomer: {
                    customer_name: '',
                    customer_phone: '',
                    customer_email: '',
                    address_line: '',
                    area: '',
                    city: '',
                    postal_code: '',
                    zone_id: '',
                    notes: '',
                },
                productSearch: '',
                searchResults: [],
                previewData: null,
                previewing: false,
                applying: false,
                editError: null,

                // Zones list (for edit + create forms)
                zones: [],

                // Courier state
                selectedCourier: '',
                assigningCourier: false,
                courierError: null,

                allowedTransitions: {
                    pending: ['confirmed', 'cancelled'],
                    confirmed: ['processing', 'cancelled'],
                    processing: ['shipped', 'cancelled'],
                    shipped: ['delivered', 'returned'],
                    delivered: [],
                    cancelled: [],
                    returned: [],
                },

                get nextStatuses() {
                    return this.allowedTransitions[this.order?.order_status] ?? [];
                },

                csrf() {
                    return document.querySelector('meta[name="csrf-token"]')?.content;
                },

                async init() {
                    await Promise.all([this.loadOrder(), this.loadZones()]);
                },

                async loadZones() {
                    try {
                        const r = await fetch('/api/v1/admin/orders/shipping-zones', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const j = await r.json();
                        this.zones = j.data ?? [];
                    } catch (e) {}
                },

                async loadOrder() {
                    this.loading = true;
                    try {
                        const r = await fetch(`/api/v1/admin/orders/${this.orderId}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await r.json();
                        if (r.ok) {
                            this.order = data.data;
                            this.newStatus = '';
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.loading = false;
                    }
                },

                async updateStatus() {
                    if (!this.newStatus) return;
                    this.changingStatus = true;
                    this.statusError = null;
                    try {
                        const r = await fetch(`/api/v1/admin/orders/${this.orderId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf(),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                status: this.newStatus
                            }),
                        });
                        const data = await r.json();
                        if (r.ok) {
                            await this.loadOrder();
                        } else {
                            this.statusError = data.message ?? 'Status update failed.';
                        }
                    } catch (e) {
                        this.statusError = 'Network error.';
                    } finally {
                        this.changingStatus = false;
                    }
                },

                async addNote() {
                    if (!this.noteBody.trim()) return;
                    this.addingNote = true;
                    try {
                        const r = await fetch(`/api/v1/admin/orders/${this.orderId}/notes`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf(),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                body: this.noteBody
                            }),
                        });
                        if (r.ok) {
                            this.noteBody = '';
                            await this.loadOrder();
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.addingNote = false;
                    }
                },

                // ── Order Edit Methods ────────────────────

                async enterEditMode() {
                    this.editError = null;
                    this.previewData = null;
                    try {
                        const r = await fetch(`/api/v1/admin/orders/${this.orderId}/edit-data`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf()
                            }
                        });
                        const data = await r.json();
                        if (r.ok) {
                            const d = data.data;
                            this.editItems = d.items.map(i => ({
                                ...i
                            }));
                            // Populate customer / address fields from current order data
                            this.editCustomer = {
                                customer_name: d.customer_name ?? '',
                                customer_phone: d.customer_phone ?? '',
                                customer_email: d.customer_email ?? '',
                                address_line: d.address?.address_line ?? '',
                                area: d.address?.area ?? '',
                                city: d.address?.city ?? '',
                                postal_code: d.address?.postal_code ?? '',
                                zone_id: d.zone_id ?? '',
                                notes: d.notes ?? '',
                            };
                            this.editMode = true;
                        } else {
                            this.editError = data.message || 'Cannot enter edit mode.';
                        }
                    } catch (e) {
                        this.editError = 'Network error.';
                    }
                },

                cancelEdit() {
                    this.editMode = false;
                    this.editItems = [];
                    this.previewData = null;
                    this.editError = null;
                    this.productSearch = '';
                    this.searchResults = [];
                    this.editCustomer = {
                        customer_name: '',
                        customer_phone: '',
                        customer_email: '',
                        address_line: '',
                        area: '',
                        city: '',
                        postal_code: '',
                        zone_id: '',
                        notes: '',
                    };
                },

                async searchProducts() {
                    if (this.productSearch.length < 2) {
                        this.searchResults = [];
                        return;
                    }
                    try {
                        const r = await fetch(
                            `/api/v1/admin/orders/search-products?q=${encodeURIComponent(this.productSearch)}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf()
                                }
                            });
                        const data = await r.json();
                        if (r.ok) this.searchResults = data.data ?? [];
                    } catch (e) {
                        console.error(e);
                    }
                },

                addItemFromSearch(result) {
                    // Check if already in edit list
                    const existing = this.editItems.find(i =>
                        (result.variant_id && i.variant_id === result.variant_id) ||
                        (result.combo_id && i.combo_id === result.combo_id)
                    );
                    if (existing) {
                        existing.quantity++;
                    } else {
                        this.editItems.push({
                            variant_id: result.variant_id,
                            combo_id: result.combo_id,
                            product_name_snapshot: result.product_name,
                            variant_title_snapshot: result.variant_title,
                            sku_snapshot: result.sku,
                            quantity: 1,
                            unit_price: result.price,
                            max_quantity: result.available_stock || 999,
                        });
                    }
                    this.productSearch = '';
                    this.searchResults = [];
                    this.previewData = null;
                },

                changeQty(idx, delta) {
                    const item = this.editItems[idx];
                    const newQty = item.quantity + delta;
                    if (newQty >= 1 && newQty <= (item.max_quantity || 999)) {
                        item.quantity = newQty;
                        this.previewData = null;
                    }
                },

                removeEditItem(idx) {
                    this.editItems.splice(idx, 1);
                    this.previewData = null;
                },

                buildEditPayload() {
                    return this.editItems.map(i => ({
                        variant_id: i.variant_id || null,
                        combo_id: i.combo_id || null,
                        quantity: i.quantity,
                    }));
                },

                async previewEdit() {
                    this.previewing = true;
                    this.editError = null;
                    this.previewData = null;
                    try {
                        const r = await fetch(`/api/v1/admin/orders/${this.orderId}/preview-edit`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf(),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                items: this.buildEditPayload(),
                                zone_id: this.editCustomer.zone_id || null,
                            }),
                        });
                        const data = await r.json();
                        if (r.ok) {
                            this.previewData = data.data;
                        } else {
                            this.editError = data.message || 'Preview failed.';
                        }
                    } catch (e) {
                        this.editError = 'Network error.';
                    } finally {
                        this.previewing = false;
                    }
                },

                async applyEdit() {
                    this.applying = true;
                    this.editError = null;
                    try {
                        const r = await fetch(`/api/v1/admin/orders/${this.orderId}`, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf(),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                items: this.buildEditPayload(),
                                zone_id: this.editCustomer.zone_id || null,
                                customer_name: this.editCustomer.customer_name || null,
                                customer_phone: this.editCustomer.customer_phone || null,
                                customer_email: this.editCustomer.customer_email || null,
                                address_line: this.editCustomer.address_line || null,
                                area: this.editCustomer.area || null,
                                city: this.editCustomer.city || null,
                                postal_code: this.editCustomer.postal_code || null,
                                notes: this.editCustomer.notes !== undefined ? this.editCustomer.notes :
                                    null,
                            }),
                        });
                        const data = await r.json();
                        if (r.ok) {
                            this.cancelEdit();
                            this.order = data.data;
                        } else {
                            this.editError = data.message || 'Apply failed.';
                        }
                    } catch (e) {
                        this.editError = 'Network error.';
                    } finally {
                        this.applying = false;
                    }
                },

                // ── Courier Methods ────────────────────

                async assignCourier() {
                    if (!this.selectedCourier) return;
                    this.assigningCourier = true;
                    this.courierError = null;
                    try {
                        const r = await fetch('/api/v1/admin/courier/assign', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf(),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                order_id: this.orderId,
                                courier: this.selectedCourier,
                            }),
                        });
                        const data = await r.json();
                        if (r.ok) {
                            this.selectedCourier = '';
                            await this.loadOrder();
                        } else {
                            this.courierError = data.message || 'Failed to assign courier.';
                        }
                    } catch (e) {
                        this.courierError = 'Network error.';
                    } finally {
                        this.assigningCourier = false;
                    }
                },

                async syncShipmentStatus(shipmentId) {
                    try {
                        const r = await fetch(`/api/v1/admin/courier/shipments/${shipmentId}/sync`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf(),
                                'Accept': 'application/json',
                            },
                        });
                        if (r.ok) await this.loadOrder();
                    } catch (e) {
                        console.error(e);
                    }
                },

                async cancelShipment(shipmentId) {
                    if (!confirm('Cancel this shipment?')) return;
                    try {
                        const r = await fetch(`/api/v1/admin/courier/shipments/${shipmentId}/cancel`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf(),
                                'Accept': 'application/json',
                            },
                        });
                        if (r.ok) await this.loadOrder();
                    } catch (e) {
                        console.error(e);
                    }
                },

                // ── UI Helpers ────────────────────

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
                    return s === 'paid' ? 'bg-green-100 text-green-800' :
                        s === 'failed' ? 'bg-red-100 text-red-800' :
                        'bg-yellow-100 text-yellow-800';
                },

                shipmentStatusColor(s) {
                    const c = {
                        pending: 'bg-yellow-100 text-yellow-700',
                        picked_up: 'bg-blue-100 text-blue-700',
                        in_transit: 'bg-indigo-100 text-indigo-700',
                        out_for_delivery: 'bg-cyan-100 text-cyan-700',
                        delivered: 'bg-green-100 text-green-700',
                        partial_delivery: 'bg-amber-100 text-amber-700',
                        cancelled: 'bg-red-100 text-red-700',
                        returned: 'bg-gray-100 text-gray-600',
                        on_hold: 'bg-orange-100 text-orange-700',
                        failed: 'bg-red-100 text-red-700',
                    };
                    return c[s] ?? 'bg-gray-100 text-gray-600';
                },

                timelineColor(s) {
                    const c = {
                        pending: 'bg-yellow-100 text-yellow-700',
                        confirmed: 'bg-blue-100 text-blue-700',
                        processing: 'bg-indigo-100 text-indigo-700',
                        shipped: 'bg-cyan-100 text-cyan-700',
                        delivered: 'bg-green-100 text-green-700',
                        cancelled: 'bg-red-100 text-red-700',
                        returned: 'bg-gray-100 text-gray-600',
                    };
                    return c[s] ?? 'bg-gray-100 text-gray-600';
                },

                timelineIcon(s) {
                    const icons = {
                        pending: 'fa-clock',
                        confirmed: 'fa-check',
                        processing: 'fa-gear',
                        shipped: 'fa-truck',
                        delivered: 'fa-circle-check',
                        cancelled: 'fa-ban',
                        returned: 'fa-rotate-left',
                    };
                    return icons[s] ?? 'fa-circle';
                },
            };
        }
    </script>
@endpush
