@extends('layouts.admin')

@section('title', 'Customer Profile')

@section('content')

<div x-data="customerDetail({{ $customerId }})" x-init="init()">

    {{-- Back link --}}
    <div class="mb-5">
        <a href="/admin/customers" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-700 transition">
            <i class="fa-solid fa-arrow-left text-xs"></i> Back to Customers
        </a>
    </div>

    {{-- Loading --}}
    <template x-if="loading">
        <div class="space-y-4">
            <div class="h-24 bg-white border border-gray-200 rounded-xl animate-pulse"></div>
            <div class="h-64 bg-white border border-gray-200 rounded-xl animate-pulse"></div>
        </div>
    </template>

    <template x-if="!loading && customer">
        <div class="space-y-5">

            {{-- Profile header --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center text-green-700 text-xl font-bold uppercase flex-shrink-0"
                    x-text="customer.name.charAt(0)">
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-900" x-text="customer.name"></h2>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
                        <span class="text-sm text-gray-500" x-text="customer.email ?? '—'"></span>
                        <span class="text-sm text-gray-500" x-text="customer.phone"></span>
                        <span x-show="customer.referral_code" class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">
                            REF: <span x-text="customer.referral_code"></span>
                        </span>
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <template x-if="customer.is_guest">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Guest</span>
                        </template>
                        <template x-if="!customer.is_guest">
                            <button @click="toggleActive()"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition cursor-pointer"
                                :class="customer.is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200'"
                                x-text="customer.is_active ? 'Active' : 'Inactive'">
                            </button>
                        </template>
                        <span class="text-xs text-gray-400">
                            Joined <span x-text="customer.created_at ? new Date(customer.created_at).toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'}) : '—'"></span>
                        </span>
                        <span class="text-xs text-gray-400" x-show="customer.last_login_at">
                            &bull; Last login <span x-text="customer.last_login_at ? new Date(customer.last_login_at).toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'2-digit'}) : ''"></span>
                        </span>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="flex sm:flex-col gap-4 sm:gap-2 sm:items-end">
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900" x-text="customer.orders_count ?? 0"></p>
                        <p class="text-xs text-gray-500">Total Orders</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-green-700">
                            ৳<span x-text="Number(customer.orders_sum_grand_total ?? 0).toLocaleString()"></span>
                        </p>
                        <p class="text-xs text-gray-500">Total Spent</p>
                    </div>
                </div>
            </div>

            {{-- Recent Orders --}}
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 text-sm">Recent Orders</h3>
                    <a :href="`/admin/orders?customer_id=${customer.id}`"
                        class="text-xs text-blue-600 hover:text-blue-800 transition">
                        View all &rarr;
                    </a>
                </div>

                <template x-if="!customer.recent_orders || customer.recent_orders.length === 0">
                    <div class="px-5 py-10 text-center text-gray-400 text-sm">
                        <i class="fa-solid fa-box text-2xl mb-2 block"></i>
                        No orders yet
                    </div>
                </template>

                <template x-if="customer.recent_orders && customer.recent_orders.length > 0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Payment</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="order in customer.recent_orders" :key="order.id">
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-5 py-3">
                                            <a :href="`/admin/orders/${order.id}`"
                                                class="font-mono text-xs font-semibold text-green-700 hover:underline"
                                                x-text="'#' + order.order_number">
                                            </a>
                                        </td>
                                        <td class="px-5 py-3 text-xs text-gray-500"
                                            x-text="(order.items_count ?? 0) + ' item' + (order.items_count !== 1 ? 's' : '')">
                                        </td>
                                        <td class="px-5 py-3 text-xs font-semibold text-gray-900">
                                            ৳<span x-text="Number(order.grand_total).toLocaleString()"></span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                                                :class="statusColor(order.order_status)"
                                                x-text="order.order_status">
                                            </span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                                                :class="paymentStatusColor(order.payment_status)"
                                                x-text="order.payment_status">
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-xs text-gray-400"
                                            x-text="order.placed_at ? new Date(order.placed_at).toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'2-digit'}) : '—'">
                                        </td>
                                        <td class="px-5 py-3">
                                            <a :href="`/admin/orders/${order.id}`"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>

        </div>
    </template>

    {{-- Not found --}}
    <template x-if="!loading && !customer">
        <div class="text-center py-16 text-gray-400">
            <i class="fa-solid fa-user-slash text-3xl mb-3 block"></i>
            Customer not found
        </div>
    </template>

</div>

@endsection

@push('scripts')
<script>
function customerDetail(customerId) {
    return {
        customer: null,
        loading: true,

        async init() {
            await this.loadCustomer();
        },

        async loadCustomer() {
            this.loading = true;
            try {
                const r = await fetch(`/api/v1/admin/customers/${customerId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await r.json();
                this.customer = data.data ?? null;
            } catch (e) {
                console.error('Failed to load customer', e);
            } finally {
                this.loading = false;
            }
        },

        async toggleActive() {
            try {
                const r = await fetch(`/api/v1/admin/customers/${customerId}/toggle-active`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await r.json();
                if (data.data?.is_active !== undefined) {
                    this.customer.is_active = data.data.is_active;
                }
            } catch (e) {
                console.error('Failed to toggle active', e);
            }
        },

        statusColor(s) {
            const c = {
                pending:    'bg-yellow-100 text-yellow-800',
                confirmed:  'bg-blue-100 text-blue-800',
                processing: 'bg-indigo-100 text-indigo-800',
                shipped:    'bg-cyan-100 text-cyan-800',
                delivered:  'bg-green-100 text-green-800',
                cancelled:  'bg-red-100 text-red-800',
                returned:   'bg-gray-100 text-gray-700',
            };
            return c[s] ?? 'bg-gray-100 text-gray-700';
        },

        paymentStatusColor(s) {
            return s === 'paid'   ? 'bg-green-100 text-green-800'
                 : s === 'failed' ? 'bg-red-100 text-red-800'
                 : 'bg-yellow-100 text-yellow-800';
        },
    };
}
</script>
@endpush
