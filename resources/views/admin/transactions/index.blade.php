@extends('layouts.admin')

@section('title', 'Transactions & Reconciliation')

@section('content')
<div x-data="transactionCenter()" x-init="init()" x-cloak>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Transactions & Reconciliation</h1>
            <p class="text-sm text-gray-500 mt-0.5">Revenue overview, payment ledger, and discrepancy detection</p>
        </div>
        <div x-show="summary && summary.discrepancies > 0"
             class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-50 border border-red-200 text-red-700 text-sm font-medium rounded-lg">
            <i class="fas fa-triangle-exclamation"></i>
            <span x-text="summary.discrepancies"></span> issue(s) need attention
        </div>
    </div>

    {{-- Tab Nav --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex gap-6">
            <button @click="switchTab('summary')"
                    :class="tab === 'summary' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-1.5 pb-3 text-sm font-medium border-b-2 transition">
                <i class="fas fa-chart-line text-xs"></i> Summary
            </button>
            <button @click="switchTab('ledger')"
                    :class="tab === 'ledger' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-1.5 pb-3 text-sm font-medium border-b-2 transition">
                <i class="fas fa-list text-xs"></i> Ledger
                <span x-show="ledgerMeta.total > 0"
                      class="ml-1 bg-gray-100 text-gray-600 text-xs px-1.5 py-0.5 rounded-full"
                      x-text="ledgerMeta.total?.toLocaleString()"></span>
            </button>
            <button @click="switchTab('reconciliation')"
                    :class="tab === 'reconciliation' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-1.5 pb-3 text-sm font-medium border-b-2 transition">
                <i class="fas fa-scale-balanced text-xs"></i> Reconciliation
                <span x-show="summary && summary.discrepancies > 0"
                      class="ml-1 bg-red-100 text-red-700 text-xs px-1.5 py-0.5 rounded-full"
                      x-text="summary?.discrepancies"></span>
            </button>
        </nav>
    </div>

    {{-- ──────────────────────────────────────────────────── --}}
    {{-- TAB: Summary                                         --}}
    {{-- ──────────────────────────────────────────────────── --}}
    <div x-show="tab === 'summary'">

        {{-- Loading skeleton --}}
        <div x-show="summaryLoading" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <template x-for="i in 8">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 animate-pulse">
                    <div class="h-3 bg-gray-200 rounded w-2/3 mb-3"></div>
                    <div class="h-7 bg-gray-200 rounded w-1/2"></div>
                </div>
            </template>
        </div>

        <div x-show="!summaryLoading && summary">
            {{-- Revenue KPIs --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="fmt(summary.totals?.revenue)"></p>
                    <p class="text-xs text-gray-400 mt-1">All paid orders</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Net Revenue</p>
                    <p class="text-2xl font-bold text-green-600" x-text="fmt(summary.totals?.net_revenue)"></p>
                    <p class="text-xs text-gray-400 mt-1">After refunds</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">This Month</p>
                    <p class="text-2xl font-bold text-indigo-600" x-text="fmt(summary.totals?.this_month)"></p>
                    <p class="text-xs text-gray-400 mt-1">Month-to-date</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Today</p>
                    <p class="text-2xl font-bold text-blue-600" x-text="fmt(summary.totals?.today)"></p>
                    <p class="text-xs text-gray-400 mt-1">Today's revenue</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total Refunds</p>
                    <p class="text-2xl font-bold text-red-600" x-text="fmt(summary.totals?.total_refunds)"></p>
                    <p class="text-xs text-gray-400 mt-1">Refunded amount</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Unpaid Orders</p>
                    <p class="text-2xl font-bold text-amber-600" x-text="summary.totals?.unpaid_count"></p>
                    <p class="text-xs text-gray-400 mt-1" x-text="fmt(summary.totals?.unpaid_total) + ' outstanding'"></p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Failed Payments</p>
                    <p class="text-2xl font-bold"
                       :class="summary.totals?.failed_count > 0 ? 'text-red-600' : 'text-gray-900'"
                       x-text="summary.totals?.failed_count"></p>
                    <p class="text-xs text-gray-400 mt-1">Need resolution</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 cursor-pointer hover:border-red-300 transition"
                     @click="switchTab('reconciliation')">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Discrepancies</p>
                    <p class="text-2xl font-bold"
                       :class="summary.discrepancies > 0 ? 'text-red-600' : 'text-green-600'"
                       x-text="summary.discrepancies"></p>
                    <p class="text-xs text-gray-400 mt-1">
                        <span x-show="summary.discrepancies === 0">All reconciled ✓</span>
                        <span x-show="summary.discrepancies > 0" class="text-red-500">View issues →</span>
                    </p>
                </div>
            </div>

            {{-- Revenue Chart + Method Breakdown --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                {{-- 30-Day Revenue Bar Chart --}}
                <div class="lg:col-span-2 bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">Daily Revenue — Last 30 Days</h3>
                    <canvas id="revenueChart" height="90"></canvas>
                </div>

                {{-- Payment Method Breakdown --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">By Payment Method</h3>
                    <div x-show="(summary.by_method ?? []).length === 0"
                         class="text-sm text-gray-400 text-center py-8">No data</div>
                    <template x-for="m in (summary.by_method ?? [])" :key="m.method">
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700 capitalize" x-text="m.method === 'cod' ? 'Cash on Delivery' : 'SSLCommerz'"></span>
                                <span class="text-gray-500" x-text="m.count + ' orders'"></span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="h-2 rounded-full bg-indigo-500"
                                     :style="'width:' + methodPct(m) + '%'"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1" x-text="fmt(m.total)"></p>
                        </div>
                    </template>

                    {{-- Transaction type breakdown --}}
                    <div class="border-t border-gray-100 pt-4 mt-2">
                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">By Type</h4>
                        <template x-for="[type, data] in Object.entries(summary.by_type ?? {})" :key="type">
                            <div class="flex items-center justify-between mb-2">
                                <span class="flex items-center gap-1.5 text-xs text-gray-600">
                                    <span :class="typeColor(type)" class="w-2 h-2 rounded-full inline-block"></span>
                                    <span class="capitalize" x-text="type"></span>
                                </span>
                                <div class="text-right">
                                    <span class="text-xs font-medium text-gray-700" x-text="fmt(data.total)"></span>
                                    <span class="text-xs text-gray-400 ml-1" x-text="'(' + data.count + ')'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ──────────────────────────────────────────────────── --}}
    {{-- TAB: Transaction Ledger                              --}}
    {{-- ──────────────────────────────────────────────────── --}}
    <div x-show="tab === 'ledger'">

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex flex-wrap gap-3 items-center">
            <div class="relative flex-1 min-w-[220px]">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                <input x-model="ledgerSearch" @input.debounce.400ms="ledgerPage=1; loadLedger()"
                       type="text" placeholder="Search order #, customer, description…"
                       class="pl-9 pr-4 py-2 w-full text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <select x-model="ledgerType" @change="ledgerPage=1; loadLedger()"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                <option value="">All Types</option>
                <option value="charge">Charge</option>
                <option value="refund">Refund</option>
                <option value="discount">Discount</option>
                <option value="coupon">Coupon</option>
                <option value="shipping">Shipping</option>
                <option value="commission">Commission</option>
            </select>
            <input x-model="ledgerFrom" @change="ledgerPage=1; loadLedger()"
                   type="date" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                   title="From date">
            <input x-model="ledgerTo" @change="ledgerPage=1; loadLedger()"
                   type="date" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                   title="To date">
            <button @click="ledgerSearch=''; ledgerType=''; ledgerFrom=''; ledgerTo=''; ledgerPage=1; loadLedger()"
                    class="text-sm text-gray-500 hover:text-gray-700 px-2">
                <i class="fas fa-rotate-left mr-1"></i> Reset
            </button>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div x-show="ledgerLoading" class="p-8 flex justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <table x-show="!ledgerLoading" class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="tx in ledger" :key="tx.id">
                        <tr class="hover:bg-gray-50 transition cursor-pointer"
                            @click="openOrderPanel(tx.order_id)">
                            <td class="px-5 py-3.5 text-sm text-gray-500 whitespace-nowrap"
                                x-text="fmtDate(tx.created_at)"></td>
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-medium text-indigo-600" x-text="tx.order_number"></p>
                                <p class="text-xs text-gray-400 mt-0.5" x-text="tx.order?.customer_name ?? ''"></p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span :class="typeBadge(tx.type)"
                                      class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                      x-text="tx.type"></span>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-gray-600 max-w-xs truncate" x-text="tx.description ?? '—'"></td>
                            <td class="px-5 py-3.5 text-right">
                                <span :class="tx.type === 'refund' || tx.type === 'discount' || tx.type === 'coupon'
                                              ? 'text-red-600' : 'text-green-700'"
                                      class="text-sm font-semibold">
                                    <span x-text="tx.type === 'refund' || tx.type === 'discount' || tx.type === 'coupon' ? '−' : '+'"></span>
                                    <span x-text="fmt(tx.amount)"></span>
                                </span>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!ledgerLoading && ledger.length === 0">
                        <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                            <i class="fas fa-receipt text-3xl mb-3 block"></i>
                            No transactions found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div x-show="ledgerMeta.last_page > 1" class="flex items-center justify-between mt-4">
            <p class="text-sm text-gray-500">
                Page <span x-text="ledgerMeta.current_page"></span> of <span x-text="ledgerMeta.last_page"></span>
                &nbsp;·&nbsp; <span x-text="ledgerMeta.total?.toLocaleString()"></span> transactions
            </p>
            <div class="flex gap-2">
                <button @click="ledgerPage--; loadLedger()" :disabled="ledgerPage <= 1"
                        class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
                <button @click="ledgerPage++; loadLedger()" :disabled="ledgerPage >= ledgerMeta.last_page"
                        class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ──────────────────────────────────────────────────── --}}
    {{-- TAB: Reconciliation                                  --}}
    {{-- ──────────────────────────────────────────────────── --}}
    <div x-show="tab === 'reconciliation'">

        {{-- Issue type filter badges --}}
        <div x-show="reconCounts" class="flex flex-wrap gap-2 mb-4">
            <button @click="reconIssue=''; reconPage=1; loadReconciliation()"
                    :class="reconIssue === '' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 border border-gray-200'"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition">
                All Issues
            </button>
            <template x-for="[key, label] in issueTypes" :key="key">
                <button @click="reconIssue = key; reconPage=1; loadReconciliation()"
                        :class="reconIssue === key ? issueActiveClass(key) : 'bg-white text-gray-600 border border-gray-200'"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg transition">
                    <span x-text="label"></span>
                    <span x-show="reconCounts[key] > 0"
                          class="bg-white/30 px-1 rounded-sm text-xs font-bold"
                          x-text="reconCounts[key]"></span>
                </button>
            </template>
        </div>

        {{-- Filter bar --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex flex-wrap gap-3">
            <select x-model="reconMethod" @change="reconPage=1; loadReconciliation()"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                <option value="">All Methods</option>
                <option value="cod">COD</option>
                <option value="sslcommerz">SSLCommerz</option>
            </select>
            <button @click="reconIssue=''; reconMethod=''; reconPage=1; loadReconciliation()"
                    class="text-sm text-gray-500 hover:text-gray-700 px-2">
                <i class="fas fa-rotate-left mr-1"></i> Reset
            </button>
        </div>

        {{-- Reconciliation Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div x-show="reconLoading" class="p-8 flex justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-500"></div>
            </div>

            <table x-show="!reconLoading" class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Method</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Grand Total</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Charged</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Net Received</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Issue</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="row in reconRows" :key="row.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-medium text-indigo-600 cursor-pointer hover:underline"
                                   @click="openOrderPanel(row.id)" x-text="row.order_number"></p>
                                <p class="text-xs text-gray-500 mt-0.5" x-text="row.customer_name"></p>
                                <p class="text-xs text-gray-400" x-text="fmtDate(row.placed_at)"></p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium uppercase"
                                      x-text="row.payment_method"></span>
                                <p x-show="row.gateway_transaction_id"
                                   class="text-xs text-gray-400 font-mono mt-1 truncate max-w-[120px]"
                                   x-text="row.gateway_transaction_id"></p>
                            </td>
                            <td class="px-5 py-3.5 text-right text-sm font-semibold text-gray-800"
                                x-text="fmt(row.grand_total)"></td>
                            <td class="px-5 py-3.5 text-right text-sm"
                                :class="parseFloat(row.charged) >= parseFloat(row.grand_total) ? 'text-green-700' : 'text-red-600'"
                                x-text="fmt(row.charged)"></td>
                            <td class="px-5 py-3.5 text-right text-sm font-medium"
                                :class="parseFloat(row.net_received) > 0 ? 'text-green-700' : 'text-gray-400'"
                                x-text="fmt(row.net_received)"></td>
                            <td class="px-5 py-3.5">
                                <span :class="issueBadge(row.issue_type)"
                                      class="text-xs px-2 py-0.5 rounded-full font-medium"
                                      x-text="issueLabel(row.issue_type)"></span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @can('order.update')
                                <button @click="openReconcileModal(row)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition">
                                    <i class="fas fa-pen-to-square text-xs"></i> Fix
                                </button>
                                @endcan
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!reconLoading && reconRows.length === 0">
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                            <i class="fas fa-circle-check text-3xl text-green-400 mb-3 block"></i>
                            No discrepancies found — payments are reconciled!
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div x-show="reconMeta.last_page > 1" class="flex items-center justify-between mt-4">
            <p class="text-sm text-gray-500">
                Page <span x-text="reconMeta.current_page"></span> of <span x-text="reconMeta.last_page"></span>
            </p>
            <div class="flex gap-2">
                <button @click="reconPage--; loadReconciliation()" :disabled="reconPage <= 1"
                        class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
                <button @click="reconPage++; loadReconciliation()" :disabled="reconPage >= reconMeta.last_page"
                        class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ──────────────────────────────────────────────────────── --}}
    {{-- Order Transactions Side Panel                            --}}
    {{-- ──────────────────────────────────────────────────────── --}}
    <div x-show="panel.open" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 w-full max-w-lg bg-white shadow-2xl z-50 flex flex-col">

        {{-- Panel Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
            <div>
                <h3 class="text-base font-semibold text-gray-900">
                    Order <span x-text="panel.order?.order_number"></span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5" x-text="panel.order?.customer_name"></p>
            </div>
            <button @click="panel.open = false" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        {{-- Panel Body --}}
        <div class="flex-1 overflow-y-auto p-6">
            <div x-show="panel.loading" class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <div x-show="!panel.loading">
                {{-- Payment Status + Summary --}}
                <div class="bg-gray-50 rounded-xl p-4 mb-5 grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Grand Total</p>
                        <p class="text-base font-bold text-gray-900" x-text="fmt(panel.order?.grand_total)"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Payment Status</p>
                        <span :class="paymentStatusBadge(panel.order?.payment_status)"
                              class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                              x-text="panel.order?.payment_status"></span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Method</p>
                        <p class="text-sm font-medium text-gray-700 uppercase" x-text="panel.order?.payment_method"></p>
                    </div>
                    <div x-show="panel.order?.gateway_transaction_id">
                        <p class="text-xs text-gray-500 mb-0.5">Gateway Ref</p>
                        <p class="text-xs font-mono text-gray-600 truncate" x-text="panel.order?.gateway_transaction_id"></p>
                    </div>
                </div>

                {{-- Reconciliation Status Alert --}}
                <div x-show="panel.reconciliation_status && panel.reconciliation_status !== 'ok'"
                     :class="{
                         'bg-red-50 border-red-200 text-red-800': panel.reconciliation_status !== 'ok',
                     }"
                     class="border rounded-lg p-3 mb-4 text-sm flex items-start gap-2">
                    <i class="fas fa-triangle-exclamation mt-0.5"></i>
                    <div>
                        <p class="font-medium" x-text="issueLabel(panel.reconciliation_status)"></p>
                        <p class="text-xs mt-0.5 opacity-80">
                            Charged: <strong x-text="fmt(panel.txSummary?.charged)"></strong>
                            of <strong x-text="fmt(panel.order?.grand_total)"></strong> expected
                        </p>
                    </div>
                </div>

                {{-- Transaction Summary Cards --}}
                <div class="grid grid-cols-3 gap-2 mb-5">
                    <div class="bg-green-50 rounded-lg p-3 text-center">
                        <p class="text-xs text-green-600 font-medium">Charged</p>
                        <p class="text-sm font-bold text-green-800 mt-0.5" x-text="fmt(panel.txSummary?.charged)"></p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3 text-center">
                        <p class="text-xs text-red-600 font-medium">Refunded</p>
                        <p class="text-sm font-bold text-red-800 mt-0.5" x-text="fmt(panel.txSummary?.refunded)"></p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-3 text-center">
                        <p class="text-xs text-indigo-600 font-medium">Net</p>
                        <p class="text-sm font-bold text-indigo-800 mt-0.5" x-text="fmt(panel.txSummary?.net)"></p>
                    </div>
                </div>

                {{-- Transaction List --}}
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Transaction History</h4>
                <div class="space-y-2 mb-6">
                    <template x-for="tx in panel.transactions" :key="tx.id">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-100">
                            <div class="flex items-center gap-2">
                                <span :class="typeColor(tx.type)" class="w-2 h-2 rounded-full flex-shrink-0"></span>
                                <div>
                                    <p class="text-xs font-medium text-gray-700 capitalize" x-text="tx.type"></p>
                                    <p class="text-xs text-gray-400" x-text="tx.description ?? '—'"></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p :class="tx.type === 'refund' || tx.type === 'discount' || tx.type === 'coupon'
                                          ? 'text-red-600' : 'text-green-700'"
                                   class="text-sm font-semibold">
                                    <span x-text="tx.type === 'refund' || tx.type === 'discount' || tx.type === 'coupon' ? '−' : '+'"></span>
                                    <span x-text="fmt(tx.amount)"></span>
                                </p>
                                <p class="text-xs text-gray-400" x-text="fmtDate(tx.created_at)"></p>
                            </div>
                        </div>
                    </template>
                    <p x-show="panel.transactions?.length === 0" class="text-sm text-gray-400 text-center py-4">
                        No transactions recorded
                    </p>
                </div>

                {{-- Actions --}}
                @can('order.update')
                <div class="border-t border-gray-100 pt-4 space-y-3">
                    {{-- Update Payment Status --}}
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Update Payment Status</h4>
                        <div class="flex gap-2">
                            <select x-model="panel.newStatus"
                                    class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                                <option value="failed">Failed</option>
                            </select>
                            <input x-model="panel.gatewayRef" type="text"
                                   placeholder="Gateway ref (optional)"
                                   class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            <button @click="updatePaymentStatus()"
                                    :disabled="panel.updating"
                                    class="px-3 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition">
                                <i class="fas fa-check" :class="panel.updating ? 'animate-pulse' : ''"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Add Manual Transaction --}}
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Add Transaction</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <select x-model="panel.txForm.type"
                                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                                <option value="charge">Charge</option>
                                <option value="refund">Refund</option>
                                <option value="discount">Discount</option>
                                <option value="commission">Commission</option>
                            </select>
                            <input x-model="panel.txForm.amount" type="number" step="0.01" min="0.01"
                                   placeholder="Amount"
                                   class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="flex gap-2 mt-2">
                            <input x-model="panel.txForm.description" type="text"
                                   placeholder="Description (required)"
                                   class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            <button @click="addTransaction()"
                                    :disabled="panel.addingTx || !panel.txForm.amount || !panel.txForm.description"
                                    class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 disabled:opacity-50 transition">
                                <i class="fas fa-plus" :class="panel.addingTx ? 'animate-spin' : ''"></i>
                            </button>
                        </div>
                        <p x-show="panel.txError" class="text-xs text-red-500 mt-1" x-text="panel.txError"></p>
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>

    {{-- Panel overlay --}}
    <div x-show="panel.open" x-transition.opacity
         @click="panel.open = false"
         class="fixed inset-0 bg-black/30 z-40"></div>

    {{-- Reconcile Modal (quick fix from reconciliation tab) --}}
    <div x-show="reconcileModal.open" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div @click.outside="reconcileModal.open = false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-1">Fix Payment Issue</h3>
            <p class="text-sm text-gray-500 mb-4">
                Order <span class="font-medium" x-text="reconcileModal.row?.order_number"></span>
                — <span :class="issueBadge(reconcileModal.row?.issue_type)"
                         class="text-xs px-1.5 py-0.5 rounded-full font-medium"
                         x-text="issueLabel(reconcileModal.row?.issue_type)"></span>
            </p>

            <div class="space-y-3 mb-5">
                <div>
                    <label class="text-xs font-medium text-gray-600 block mb-1">Set Payment Status</label>
                    <select x-model="reconcileModal.status"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600 block mb-1">Gateway Reference (optional)</label>
                    <input x-model="reconcileModal.gatewayRef" type="text"
                           placeholder="e.g. TXN_123456789"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600 block mb-1">Note</label>
                    <input x-model="reconcileModal.note" type="text"
                           placeholder="Reason for this change"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button @click="reconcileModal.open = false"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button @click="applyReconcile()"
                        :disabled="reconcileModal.saving"
                        class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition">
                    <i class="fas fa-check mr-1" :class="reconcileModal.saving ? 'animate-pulse' : ''"></i>
                    <span x-text="reconcileModal.saving ? 'Saving…' : 'Apply Fix'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
function transactionCenter() {
    return {
        tab: 'summary',

        // Summary
        summary: null,
        summaryLoading: false,
        chart: null,

        // Ledger
        ledger: [],
        ledgerPage: 1,
        ledgerMeta: {},
        ledgerLoading: false,
        ledgerSearch: '',
        ledgerType: '',
        ledgerFrom: '',
        ledgerTo: '',

        // Reconciliation
        reconRows: [],
        reconPage: 1,
        reconMeta: {},
        reconCounts: null,
        reconLoading: false,
        reconIssue: '',
        reconMethod: '',
        issueTypes: [
            ['missing_charge',     'Missing Charge'],
            ['underpaid',          'Underpaid'],
            ['unrecorded_payment', 'Unrecorded Payment'],
            ['payment_failed',     'Failed Payment'],
        ],

        // Side panel
        panel: {
            open: false,
            loading: false,
            orderId: null,
            order: null,
            transactions: [],
            txSummary: null,
            reconciliation_status: null,
            newStatus: 'unpaid',
            gatewayRef: '',
            updating: false,
            txForm: { type: 'refund', amount: '', description: '' },
            addingTx: false,
            txError: null,
        },

        // Reconcile modal
        reconcileModal: {
            open: false,
            row: null,
            status: 'paid',
            gatewayRef: '',
            note: '',
            saving: false,
        },

        async init() {
            await this.loadSummary();
        },

        switchTab(tab) {
            this.tab = tab;
            if (tab === 'ledger'          && this.ledger.length === 0)    this.loadLedger();
            if (tab === 'reconciliation'  && this.reconRows.length === 0) this.loadReconciliation();
        },

        // ── Summary ────────────────────────────────────────────────────────

        async loadSummary() {
            this.summaryLoading = true;
            try {
                const r = await fetch('/api/v1/admin/transactions/summary', { headers: this.h() });
                const d = await r.json();
                if (d.success) {
                    this.summary = d.data;
                    this.$nextTick(() => this.drawChart(d.data.daily));
                }
            } catch(e) { console.error(e); }
            finally { this.summaryLoading = false; }
        },

        drawChart(daily) {
            const ctx = document.getElementById('revenueChart');
            if (!ctx || !daily) return;
            if (this.chart) this.chart.destroy();

            const labels = daily.map(d => {
                const dt = new Date(d.date);
                return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
            });
            const values = daily.map(d => d.total);

            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Revenue',
                        data: values,
                        backgroundColor: 'rgba(99,102,241,0.7)',
                        borderColor: 'rgb(99,102,241)',
                        borderWidth: 1,
                        borderRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => '৳ ' + parseFloat(ctx.raw).toLocaleString('en', { minimumFractionDigits: 2 }),
                            },
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: {
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: {
                                font: { size: 10 },
                                callback: v => '৳' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v),
                            },
                        },
                    },
                },
            });
        },

        methodPct(m) {
            const total = (this.summary?.by_method ?? []).reduce((s, x) => s + parseFloat(x.total), 0);
            return total > 0 ? Math.round((parseFloat(m.total) / total) * 100) : 0;
        },

        // ── Ledger ─────────────────────────────────────────────────────────

        async loadLedger() {
            this.ledgerLoading = true;
            try {
                const p = new URLSearchParams({
                    page: this.ledgerPage,
                    ...(this.ledgerSearch && { q: this.ledgerSearch }),
                    ...(this.ledgerType   && { type: this.ledgerType }),
                    ...(this.ledgerFrom   && { from: this.ledgerFrom }),
                    ...(this.ledgerTo     && { to: this.ledgerTo }),
                });
                const r = await fetch(`/api/v1/admin/transactions?${p}`, { headers: this.h() });
                const d = await r.json();
                if (d.success) {
                    this.ledger     = d.data.data;
                    this.ledgerMeta = d.data.meta;
                }
            } catch(e) { console.error(e); }
            finally { this.ledgerLoading = false; }
        },

        // ── Reconciliation ──────────────────────────────────────────────────

        async loadReconciliation() {
            this.reconLoading = true;
            try {
                const p = new URLSearchParams({
                    page: this.reconPage,
                    ...(this.reconIssue  && { issue_type: this.reconIssue }),
                    ...(this.reconMethod && { method: this.reconMethod }),
                });
                const r = await fetch(`/api/v1/admin/transactions/reconciliation?${p}`, { headers: this.h() });
                const d = await r.json();
                if (d.success) {
                    this.reconRows   = d.data.data;
                    this.reconMeta   = d.data.meta;
                    this.reconCounts = d.data.counts;
                }
            } catch(e) { console.error(e); }
            finally { this.reconLoading = false; }
        },

        openReconcileModal(row) {
            this.reconcileModal.row       = row;
            this.reconcileModal.status    = row.payment_status === 'failed' ? 'paid' : 'paid';
            this.reconcileModal.gatewayRef= row.gateway_transaction_id ?? '';
            this.reconcileModal.note      = '';
            this.reconcileModal.open      = true;
        },

        async applyReconcile() {
            this.reconcileModal.saving = true;
            try {
                const r = await fetch(`/api/v1/admin/transactions/order/${this.reconcileModal.row.id}/payment-status`, {
                    method: 'PATCH',
                    headers: { ...this.h(), 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        payment_status:         this.reconcileModal.status,
                        gateway_transaction_id: this.reconcileModal.gatewayRef || null,
                        note:                   this.reconcileModal.note || null,
                    }),
                });
                const d = await r.json();
                if (d.success) {
                    this.reconcileModal.open = false;
                    this.reconPage = 1;
                    await Promise.all([this.loadReconciliation(), this.loadSummary()]);
                    this.toast('Payment status updated', 'success');
                } else {
                    this.toast(d.message || 'Failed', 'error');
                }
            } catch(e) { this.toast('Network error', 'error'); }
            finally { this.reconcileModal.saving = false; }
        },

        // ── Order Side Panel ────────────────────────────────────────────────

        async openOrderPanel(orderId) {
            this.panel.open       = true;
            this.panel.loading    = true;
            this.panel.orderId    = orderId;
            this.panel.transactions = [];
            this.panel.txError    = null;
            this.panel.txForm     = { type: 'refund', amount: '', description: '' };
            try {
                const r = await fetch(`/api/v1/admin/transactions/order/${orderId}`, { headers: this.h() });
                const d = await r.json();
                if (d.success) {
                    this.panel.order                 = d.data.order;
                    this.panel.transactions          = d.data.transactions;
                    this.panel.txSummary             = d.data.summary;
                    this.panel.reconciliation_status = d.data.reconciliation_status;
                    this.panel.newStatus             = d.data.order.payment_status;
                    this.panel.gatewayRef            = d.data.order.gateway_transaction_id ?? '';
                }
            } catch(e) { console.error(e); }
            finally { this.panel.loading = false; }
        },

        async updatePaymentStatus() {
            this.panel.updating = true;
            try {
                const r = await fetch(`/api/v1/admin/transactions/order/${this.panel.orderId}/payment-status`, {
                    method: 'PATCH',
                    headers: { ...this.h(), 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        payment_status:         this.panel.newStatus,
                        gateway_transaction_id: this.panel.gatewayRef || null,
                    }),
                });
                const d = await r.json();
                if (d.success) {
                    this.panel.order.payment_status         = d.data.payment_status;
                    this.panel.order.gateway_transaction_id = d.data.gateway_transaction_id;
                    await this.openOrderPanel(this.panel.orderId); // refresh
                    this.toast('Payment status updated', 'success');
                    await this.loadSummary();
                } else {
                    this.toast(d.message || 'Update failed', 'error');
                }
            } catch(e) { this.toast('Network error', 'error'); }
            finally { this.panel.updating = false; }
        },

        async addTransaction() {
            if (!this.panel.txForm.amount || !this.panel.txForm.description) return;
            this.panel.addingTx = true;
            this.panel.txError  = null;
            try {
                const r = await fetch(`/api/v1/admin/transactions/order/${this.panel.orderId}`, {
                    method: 'POST',
                    headers: { ...this.h(), 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.panel.txForm),
                });
                const d = await r.json();
                if (r.ok && d.success) {
                    await this.openOrderPanel(this.panel.orderId); // full refresh
                    this.toast('Transaction recorded', 'success');
                    await this.loadSummary();
                } else {
                    this.panel.txError = d.message || (d.errors ? Object.values(d.errors).flat().join(', ') : 'Failed');
                }
            } catch(e) { this.panel.txError = 'Network error'; }
            finally { this.panel.addingTx = false; }
        },

        // ── Helpers ─────────────────────────────────────────────────────────

        h() {
            return {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            };
        },

        fmt(v) {
            if (v === undefined || v === null) return '৳0.00';
            return '৳' + parseFloat(v).toLocaleString('en', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        fmtDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        typeBadge(type) {
            const map = {
                charge:     'bg-green-100 text-green-700',
                refund:     'bg-red-100 text-red-700',
                discount:   'bg-amber-100 text-amber-700',
                coupon:     'bg-purple-100 text-purple-700',
                shipping:   'bg-blue-100 text-blue-700',
                commission: 'bg-gray-100 text-gray-700',
            };
            return map[type] ?? 'bg-gray-100 text-gray-700';
        },

        typeColor(type) {
            const map = {
                charge: 'bg-green-500', refund: 'bg-red-500', discount: 'bg-amber-500',
                coupon: 'bg-purple-500', shipping: 'bg-blue-500', commission: 'bg-gray-400',
            };
            return map[type] ?? 'bg-gray-400';
        },

        paymentStatusBadge(s) {
            return s === 'paid' ? 'bg-green-100 text-green-700'
                 : s === 'failed' ? 'bg-red-100 text-red-700'
                 : 'bg-amber-100 text-amber-700';
        },

        issueBadge(type) {
            const map = {
                missing_charge:     'bg-red-100 text-red-700',
                underpaid:          'bg-orange-100 text-orange-700',
                unrecorded_payment: 'bg-amber-100 text-amber-700',
                payment_failed:     'bg-red-100 text-red-700',
            };
            return map[type] ?? 'bg-gray-100 text-gray-700';
        },

        issueActiveClass(key) {
            const map = {
                missing_charge:     'bg-red-600 text-white',
                underpaid:          'bg-orange-600 text-white',
                unrecorded_payment: 'bg-amber-600 text-white',
                payment_failed:     'bg-red-600 text-white',
            };
            return map[key] ?? 'bg-gray-600 text-white';
        },

        issueLabel(type) {
            const map = {
                missing_charge:     'Missing Charge',
                underpaid:          'Underpaid',
                unrecorded_payment: 'Unrecorded Payment',
                payment_failed:     'Payment Failed',
                ok:                 'Reconciled',
            };
            return map[type] ?? type;
        },

        toast(msg, type = 'success') {
            const d = document.createElement('div');
            d.className = `fixed bottom-5 right-5 z-[9999] px-4 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2
                ${type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`;
            d.innerHTML = `<i class="fas ${type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${msg}`;
            document.body.appendChild(d);
            setTimeout(() => d.remove(), 3500);
        },
    };
}
</script>
@endpush
