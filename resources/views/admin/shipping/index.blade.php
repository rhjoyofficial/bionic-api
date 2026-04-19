@extends('layouts.admin')

@section('title', 'Shipping Zones')

@section('content')

<div x-data="shippingManager()" x-init="init()">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Shipping Zones</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Drag rows to reorder — order is reflected in the checkout zone picker
            </p>
        </div>
        @can('shipping.create')
        <button @click="openCreateModal()"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-green-700 text-white rounded-lg hover:bg-green-800 transition cursor-pointer">
            <i class="fa-solid fa-plus text-xs"></i> New Zone
        </button>
        @endcan
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3">
            <p class="text-xs text-gray-500 mb-0.5">Total Zones</p>
            <p class="text-xl font-bold text-gray-900" x-text="zones.length"></p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3">
            <p class="text-xs text-gray-500 mb-0.5">Active</p>
            <p class="text-xl font-bold text-green-700" x-text="zones.filter(z => z.is_active).length"></p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3">
            <p class="text-xs text-gray-500 mb-0.5">With Free Shipping</p>
            <p class="text-xl font-bold text-blue-700" x-text="zones.filter(z => z.free_shipping_threshold).length"></p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3">
            <p class="text-xs text-gray-500 mb-0.5">Total Orders</p>
            <p class="text-xl font-bold text-gray-900"
                x-text="zones.reduce((s, z) => s + (z.orders_count ?? 0), 0).toLocaleString()">
            </p>
        </div>
    </div>

    {{-- Reorder save indicator --}}
    <div x-show="reordering" x-cloak
        class="mb-3 inline-flex items-center gap-2 text-xs text-blue-700 bg-blue-50 border border-blue-200 rounded-lg px-3 py-1.5">
        <i class="fa-solid fa-spinner fa-spin"></i> Saving new order…
    </div>
    <div x-show="reorderSaved" x-cloak
        class="mb-3 inline-flex items-center gap-2 text-xs text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-1.5">
        <i class="fa-solid fa-check"></i> Order saved
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-3 py-3 w-10"></th>{{-- drag handle --}}
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Zone Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Charge</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Free Above</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Est. Days</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Orders</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase"></th>
                    </tr>
                </thead>
                <tbody x-ref="sortableBody" class="divide-y divide-gray-50">

                    <template x-if="loading">
                        <template x-for="i in 5" :key="i">
                            <tr>
                                <td colspan="9" class="px-5 py-4">
                                    <div class="h-4 bg-gray-100 rounded animate-pulse w-full"></div>
                                </td>
                            </tr>
                        </template>
                    </template>

                    <template x-if="!loading">
                        <template x-for="(zone, index) in zones" :key="zone.id">
                            <tr class="hover:bg-gray-50 transition group" :data-id="zone.id">
                                {{-- Drag handle --}}
                                <td class="px-3 py-3 text-center">
                                    <span class="drag-handle inline-block text-gray-300 group-hover:text-gray-500 cursor-grab active:cursor-grabbing transition select-none">
                                        <i class="fa-solid fa-grip-vertical"></i>
                                    </span>
                                </td>
                                {{-- Sort order badge --}}
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-bold text-gray-600"
                                        x-text="index + 1">
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="font-semibold text-gray-800 text-xs" x-text="zone.name"></span>
                                </td>
                                <td class="px-5 py-3 text-xs font-semibold text-gray-900">
                                    ৳<span x-text="Number(zone.base_charge).toLocaleString()"></span>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-600">
                                    <span x-show="zone.free_shipping_threshold">
                                        ৳<span x-text="Number(zone.free_shipping_threshold).toLocaleString()"></span>
                                    </span>
                                    <span x-show="!zone.free_shipping_threshold" class="text-gray-400">—</span>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-600">
                                    <span x-show="zone.estimated_days">
                                        <span x-text="zone.estimated_days"></span> day<span x-text="zone.estimated_days > 1 ? 's' : ''"></span>
                                    </span>
                                    <span x-show="!zone.estimated_days" class="text-gray-400">—</span>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-600"
                                    x-text="(zone.orders_count ?? 0).toLocaleString()">
                                </td>
                                <td class="px-5 py-3">
                                    @can('shipping.update')
                                    <button @click="toggleActive(zone)"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium transition cursor-pointer"
                                        :class="zone.is_active
                                            ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                        x-text="zone.is_active ? 'Active' : 'Inactive'">
                                    </button>
                                    @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="zone.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                                        x-text="zone.is_active ? 'Active' : 'Inactive'">
                                    </span>
                                    @endcan
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        @can('shipping.update')
                                        <button @click="openEditModal(zone)"
                                            class="text-xs text-blue-600 hover:text-blue-800 font-medium transition cursor-pointer">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        @endcan
                                        @can('shipping.delete')
                                        <button @click="confirmDelete(zone)"
                                            class="text-xs transition cursor-pointer"
                                            :class="(zone.orders_count ?? 0) > 0
                                                ? 'text-gray-300 cursor-not-allowed'
                                                : 'text-red-500 hover:text-red-700'"
                                            :disabled="(zone.orders_count ?? 0) > 0"
                                            :title="(zone.orders_count ?? 0) > 0 ? 'Zone has orders — cannot delete' : 'Delete zone'">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>

                    <template x-if="!loading && zones.length === 0">
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center text-gray-400">
                                <i class="fa-solid fa-truck text-2xl mb-2 block"></i>
                                No shipping zones yet
                            </td>
                        </tr>
                    </template>

                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- CREATE / EDIT MODAL                                          --}}
    {{-- ============================================================ --}}
    <div x-show="showFormModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @keydown.escape.window="showFormModal = false">

        <div @click.outside="showFormModal = false"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-md">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800" x-text="editingId ? 'Edit Shipping Zone' : 'New Shipping Zone'"></h3>
                <button @click="showFormModal = false" class="text-gray-400 hover:text-gray-700 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form @submit.prevent="saveZone()" class="px-6 py-5 space-y-4">

                {{-- Name --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Zone Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.name" placeholder="e.g. Dhaka City"
                        :class="errors.name ? 'border-red-400' : 'border-gray-200'"
                        class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                    <p x-show="errors.name" class="text-xs text-red-500 mt-1" x-text="errors.name?.[0]"></p>
                </div>

                {{-- Charge + Free threshold --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Base Charge (৳) <span class="text-red-500">*</span></label>
                        <input type="number" x-model="form.base_charge" min="0" step="0.01"
                            :class="errors.base_charge ? 'border-red-400' : 'border-gray-200'"
                            class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                        <p x-show="errors.base_charge" class="text-xs text-red-500 mt-1" x-text="errors.base_charge?.[0]"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Free Shipping Above (৳)</label>
                        <input type="number" x-model="form.free_shipping_threshold" min="0" step="0.01" placeholder="Disabled"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                        <p class="text-xs text-gray-400 mt-0.5">Leave blank to disable</p>
                    </div>
                </div>

                {{-- Estimated days + Sort order --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Estimated Days</label>
                        <input type="number" x-model="form.estimated_days" min="1" placeholder="e.g. 2"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Sort Order</label>
                        <input type="number" x-model="form.sort_order" min="0" max="9999" placeholder="Auto"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                        <p class="text-xs text-gray-400 mt-0.5">Lower = higher in list</p>
                    </div>
                </div>

                {{-- Active toggle --}}
                <div class="flex items-center gap-3 pt-1">
                    <button type="button" @click="form.is_active = !form.is_active"
                        class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200"
                        :class="form.is_active ? 'bg-green-600' : 'bg-gray-300'">
                        <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow transition duration-200"
                            :class="form.is_active ? 'translate-x-4' : 'translate-x-0'"></span>
                    </button>
                    <span class="text-sm text-gray-700" x-text="form.is_active ? 'Active (visible in checkout)' : 'Inactive (hidden from checkout)'"></span>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 mt-4">
                    <button type="button" @click="showFormModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" :disabled="saving"
                        class="px-5 py-2 text-sm font-semibold bg-green-700 text-white rounded-lg hover:bg-green-800 disabled:opacity-50 transition cursor-pointer disabled:cursor-not-allowed">
                        <span x-text="saving ? 'Saving…' : (editingId ? 'Update Zone' : 'Create Zone')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- DELETE CONFIRM MODAL                                         --}}
    {{-- ============================================================ --}}
    <div x-show="showDeleteModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @keydown.escape.window="showDeleteModal = false">
        <div @click.outside="showDeleteModal = false"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-trash text-red-600"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Delete Zone</h3>
                    <p class="text-xs text-gray-500">This action cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-700 mb-5">
                Delete <span class="font-semibold" x-text="deleteTarget?.name"></span>?
            </p>
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
{{-- SortableJS must load before Alpine's defer script initializes --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
function shippingManager() {
    const emptyForm = () => ({
        name: '', base_charge: '', free_shipping_threshold: '',
        estimated_days: '', sort_order: '', is_active: true,
    });

    return {
        zones: [],
        loading: true,
        reordering: false,
        reorderSaved: false,
        _reorderTimer: null,

        // form modal
        showFormModal: false,
        editingId: null,
        form: emptyForm(),
        errors: {},
        saving: false,

        // delete modal
        showDeleteModal: false,
        deleteTarget: null,
        deleting: false,

        async init() {
            await this.load();
            this.$nextTick(() => this.initSortable());
        },

        async load() {
            this.loading = true;
            try {
                const r = await fetch('/api/v1/admin/shipping-zones', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await r.json();
                this.zones = data.data ?? [];
            } catch (e) {
                console.error('Failed to load shipping zones', e);
            } finally {
                this.loading = false;
                // Re-init sortable after data loads
                this.$nextTick(() => this.initSortable());
            }
        },

        initSortable() {
            const el = this.$refs.sortableBody;
            if (!el || typeof Sortable === 'undefined') return;

            // Destroy previous instance if any
            if (this._sortable) this._sortable.destroy();

            this._sortable = Sortable.create(el, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'opacity-40',
                chosenClass: 'bg-green-50',
                dragClass: 'shadow-xl',

                onEnd: (evt) => {
                    if (evt.oldIndex === evt.newIndex) return;

                    // Move item in zones array to match new DOM order
                    const moved = this.zones.splice(evt.oldIndex, 1)[0];
                    this.zones.splice(evt.newIndex, 0, moved);

                    // Assign fresh 1-based sort_order values
                    this.zones = this.zones.map((z, i) => ({ ...z, sort_order: i + 1 }));

                    // Debounce save — wait 800ms in case user drags multiple rows quickly
                    clearTimeout(this._reorderTimer);
                    this._reorderTimer = setTimeout(() => {
                        this.saveOrder(this.zones.map(z => ({ id: z.id, sort_order: z.sort_order })));
                    }, 800);
                },
            });
        },

        async saveOrder(payload) {
            this.reordering = true;
            this.reorderSaved = false;
            try {
                await fetch('/api/v1/admin/shipping-zones/reorder', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ zones: payload }),
                });
                this.reorderSaved = true;
                setTimeout(() => { this.reorderSaved = false; }, 2000);
            } catch (e) {
                console.error('Failed to save order', e);
            } finally {
                this.reordering = false;
            }
        },

        // ── Create / Edit ─────────────────────────────────────────────────
        openCreateModal() {
            this.editingId = null;
            this.form = emptyForm();
            this.errors = {};
            this.showFormModal = true;
        },

        openEditModal(zone) {
            this.editingId = zone.id;
            this.form = {
                name:                    zone.name,
                base_charge:             zone.base_charge,
                free_shipping_threshold: zone.free_shipping_threshold ?? '',
                estimated_days:          zone.estimated_days ?? '',
                sort_order:              zone.sort_order ?? '',
                is_active:               zone.is_active,
            };
            this.errors = {};
            this.showFormModal = true;
        },

        async saveZone() {
            this.saving = true;
            this.errors = {};
            try {
                const url    = this.editingId
                    ? `/api/v1/admin/shipping-zones/${this.editingId}`
                    : '/api/v1/admin/shipping-zones';
                const method = this.editingId ? 'PUT' : 'POST';

                const payload = { ...this.form };
                // free_shipping_threshold and estimated_days are truly nullable in DB
                ['free_shipping_threshold', 'estimated_days'].forEach(k => {
                    if (payload[k] === '') payload[k] = null;
                });
                // sort_order is NOT NULL — keep blank as null and let server fill it
                if (payload.sort_order === '') payload.sort_order = null;

                const r = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await r.json();

                if (r.status === 422) { this.errors = data.errors ?? {}; return; }
                if (!r.ok) { alert(data.message ?? 'An error occurred'); return; }

                this.showFormModal = false;
                await this.load();
            } finally {
                this.saving = false;
            }
        },

        // ── Toggle active ──────────────────────────────────────────────────
        async toggleActive(zone) {
            const previous = zone.is_active;
            zone.is_active = !zone.is_active;  // optimistic update
            zone._toggling = true;
            try {
                const r = await fetch(`/api/v1/admin/shipping-zones/${zone.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ is_active: zone.is_active }),
                });
                const data = await r.json();
                if (!r.ok) {
                    zone.is_active = previous;  // revert on failure
                    alert(data.message ?? 'Failed to update status');
                } else {
                    zone.is_active = data.data?.is_active ?? zone.is_active;
                }
            } catch (e) {
                zone.is_active = previous;
                alert('Network error. Please try again.');
            } finally {
                zone._toggling = false;
            }
        },

        // ── Delete ─────────────────────────────────────────────────────────
        confirmDelete(zone) {
            if ((zone.orders_count ?? 0) > 0) return;
            this.deleteTarget  = zone;
            this.showDeleteModal = true;
        },

        async doDelete() {
            this.deleting = true;
            try {
                const r = await fetch(`/api/v1/admin/shipping-zones/${this.deleteTarget.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                if (r.ok) {
                    this.showDeleteModal = false;
                    await this.load();
                }
            } finally {
                this.deleting = false;
            }
        },
    };
}
</script>
@endpush
