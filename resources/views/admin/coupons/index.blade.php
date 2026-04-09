@extends('layouts.admin')

@section('title', 'Coupons')

@section('content')

    <div x-data="couponManager()" x-init="init()">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Coupons</h2>
                <p class="text-sm text-gray-500 mt-0.5">Manage discount codes and usage analytics</p>
            </div>
            <div class="flex gap-2">
                @can('coupon.create')
                    <button @click="openBulkModal()"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                        <i class="fa-solid fa-wand-magic-sparkles text-xs"></i> Bulk Generate
                    </button>
                    <button @click="openCreateModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-green-700 text-white rounded-lg hover:bg-green-800 transition cursor-pointer">
                        <i class="fa-solid fa-plus text-xs"></i> New Coupon
                    </button>
                @endcan
            </div>
        </div>

        {{-- Stats Bar --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
            <div class="bg-white border border-gray-200 rounded-xl px-4 py-3">
                <p class="text-xs text-gray-500 mb-0.5">Total</p>
                <p class="text-xl font-bold text-gray-900" x-text="stats.total ?? '—'"></p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl px-4 py-3">
                <p class="text-xs text-gray-500 mb-0.5">Active</p>
                <p class="text-xl font-bold text-green-700" x-text="stats.active ?? '—'"></p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl px-4 py-3">
                <p class="text-xs text-gray-500 mb-0.5">Expired</p>
                <p class="text-xl font-bold text-red-600" x-text="stats.expired ?? '—'"></p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl px-4 py-3">
                <p class="text-xs text-gray-500 mb-0.5">Total Uses</p>
                <p class="text-xl font-bold text-gray-900" x-text="stats.total_usages ?? '—'"></p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl px-4 py-3">
                <p class="text-xs text-gray-500 mb-0.5">Discount Given</p>
                <p class="text-xl font-bold text-indigo-700">
                    ৳<span x-text="stats.total_discount ? Number(stats.total_discount).toLocaleString() : '0'"></span>
                </p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white border border-gray-200 rounded-xl mb-4 p-4 flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-52">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" x-model="search" @input.debounce.400ms="load(1)" placeholder="Search by code…"
                    class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-green-600">
            </div>
            <select x-model="filterStatus" @change="load(1)"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
                <option value="">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="expired">Expired</option>
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
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Discount</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Min. Purchase</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Usage</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Saved</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Validity</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">

                        <template x-if="loading">
                            <template x-for="i in 8" :key="i">
                                <tr>
                                    <td colspan="8" class="px-5 py-4">
                                        <div class="h-4 bg-gray-100 rounded animate-pulse w-full"></div>
                                    </td>
                                </tr>
                            </template>
                        </template>

                        <template x-if="!loading">
                            <template x-for="c in coupons" :key="c.id">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs font-semibold text-gray-800 tracking-wide"
                                                x-text="c.code"></span>
                                            <button @click="copyCode(c.code)"
                                                class="text-gray-400 hover:text-green-700 transition cursor-pointer text-xs"
                                                title="Copy">
                                                <i class="fa-regular fa-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-xs">
                                        <span class="font-semibold text-gray-900"
                                            x-text="c.type === 'percentage' ? c.value + '%' : '৳' + Number(c.value).toLocaleString()">
                                        </span>
                                        <span class="ml-1 text-gray-400"
                                            x-text="c.type === 'percentage' ? 'off' : 'flat'"></span>
                                    </td>
                                    <td class="px-5 py-3 text-xs text-gray-600"
                                        x-text="c.min_purchase ? '৳' + Number(c.min_purchase).toLocaleString() : '—'">
                                    </td>
                                    <td class="px-5 py-3 text-xs">
                                        <span class="font-semibold text-gray-800"
                                            x-text="c.usages_count ?? c.used_count"></span>
                                        <span class="text-gray-400"
                                            x-text="c.usage_limit ? ' / ' + c.usage_limit : ' / ∞'"></span>
                                    </td>
                                    <td class="px-5 py-3 text-xs font-semibold text-indigo-700">
                                        <span x-show="c.total_discount != null">
                                            ৳<span x-text="Number(c.total_discount ?? 0).toLocaleString()"></span>
                                        </span>
                                        <span x-show="c.total_discount == null" class="text-gray-400">—</span>
                                    </td>
                                    <td class="px-5 py-3 text-xs text-gray-500">
                                        <template x-if="c.start_date || c.end_date">
                                            <span>
                                                <span x-text="c.start_date ? fmtDate(c.start_date) : '∞'"></span>
                                                →
                                                <span x-text="c.end_date ? fmtDate(c.end_date) : '∞'"></span>
                                            </span>
                                        </template>
                                        <template x-if="!c.start_date && !c.end_date">
                                            <span class="text-gray-400">No limit</span>
                                        </template>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                            :class="statusBadge(c)">
                                            <span x-text="statusLabel(c)"></span>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            @can('coupon.update')
                                                <button @click="openEditModal(c)"
                                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium transition cursor-pointer">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                            @endcan
                                            @can('coupon.delete')
                                                <button @click="confirmDelete(c)"
                                                    class="text-xs text-red-500 hover:text-red-700 transition cursor-pointer">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>

                        <template x-if="!loading && coupons.length === 0">
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                                    <i class="fa-solid fa-ticket text-2xl mb-2 block"></i>
                                    No coupons found
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
                    &bull; <span x-text="meta.total"></span> coupons
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

        {{-- ============================================================ --}}
        {{-- CREATE / EDIT MODAL                                          --}}
        {{-- ============================================================ --}}
        <div x-show="showFormModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @keydown.escape.window="showFormModal = false">

            <div @click.outside="showFormModal = false"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800" x-text="editingId ? 'Edit Coupon' : 'New Coupon'"></h3>
                    <button @click="showFormModal = false" class="text-gray-400 hover:text-gray-700 cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form @submit.prevent="saveCoupon()" class="px-6 py-5 space-y-4">

                    {{-- Code --}}
                    <div x-show="!editingId">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Code <span
                                class="text-red-500">*</span></label>
                        <input type="text" x-model="form.code"
                            :class="errors.code ? 'border-red-400' : 'border-gray-200'" placeholder="e.g. SAVE20"
                            class="w-full border rounded-lg px-3 py-2 text-sm uppercase tracking-wide outline-none focus:ring-2 focus:ring-green-600">
                        <p x-show="errors.code" class="text-xs text-red-500 mt-1" x-text="errors.code?.[0]"></p>
                    </div>
                    <div x-show="editingId">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Code</label>
                        <p class="font-mono font-bold text-gray-800 text-sm tracking-wide px-3 py-2 bg-gray-50 rounded-lg"
                            x-text="form.code"></p>
                    </div>

                    {{-- Type + Value --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Type <span
                                    class="text-red-500">*</span></label>
                            <select x-model="form.type" :class="errors.type ? 'border-red-400' : 'border-gray-200'"
                                class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
                                <option value="fixed">Fixed (৳)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                            <p x-show="errors.type" class="text-xs text-red-500 mt-1" x-text="errors.type?.[0]"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">
                                Value <span class="text-red-500">*</span>
                                <span class="text-gray-400 font-normal"
                                    x-text="form.type === 'percentage' ? '(%)' : '(৳)'"></span>
                            </label>
                            <input type="number" x-model="form.value" min="0" step="0.01"
                                :class="errors.value ? 'border-red-400' : 'border-gray-200'"
                                class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                            <p x-show="errors.value" class="text-xs text-red-500 mt-1" x-text="errors.value?.[0]"></p>
                        </div>
                    </div>

                    {{-- Min purchase + Usage limit --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Min Purchase (৳)</label>
                            <input type="number" x-model="form.min_purchase" min="0" step="0.01"
                                placeholder="Optional"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Total Usage Limit</label>
                            <input type="number" x-model="form.usage_limit" min="1" placeholder="Unlimited"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                        </div>
                    </div>

                    {{-- Limit per user --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Limit Per User</label>
                        <input type="number" x-model="form.limit_per_user" min="1" placeholder="Unlimited"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                    </div>

                    {{-- Validity dates --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Start Date</label>
                            <input type="datetime-local" x-model="form.start_date"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                            <p x-show="errors.start_date" class="text-xs text-red-500 mt-1"
                                x-text="errors.start_date?.[0]"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">End Date</label>
                            <input type="datetime-local" x-model="form.end_date"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                            <p x-show="errors.end_date" class="text-xs text-red-500 mt-1" x-text="errors.end_date?.[0]">
                            </p>
                        </div>
                    </div>

                    {{-- Active toggle --}}
                    <div class="flex items-center gap-3 pt-1">
                        <button type="button" @click="form.is_active = !form.is_active"
                            class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                            :class="form.is_active ? 'bg-green-600' : 'bg-gray-300'">
                            <span
                                class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                :class="form.is_active ? 'translate-x-4' : 'translate-x-0'"></span>
                        </button>
                        <span class="text-sm text-gray-700" x-text="form.is_active ? 'Active' : 'Inactive'"></span>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 mt-4">
                        <button type="button" @click="showFormModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" :disabled="saving"
                            class="px-5 py-2 text-sm font-semibold bg-green-700 text-white rounded-lg hover:bg-green-800 disabled:opacity-50 transition cursor-pointer disabled:cursor-not-allowed">
                            <span x-text="saving ? 'Saving…' : (editingId ? 'Update' : 'Create')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- BULK GENERATE MODAL                                          --}}
        {{-- ============================================================ --}}
        <div x-show="showBulkModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @keydown.escape.window="showBulkModal = false">

            <div @click.outside="showBulkModal = false"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Bulk Generate Coupons</h3>
                    <button @click="showBulkModal = false" class="text-gray-400 hover:text-gray-700 cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Generated codes result --}}
                <template x-if="generatedCodes.length > 0">
                    <div class="px-6 py-4 border-b border-gray-100 bg-green-50">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-semibold text-green-800">
                                <i class="fa-solid fa-check-circle mr-1"></i>
                                <span x-text="generatedCodes.length"></span> coupons generated!
                            </p>
                            <button @click="copyAllCodes()"
                                class="text-xs text-green-700 hover:underline cursor-pointer font-medium">
                                Copy all
                            </button>
                        </div>
                        <div class="max-h-40 overflow-y-auto rounded-lg bg-white border border-green-200 p-3">
                            <div class="grid grid-cols-2 gap-1">
                                <template x-for="code in generatedCodes" :key="code">
                                    <span class="font-mono text-xs text-gray-700 bg-gray-50 px-2 py-0.5 rounded"
                                        x-text="code"></span>
                                </template>
                            </div>
                        </div>
                        <button @click="generatedCodes = []; resetBulkForm()"
                            class="mt-3 text-xs text-green-700 hover:underline cursor-pointer">
                            Generate more
                        </button>
                    </div>
                </template>

                <form x-show="generatedCodes.length === 0" @submit.prevent="doBulkGenerate()"
                    class="px-6 py-5 space-y-4">

                    {{-- Prefix + Count --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Prefix <span
                                    class="text-red-500">*</span></label>
                            <input type="text" x-model="bulkForm.prefix" placeholder="e.g. SALE"
                                :class="bulkErrors.prefix ? 'border-red-400' : 'border-gray-200'"
                                class="w-full border rounded-lg px-3 py-2 text-sm uppercase tracking-wide outline-none focus:ring-2 focus:ring-green-600">
                            <p x-show="bulkErrors.prefix" class="text-xs text-red-500 mt-1"
                                x-text="bulkErrors.prefix?.[0]"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Count <span
                                    class="text-red-500">*</span> <span class="text-gray-400 font-normal">(max
                                    500)</span></label>
                            <input type="number" x-model="bulkForm.count" min="1" max="500"
                                placeholder="10" :class="bulkErrors.count ? 'border-red-400' : 'border-gray-200'"
                                class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                            <p x-show="bulkErrors.count" class="text-xs text-red-500 mt-1"
                                x-text="bulkErrors.count?.[0]"></p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 -mt-2">Codes will look like: <span
                            class="font-mono font-semibold text-gray-600"
                            x-text="(bulkForm.prefix || 'SALE').toUpperCase() + 'XXXXXXXX'"></span></p>

                    {{-- Type + Value --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Type <span
                                    class="text-red-500">*</span></label>
                            <select x-model="bulkForm.type"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
                                <option value="fixed">Fixed (৳)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">
                                Value <span class="text-red-500">*</span>
                                <span class="text-gray-400 font-normal"
                                    x-text="bulkForm.type === 'percentage' ? '(%)' : '(৳)'"></span>
                            </label>
                            <input type="number" x-model="bulkForm.value" min="0" step="0.01"
                                :class="bulkErrors.value ? 'border-red-400' : 'border-gray-200'"
                                class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                            <p x-show="bulkErrors.value" class="text-xs text-red-500 mt-1"
                                x-text="bulkErrors.value?.[0]"></p>
                        </div>
                    </div>

                    {{-- Min purchase + Usage limit --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Min Purchase (৳)</label>
                            <input type="number" x-model="bulkForm.min_purchase" min="0" placeholder="Optional"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Usage Limit / Code</label>
                            <input type="number" x-model="bulkForm.usage_limit" min="1" placeholder="Unlimited"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                        </div>
                    </div>

                    {{-- Limit per user --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Limit Per User</label>
                        <input type="number" x-model="bulkForm.limit_per_user" min="1" placeholder="Unlimited"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                    </div>

                    {{-- Dates --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Start Date</label>
                            <input type="datetime-local" x-model="bulkForm.start_date"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">End Date</label>
                            <input type="datetime-local" x-model="bulkForm.end_date"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                        </div>
                    </div>

                    {{-- Active toggle --}}
                    <div class="flex items-center gap-3">
                        <button type="button" @click="bulkForm.is_active = !bulkForm.is_active"
                            class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors"
                            :class="bulkForm.is_active ? 'bg-green-600' : 'bg-gray-300'">
                            <span
                                class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow transition"
                                :class="bulkForm.is_active ? 'translate-x-4' : 'translate-x-0'"></span>
                        </button>
                        <span class="text-sm text-gray-700"
                            x-text="bulkForm.is_active ? 'Active immediately' : 'Inactive'"></span>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 mt-4">
                        <button type="button" @click="showBulkModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" :disabled="bulkSaving"
                            class="px-5 py-2 text-sm font-semibold bg-green-700 text-white rounded-lg hover:bg-green-800 disabled:opacity-50 transition cursor-pointer disabled:cursor-not-allowed">
                            <span x-text="bulkSaving ? 'Generating…' : 'Generate'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- DELETE CONFIRM MODAL                                         --}}
        {{-- ============================================================ --}}
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @keydown.escape.window="showDeleteModal = false">
            <div @click.outside="showDeleteModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-trash text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Delete Coupon</h3>
                        <p class="text-xs text-gray-500">This action cannot be undone.</p>
                    </div>
                </div>
                <p class="text-sm text-gray-700 mb-5">
                    Delete coupon <span class="font-mono font-bold" x-text="deleteTarget?.code"></span>?
                    <span x-show="deleteTarget?.usages_count > 0" class="text-yellow-600 block mt-1 text-xs">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        This coupon has been used <span x-text="deleteTarget?.usages_count"></span> time(s).
                    </span>
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
    <script>
        function couponManager() {
            const emptyForm = () => ({
                code: '',
                type: 'fixed',
                value: '',
                min_purchase: '',
                usage_limit: '',
                limit_per_user: '',
                start_date: '',
                end_date: '',
                is_active: true,
            });

            const emptyBulk = () => ({
                prefix: '',
                count: 10,
                type: 'fixed',
                value: '',
                min_purchase: '',
                usage_limit: '',
                limit_per_user: '',
                start_date: '',
                end_date: '',
                is_active: true,
            });

            return {
                coupons: [],
                meta: {},
                loading: true,
                stats: {},
                search: '',
                filterStatus: '',

                // form modal
                showFormModal: false,
                editingId: null,
                form: emptyForm(),
                errors: {},
                saving: false,

                // bulk modal
                showBulkModal: false,
                bulkForm: emptyBulk(),
                bulkErrors: {},
                bulkSaving: false,
                generatedCodes: [],

                // delete modal
                showDeleteModal: false,
                deleteTarget: null,
                deleting: false,

                async init() {
                    await Promise.all([this.load(), this.loadStats()]);
                },

                async load(page = 1) {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({
                            page
                        });
                        if (this.search) params.set('q', this.search);
                        if (this.filterStatus) params.set('status', this.filterStatus);

                        const r = await fetch(`/api/v1/admin/coupons?${params}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await r.json();
                        this.coupons = data.data ?? [];
                        this.meta = data.meta ?? {};
                    } catch (e) {
                        console.error('Failed to load coupons', e);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadStats() {
                    try {
                        const r = await fetch('/api/v1/admin/coupons/stats', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await r.json();
                        this.stats = data.data ?? {};
                    } catch (e) {
                        console.error('Failed to load stats', e);
                    }
                },

                // ── Create / Edit ─────────────────────────────────────────────────
                openCreateModal() {
                    this.editingId = null;
                    this.form = emptyForm();
                    this.errors = {};
                    this.showFormModal = true;
                },

                openEditModal(coupon) {
                    this.editingId = coupon.id;
                    this.form = {
                        code: coupon.code,
                        type: coupon.type,
                        value: coupon.value,
                        min_purchase: coupon.min_purchase ?? '',
                        usage_limit: coupon.usage_limit ?? '',
                        limit_per_user: coupon.limit_per_user ?? '',
                        start_date: coupon.start_date ? this.toDatetimeLocal(coupon.start_date) : '',
                        end_date: coupon.end_date ? this.toDatetimeLocal(coupon.end_date) : '',
                        is_active: coupon.is_active,
                    };
                    this.errors = {};
                    this.showFormModal = true;
                },

                async saveCoupon() {
                    this.saving = true;
                    this.errors = {};
                    try {
                        const url = this.editingId ?
                            `/api/v1/admin/coupons/${this.editingId}` :
                            `/api/v1/admin/coupons`;
                        const method = this.editingId ? 'PUT' : 'POST';

                        const payload = {
                            ...this.form
                        };
                        // Convert empty strings to null for nullable fields
                        ['min_purchase', 'usage_limit', 'limit_per_user', 'start_date', 'end_date'].forEach(k => {
                            if (payload[k] === '' || payload[k] === null) payload[k] = null;
                        });
                        if (!this.editingId) payload.code = payload.code.toUpperCase();

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

                        if (r.status === 422) {
                            this.errors = data.errors ?? {};
                            return;
                        }
                        if (!r.ok) {
                            alert(data.message ?? 'An error occurred');
                            return;
                        }
                        window.flash?.(this.editingId ? 'Coupon updated' : 'Coupon created', 'success');
                        this.showFormModal = false;
                        await this.load(this.meta.current_page ?? 1);
                        await this.loadStats();
                    } finally {
                        this.saving = false;
                    }
                },

                // ── Bulk generate ─────────────────────────────────────────────────
                openBulkModal() {
                    this.generatedCodes = [];
                    this.resetBulkForm();
                    this.showBulkModal = true;
                },

                resetBulkForm() {
                    this.bulkForm = emptyBulk();
                    this.bulkErrors = {};
                },

                async doBulkGenerate() {
                    this.bulkSaving = true;
                    this.bulkErrors = {};
                    try {
                        const payload = {
                            ...this.bulkForm
                        };
                        ['min_purchase', 'usage_limit', 'limit_per_user', 'start_date', 'end_date'].forEach(k => {
                            if (payload[k] === '') payload[k] = null;
                        });

                        const r = await fetch('/api/v1/admin/coupons/bulk-generate', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(payload),
                        });

                        const data = await r.json();

                        if (r.status === 422) {
                            this.bulkErrors = data.errors ?? {};
                            return;
                        }
                        if (!r.ok) {
                            alert(data.message ?? 'An error occurred');
                            return;
                        }

                        this.generatedCodes = data.data?.codes ?? [];
                        await this.load(1);
                        await this.loadStats();
                    } finally {
                        this.bulkSaving = false;
                    }
                },

                async copyAllCodes() {
                    await navigator.clipboard.writeText(this.generatedCodes.join('\n'));
                },

                // ── Delete ─────────────────────────────────────────────────────────
                confirmDelete(coupon) {
                    this.deleteTarget = coupon;
                    this.showDeleteModal = true;
                },

                async doDelete() {
                    this.deleting = true;
                    try {
                        const r = await fetch(`/api/v1/admin/coupons/${this.deleteTarget.id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });
                        if (r.ok) {
                            this.showDeleteModal = false;
                            await this.load(this.meta.current_page ?? 1);
                            await this.loadStats();
                        }
                    } finally {
                        this.deleting = false;
                    }
                },

                // ── Helpers ────────────────────────────────────────────────────────
                async copyCode(code) {
                    await navigator.clipboard.writeText(code);
                    window.flash?.(`Coupon code "${code}" copied`, 'success');
                },

                statusLabel(c) {
                    if (!c.is_active) return 'Inactive';
                    if (c.end_date && new Date(c.end_date) < new Date()) return 'Expired';
                    if (c.start_date && new Date(c.start_date) > new Date()) return 'Scheduled';
                    return 'Active';
                },

                statusBadge(c) {
                    const label = this.statusLabel(c);
                    if (label === 'Active') return 'bg-green-100 text-green-800';
                    if (label === 'Expired') return 'bg-red-100 text-red-800';
                    if (label === 'Scheduled') return 'bg-blue-100 text-blue-800';
                    return 'bg-gray-100 text-gray-700';
                },

                fmtDate(d) {
                    return new Date(d).toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: '2-digit'
                    });
                },

                toDatetimeLocal(iso) {
                    return new Date(iso).toISOString().slice(0, 16);
                },
            };
        }
    </script>
@endpush
