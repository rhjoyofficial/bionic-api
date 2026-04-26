@extends('layouts.admin')

@section('title', 'Customers')

@section('content')

<div x-data="customerList()" x-init="init()" x-cloak>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Customers</h2>
            <p class="text-sm text-gray-500 mt-0.5">Manage registered customers and their accounts</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-sm text-gray-500" x-show="meta.total !== undefined">
                <span x-text="meta.total + ' customers'"></span>
            </div>
            <button @click="openCreateModal()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition cursor-pointer">
                <i class="fa-solid fa-plus"></i> New Customer
            </button>
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
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
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
                                <td class="px-5 py-3 text-xs text-gray-600" x-text="c.phone ?? '—'"></td>
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
                                    <div class="flex items-center gap-1">
                                        <a :href="`/admin/customers/${c.id}`"
                                            class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition"
                                            title="View">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                        <button @click="openEditModal(c)" title="Edit"
                                            class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition cursor-pointer">
                                            <i class="fa-solid fa-pencil text-xs"></i>
                                        </button>
                                        <button @click="openPasswordModal(c)" title="Change Password"
                                            class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded transition cursor-pointer">
                                            <i class="fa-solid fa-key text-xs"></i>
                                        </button>
                                        <template x-if="!c.is_guest">
                                            <button @click="confirmDelete(c)" title="Delete"
                                                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition cursor-pointer">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </template>
                                    </div>
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

    {{-- ─── Create Customer Modal ───────────────────────────────────────── --}}
    <div x-show="createModal.open" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div @click.outside="createModal.open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Create New Customer</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input x-model="createModal.name" type="text" placeholder="John Doe"
                        class="w-full text-sm border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-600 focus:border-transparent outline-none"
                        :class="createModal.errors.name ? 'border-red-400' : 'border-gray-200'">
                    <p x-show="createModal.errors.name" class="text-xs text-red-500 mt-1" x-text="createModal.errors.name?.[0]"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input x-model="createModal.email" type="email" placeholder="john@example.com"
                        class="w-full text-sm border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-600 focus:border-transparent outline-none"
                        :class="createModal.errors.email ? 'border-red-400' : 'border-gray-200'">
                    <p x-show="createModal.errors.email" class="text-xs text-red-500 mt-1" x-text="createModal.errors.email?.[0]"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input x-model="createModal.phone" type="text" placeholder="01XXXXXXXXX"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-600 focus:border-transparent outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input x-model="createModal.password" type="password" placeholder="Min. 8 characters"
                        class="w-full text-sm border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-600 focus:border-transparent outline-none"
                        :class="createModal.errors.password ? 'border-red-400' : 'border-gray-200'">
                    <p x-show="createModal.errors.password" class="text-xs text-red-500 mt-1" x-text="createModal.errors.password?.[0]"></p>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button @click="createModal.open = false"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                    Cancel
                </button>
                <button @click="createCustomer()" :disabled="createModal.loading"
                    class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition cursor-pointer">
                    <i class="fa-solid mr-1" :class="createModal.loading ? 'fa-circle-notch fa-spin' : 'fa-plus'"></i>
                    <span x-text="createModal.loading ? 'Creating…' : 'Create Customer'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ─── Edit Customer Modal ─────────────────────────────────────────── --}}
    <div x-show="editModal.open" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div @click.outside="editModal.open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Edit Customer</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input x-model="editModal.name" type="text"
                        class="w-full text-sm border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none"
                        :class="editModal.errors.name ? 'border-red-400' : 'border-gray-200'">
                    <p x-show="editModal.errors.name" class="text-xs text-red-500 mt-1" x-text="editModal.errors.name?.[0]"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input x-model="editModal.email" type="email"
                        class="w-full text-sm border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none"
                        :class="editModal.errors.email ? 'border-red-400' : 'border-gray-200'">
                    <p x-show="editModal.errors.email" class="text-xs text-red-500 mt-1" x-text="editModal.errors.email?.[0]"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input x-model="editModal.phone" type="text"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button @click="editModal.open = false"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                    Cancel
                </button>
                <button @click="updateCustomer()" :disabled="editModal.loading"
                    class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition cursor-pointer">
                    <span x-text="editModal.loading ? 'Saving…' : 'Save Changes'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ─── Change Password Modal ───────────────────────────────────────── --}}
    <div x-show="passwordModal.open" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div @click.outside="passwordModal.open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-key text-amber-600"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Change Password</h3>
                    <p class="text-xs text-gray-500">For: <span class="font-medium text-gray-700" x-text="passwordModal.customer?.name"></span></p>
                </div>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password <span class="text-red-500">*</span></label>
                    <input x-model="passwordModal.password" type="password" placeholder="Min. 8 characters"
                        class="w-full text-sm border rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none"
                        :class="passwordModal.errors.password ? 'border-red-400' : 'border-gray-200'">
                    <p x-show="passwordModal.errors.password" class="text-xs text-red-500 mt-1" x-text="passwordModal.errors.password?.[0]"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                    <input x-model="passwordModal.password_confirmation" type="password" placeholder="Repeat new password"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button @click="passwordModal.open = false"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                    Cancel
                </button>
                <button @click="changePassword()" :disabled="passwordModal.loading"
                    class="px-4 py-2 text-sm bg-amber-600 text-white rounded-lg hover:bg-amber-700 disabled:opacity-50 transition cursor-pointer">
                    <i class="fa-solid fa-key mr-1"></i>
                    <span x-text="passwordModal.loading ? 'Updating…' : 'Change Password'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ─── Delete Confirmation Modal ───────────────────────────────────── --}}
    <div x-show="deleteModal.open" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div @click.outside="deleteModal.open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-trash text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Delete Customer?</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        This will permanently remove
                        <strong class="text-gray-700" x-text="deleteModal.customer?.name"></strong>.
                        This cannot be undone.
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button @click="deleteModal.open = false"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                    Cancel
                </button>
                <button @click="deleteCustomer()" :disabled="deleteModal.loading"
                    class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 transition cursor-pointer">
                    <span x-text="deleteModal.loading ? 'Deleting…' : 'Delete'"></span>
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

        createModal:   { open: false, name: '', email: '', phone: '', password: '', loading: false, errors: {} },
        editModal:     { open: false, customer: null, name: '', email: '', phone: '', loading: false, errors: {} },
        passwordModal: { open: false, customer: null, password: '', password_confirmation: '', loading: false, errors: {} },
        deleteModal:   { open: false, customer: null, loading: false },

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
                    headers: this.h(),
                });
                const data = await r.json();
                if (data.data?.is_active !== undefined) {
                    customer.is_active = data.data.is_active;
                }
            } catch (e) {
                console.error('Failed to toggle active', e);
            }
        },

        // ── Create ────────────────────────────────────────────────────────────
        openCreateModal() {
            this.createModal = { open: true, name: '', email: '', phone: '', password: '', loading: false, errors: {} };
        },

        async createCustomer() {
            this.createModal.loading = true;
            this.createModal.errors = {};
            try {
                const r = await fetch('/api/v1/admin/customers', {
                    method: 'POST',
                    headers: { ...this.h(), 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name:     this.createModal.name,
                        email:    this.createModal.email,
                        phone:    this.createModal.phone || null,
                        password: this.createModal.password,
                    }),
                });
                const data = await r.json();
                if (r.ok && data.success) {
                    this.createModal.open = false;
                    this.toast('Customer created successfully', 'success');
                    this.load(1);
                } else {
                    this.createModal.errors = data.errors ?? {};
                    if (!Object.keys(this.createModal.errors).length) {
                        this.toast(data.message || 'Failed to create customer', 'error');
                    }
                }
            } catch (e) {
                this.toast('Network error', 'error');
            } finally {
                this.createModal.loading = false;
            }
        },

        // ── Edit ──────────────────────────────────────────────────────────────
        openEditModal(customer) {
            this.editModal = {
                open: true,
                customer,
                name:  customer.name,
                email: customer.email,
                phone: customer.phone ?? '',
                loading: false,
                errors: {},
            };
        },

        async updateCustomer() {
            this.editModal.loading = true;
            this.editModal.errors = {};
            try {
                const r = await fetch(`/api/v1/admin/customers/${this.editModal.customer.id}`, {
                    method: 'PUT',
                    headers: { ...this.h(), 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name:  this.editModal.name,
                        email: this.editModal.email,
                        phone: this.editModal.phone || null,
                    }),
                });
                const data = await r.json();
                if (r.ok && data.success) {
                    const idx = this.customers.findIndex(c => c.id === this.editModal.customer.id);
                    if (idx > -1) {
                        this.customers[idx].name  = data.data.name;
                        this.customers[idx].email = data.data.email;
                        this.customers[idx].phone = data.data.phone;
                    }
                    this.editModal.open = false;
                    this.toast('Customer updated', 'success');
                } else {
                    this.editModal.errors = data.errors ?? {};
                    if (!Object.keys(this.editModal.errors).length) {
                        this.toast(data.message || 'Failed to update customer', 'error');
                    }
                }
            } catch (e) {
                this.toast('Network error', 'error');
            } finally {
                this.editModal.loading = false;
            }
        },

        // ── Change Password ───────────────────────────────────────────────────
        openPasswordModal(customer) {
            this.passwordModal = {
                open: true,
                customer,
                password: '',
                password_confirmation: '',
                loading: false,
                errors: {},
            };
        },

        async changePassword() {
            this.passwordModal.loading = true;
            this.passwordModal.errors = {};
            try {
                const r = await fetch(`/api/v1/admin/customers/${this.passwordModal.customer.id}/change-password`, {
                    method: 'PATCH',
                    headers: { ...this.h(), 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        password:              this.passwordModal.password,
                        password_confirmation: this.passwordModal.password_confirmation,
                    }),
                });
                const data = await r.json();
                if (r.ok && data.success) {
                    this.passwordModal.open = false;
                    this.toast('Password changed successfully', 'success');
                } else {
                    this.passwordModal.errors = data.errors ?? {};
                    if (!Object.keys(this.passwordModal.errors).length) {
                        this.toast(data.message || 'Failed to change password', 'error');
                    }
                }
            } catch (e) {
                this.toast('Network error', 'error');
            } finally {
                this.passwordModal.loading = false;
            }
        },

        // ── Delete ────────────────────────────────────────────────────────────
        confirmDelete(customer) {
            this.deleteModal = { open: true, customer, loading: false };
        },

        async deleteCustomer() {
            this.deleteModal.loading = true;
            try {
                const r = await fetch(`/api/v1/admin/customers/${this.deleteModal.customer.id}`, {
                    method: 'DELETE',
                    headers: this.h(),
                });
                const data = await r.json();
                if (r.ok && data.success) {
                    this.customers = this.customers.filter(c => c.id !== this.deleteModal.customer.id);
                    this.deleteModal.open = false;
                    this.toast('Customer deleted', 'success');
                    this.load(this.meta.current_page ?? 1);
                } else {
                    this.deleteModal.open = false;
                    this.toast(data.message || 'Failed to delete customer', 'error');
                }
            } catch (e) {
                this.toast('Network error', 'error');
            } finally {
                this.deleteModal.loading = false;
            }
        },

        // ── Helpers ───────────────────────────────────────────────────────────
        h() {
            return {
                'Accept':       'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            };
        },

        toast(msg, type = 'success') {
            const el = document.createElement('div');
            el.className = `fixed bottom-5 right-5 z-[9999] px-4 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2 ${type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`;
            el.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${msg}`;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3500);
        },
    };
}
</script>
@endpush
