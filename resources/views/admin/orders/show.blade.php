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
                            :class="order ? statusColor(order.order_status) : ''"
                            x-text="order?.order_status ?? ''">
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
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- ============================
                 LEFT: Order details
            ============================= --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- Items Table --}}
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-bold text-gray-700">Order Items</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Unit Price</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="(item, i) in order?.items ?? []" :key="i">
                                    <tr>
                                        <td class="px-5 py-3">
                                            <p class="font-medium text-gray-800" x-text="item.product_name"></p>
                                            <p class="text-xs text-gray-400" x-text="item.variant_title"></p>
                                        </td>
                                        <td class="px-5 py-3 font-mono text-xs text-gray-500" x-text="item.sku ?? '—'"></td>
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
                                    <td class="px-5 py-2 text-right text-sm text-gray-700">৳<span x-text="Number(order?.subtotal ?? 0).toLocaleString()"></span></td>
                                </tr>
                                <template x-if="order?.discount_total > 0">
                                    <tr>
                                        <td colspan="4" class="px-5 py-1 text-right text-xs text-green-700">
                                            Discount <span x-show="order?.coupon_code" x-text="'(' + order.coupon_code + ')'"></span>
                                        </td>
                                        <td class="px-5 py-1 text-right text-sm text-green-700">−৳<span x-text="Number(order?.discount_total ?? 0).toLocaleString()"></span></td>
                                    </tr>
                                </template>
                                <tr>
                                    <td colspan="4" class="px-5 py-1 text-right text-xs text-gray-500">Shipping</td>
                                    <td class="px-5 py-1 text-right text-sm text-gray-700">৳<span x-text="Number(order?.shipping_cost ?? 0).toLocaleString()"></span></td>
                                </tr>
                                <tr class="border-t border-gray-200">
                                    <td colspan="4" class="px-5 py-2.5 text-right text-sm font-bold text-gray-800">Grand Total</td>
                                    <td class="px-5 py-2.5 text-right text-base font-bold text-gray-900">৳<span x-text="Number(order?.grand_total ?? 0).toLocaleString()"></span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

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
                                        <p class="text-xs text-gray-400" x-text="new Date(event.at).toLocaleString('en-GB', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'})"></p>
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
                                    <span x-text="new Date(note.created_at).toLocaleString('en-GB', {day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'})"></span>
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

                    <div x-show="nextStatuses.length === 0"
                        class="text-sm text-gray-400 italic">
                        No further transitions available.
                    </div>

                    <div x-show="nextStatuses.length > 0" class="space-y-3">
                        <select x-model="newStatus"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
                            <option value="">Select next status…</option>
                            <template x-for="s in nextStatuses" :key="s">
                                <option :value="s" x-text="s.charAt(0).toUpperCase() + s.slice(1)"></option>
                            </template>
                        </select>

                        <div x-show="statusError" class="text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2" x-text="statusError"></div>

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
                            <p><span x-text="order.shipping_address.city"></span><span x-show="order.shipping_address.postal_code"> &ndash; <span x-text="order.shipping_address.postal_code"></span></span></p>
                            <p class="text-xs text-gray-400 mt-1" x-show="order.zone">Zone: <span x-text="order.zone?.name"></span></p>
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
                            <span class="uppercase font-medium text-gray-700" x-text="order?.payment_method ?? '—'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                                :class="paymentStatusColor(order?.payment_status)"
                                x-text="order?.payment_status ?? '—'">
                            </span>
                        </div>
                        <template x-if="order?.coupon_code">
                            <div class="flex justify-between pt-2 border-t border-gray-100">
                                <span class="text-gray-500">Coupon</span>
                                <span class="font-mono text-xs font-medium text-green-700" x-text="order.coupon_code"></span>
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

        allowedTransitions: {
            pending:    ['confirmed', 'cancelled'],
            confirmed:  ['processing', 'cancelled'],
            processing: ['shipped', 'cancelled'],
            shipped:    ['delivered', 'returned'],
            delivered:  [],
            cancelled:  [],
            returned:   [],
        },

        get nextStatuses() {
            return this.allowedTransitions[this.order?.order_status] ?? [];
        },

        async init() {
            await this.loadOrder();
        },

        async loadOrder() {
            this.loading = true;
            try {
                const r = await fetch(`/api/v1/admin/orders/${this.orderId}`, {
                    headers: { 'Accept': 'application/json' }
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: this.newStatus }),
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ body: this.noteBody }),
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
