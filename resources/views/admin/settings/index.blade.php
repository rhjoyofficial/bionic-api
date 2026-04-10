@extends('layouts.admin')

@section('title', 'Settings & System Health')

@section('content')
<div x-data="settingsApp()" x-init="init()" x-cloak>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Settings & System Health</h1>
            <p class="text-sm text-gray-500 mt-0.5">Configure application settings and monitor system status</p>
        </div>
    </div>

    {{-- Tab Nav --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex gap-6">
            <button @click="tab = 'settings'"
                    :class="tab === 'settings' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-1.5 pb-3 text-sm font-medium border-b-2 transition">
                <i class="fas fa-sliders text-xs"></i> App Settings
            </button>
            <button @click="tab = 'health'; loadHealth()"
                    :class="tab === 'health' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-1.5 pb-3 text-sm font-medium border-b-2 transition">
                <i class="fas fa-heart-pulse text-xs"></i> System Health
            </button>
        </nav>
    </div>

    {{-- ============================================================ --}}
    {{-- TAB: APP SETTINGS                                            --}}
    {{-- ============================================================ --}}
    <div x-show="tab === 'settings'">

        {{-- Loading skeleton --}}
        <div x-show="loadingSettings" class="space-y-4">
            <template x-for="i in 4">
                <div class="bg-white rounded-xl border border-gray-200 p-6 animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-32 mb-4"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="h-10 bg-gray-100 rounded"></div>
                        <div class="h-10 bg-gray-100 rounded"></div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Settings Groups --}}
        <div x-show="!loadingSettings" class="space-y-6">

            {{-- Save All Bar --}}
            <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-5 py-3">
                <p class="text-sm text-green-800 font-medium">
                    <i class="fas fa-circle-info mr-1"></i>
                    Changes are saved per group. Mail settings are managed via <code>.env</code>.
                </p>
                <button @click="saveAll()"
                        :disabled="saving"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 transition">
                    <i class="fas fa-floppy-disk"></i>
                    <span x-text="saving ? 'Saving…' : 'Save All'"></span>
                </button>
            </div>

            <template x-for="(group, groupKey) in groups" :key="groupKey">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    {{-- Group Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <div class="flex items-center gap-2">
                            <i class="fas text-green-600 text-sm" :class="groupIcons[groupKey] || 'fa-cog'"></i>
                            <h3 class="text-sm font-semibold text-gray-800 capitalize" x-text="groupLabels[groupKey] || groupKey"></h3>
                            <template x-if="group[0]?.is_readonly">
                                <span class="ml-2 px-2 py-0.5 bg-gray-200 text-gray-500 text-xs rounded-full">Read-only</span>
                            </template>
                        </div>
                        <template x-if="!group[0]?.is_readonly">
                            <button @click="saveGroup(groupKey)"
                                    :disabled="saving"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-green-600 text-green-600 text-xs font-medium rounded-lg hover:bg-green-50 disabled:opacity-50 transition">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </template>
                    </div>
                    {{-- Group Fields --}}
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <template x-for="setting in group" :key="setting.key">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5" x-text="setting.label"></label>
                                {{-- Boolean toggle --}}
                                <template x-if="setting.type === 'boolean'">
                                    <div class="flex items-center gap-3">
                                        <button @click="if (!setting.is_readonly) setting.value = setting.value == '1' ? '0' : '1'"
                                                :class="setting.value == '1' ? 'bg-green-600' : 'bg-gray-300'"
                                                :disabled="setting.is_readonly"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition disabled:opacity-60 disabled:cursor-not-allowed">
                                            <span :class="setting.value == '1' ? 'translate-x-6' : 'translate-x-1'"
                                                  class="inline-block h-4 w-4 transform rounded-full bg-white transition"></span>
                                        </button>
                                        <span class="text-sm text-gray-600" x-text="setting.value == '1' ? 'Enabled' : 'Disabled'"></span>
                                    </div>
                                </template>
                                {{-- Integer input --}}
                                <template x-if="setting.type === 'integer'">
                                    <input type="number"
                                           x-model="setting.value"
                                           :disabled="setting.is_readonly"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
                                </template>
                                {{-- Text area --}}
                                <template x-if="setting.type === 'text'">
                                    <textarea x-model="setting.value"
                                              :disabled="setting.is_readonly"
                                              rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed"></textarea>
                                </template>
                                {{-- Default: string input --}}
                                <template x-if="setting.type === 'string'">
                                    <input type="text"
                                           x-model="setting.value"
                                           :disabled="setting.is_readonly"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
                                </template>
                                <template x-if="setting.description">
                                    <p class="mt-1 text-xs text-gray-400" x-text="setting.description"></p>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TAB: SYSTEM HEALTH                                           --}}
    {{-- ============================================================ --}}
    <div x-show="tab === 'health'">

        {{-- Action Bar --}}
        <div class="flex flex-wrap items-center gap-3 mb-6">
            <button @click="loadHealth()"
                    :disabled="loadingHealth"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 disabled:opacity-50 transition">
                <i class="fas fa-arrows-rotate" :class="loadingHealth ? 'animate-spin' : ''"></i>
                Refresh
            </button>

            <button @click="clearCache()"
                    :disabled="actionLoading"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 disabled:opacity-50 transition">
                <i class="fas fa-broom"></i>
                <span x-text="actionLoading === 'cache' ? 'Clearing…' : 'Clear Cache'"></span>
            </button>

            <button @click="optimizeApp()"
                    :disabled="actionLoading"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 disabled:opacity-50 transition">
                <i class="fas fa-bolt"></i>
                <span x-text="actionLoading === 'optimize' ? 'Optimizing…' : 'Optimize'"></span>
            </button>

            <div class="ml-auto flex items-center gap-2 text-xs text-gray-400">
                <i class="fas fa-clock"></i>
                <span x-text="lastRefreshed ? 'Last refreshed: ' + lastRefreshed : 'Not yet loaded'"></span>
            </div>
        </div>

        {{-- Overall Status Banner --}}
        <template x-if="health">
            <div :class="{
                    'bg-green-50 border-green-200 text-green-800': health.overall_status === 'ok',
                    'bg-amber-50 border-amber-200 text-amber-800': health.overall_status === 'warning',
                    'bg-red-50 border-red-200 text-red-800':    health.overall_status === 'error'
                 }"
                 class="flex items-center gap-3 border rounded-xl px-5 py-4 mb-6">
                <i class="fas text-lg"
                   :class="{
                       'fa-circle-check':      health.overall_status === 'ok',
                       'fa-triangle-exclamation': health.overall_status === 'warning',
                       'fa-circle-xmark':      health.overall_status === 'error'
                   }"></i>
                <div>
                    <p class="font-semibold text-sm capitalize" x-text="'System Status: ' + health.overall_status.toUpperCase()"></p>
                    <p class="text-xs mt-0.5 opacity-75"
                       x-text="health.overall_status === 'ok' ? 'All systems operating normally.' : (health.overall_status === 'warning' ? 'Some checks require attention.' : 'Critical issues detected — immediate action required.')">
                    </p>
                </div>
            </div>
        </template>

        {{-- Health Check Cards --}}
        <div x-show="loadingHealth && !health" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <template x-for="i in 8">
                <div class="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-24 mb-3"></div>
                    <div class="h-3 bg-gray-100 rounded w-full mb-2"></div>
                    <div class="h-3 bg-gray-100 rounded w-3/4"></div>
                </div>
            </template>
        </div>

        <template x-if="health">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <template x-for="(check, key) in health.checks" :key="key">
                    <div :class="{
                             'border-green-200 bg-green-50':  check.status === 'ok',
                             'border-amber-200 bg-amber-50':  check.status === 'warning',
                             'border-red-200   bg-red-50':    check.status === 'error',
                             'border-blue-200  bg-blue-50':   check.status === 'info'
                         }"
                         class="rounded-xl border p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <i class="fas text-sm"
                                   :class="{
                                       'fa-circle-check text-green-600':   check.status === 'ok',
                                       'fa-triangle-exclamation text-amber-600': check.status === 'warning',
                                       'fa-circle-xmark text-red-600':     check.status === 'error',
                                       'fa-circle-info text-blue-600':     check.status === 'info'
                                   }"></i>
                                <h4 class="text-sm font-semibold text-gray-800 capitalize" x-text="key.replace('_', ' ')"></h4>
                            </div>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize"
                                  :class="{
                                      'bg-green-100 text-green-700': check.status === 'ok',
                                      'bg-amber-100 text-amber-700': check.status === 'warning',
                                      'bg-red-100 text-red-700':     check.status === 'error',
                                      'bg-blue-100 text-blue-700':   check.status === 'info'
                                  }"
                                  x-text="check.status"></span>
                        </div>
                        <dl class="space-y-1.5">
                            <template x-for="(val, label) in check" :key="label">
                                <template x-if="label !== 'status'">
                                    <div class="flex justify-between text-xs">
                                        <dt class="text-gray-500 capitalize" x-text="label.replace(/_/g, ' ')"></dt>
                                        <dd class="font-medium text-gray-800 truncate max-w-[60%] text-right" x-text="val ?? '—'"></dd>
                                    </div>
                                </template>
                            </template>
                        </dl>
                    </div>
                </template>
            </div>
        </template>

        {{-- Maintenance Mode Card --}}
        <template x-if="health">
            <div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                    <i class="fas fa-hard-hat text-amber-500 text-sm"></i>
                    <h3 class="text-sm font-semibold text-gray-800">Maintenance Mode</h3>
                </div>
                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex-1">
                        <p class="text-sm text-gray-700 font-medium"
                           x-text="health.checks.app?.maintenance ? 'App is currently in MAINTENANCE MODE' : 'App is live and accepting requests'"></p>
                        <p class="text-xs text-gray-400 mt-1">
                            Enabling maintenance mode will return a 503 response to all visitors.
                            A secret bypass token is set in your General settings.
                        </p>
                    </div>
                    <button @click="toggleMaintenance()"
                            :disabled="actionLoading === 'maintenance'"
                            :class="health.checks.app?.maintenance
                                ? 'bg-green-600 hover:bg-green-700 text-white'
                                : 'bg-amber-500 hover:bg-amber-600 text-white'"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium rounded-lg disabled:opacity-50 transition whitespace-nowrap">
                        <i class="fas"
                           :class="health.checks.app?.maintenance ? 'fa-play' : 'fa-pause'"></i>
                        <span x-text="actionLoading === 'maintenance' ? 'Updating…' : (health.checks.app?.maintenance ? 'Bring Back Online' : 'Enable Maintenance Mode')"></span>
                    </button>
                </div>
                <div x-show="health.checks.app?.maintenance"
                     class="mx-6 mb-5 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                    <i class="fas fa-triangle-exclamation mr-1"></i>
                    <strong>Maintenance mode is active.</strong>
                    Use your bypass secret token (set in General settings) to access the app.
                </div>
            </div>
        </template>

    </div><!-- /health tab -->

</div><!-- /x-data -->
@endsection

@push('scripts')
<script>
function settingsApp() {
    return {
        tab: 'settings',

        // Settings tab
        loadingSettings: true,
        saving: false,
        groups: {},

        groupLabels: {
            general:   'General',
            contact:   'Contact',
            business:  'Business',
            mail_info: 'Mail / SMTP (read-only)',
        },
        groupIcons: {
            general:   'fa-house',
            contact:   'fa-address-book',
            business:  'fa-briefcase',
            mail_info: 'fa-envelope',
        },

        // Health tab
        loadingHealth: false,
        health: null,
        lastRefreshed: null,
        actionLoading: null,
        refreshTimer: null,

        init() {
            this.loadSettings();
        },

        csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.content;
        },

        // ─── Settings ────────────────────────────────────────────
        async loadSettings() {
            this.loadingSettings = true;
            try {
                const res = await fetch('/admin/api/settings', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }
                });
                const json = await res.json();
                if (json.success) this.groups = json.data;
            } catch (e) {
                console.error('Failed to load settings', e);
            } finally {
                this.loadingSettings = false;
            }
        },

        buildPayload(groupKey) {
            return (this.groups[groupKey] || [])
                .filter(s => !s.is_readonly)
                .map(s => ({ key: s.key, value: s.value }));
        },

        async saveGroup(groupKey) {
            this.saving = true;
            try {
                const payload = this.buildPayload(groupKey);
                const res = await fetch('/admin/api/settings', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf()
                    },
                    body: JSON.stringify({ settings: payload })
                });
                const json = await res.json();
                if (json.success) {
                    this.$dispatch('flash', { type: 'success', message: (this.groupLabels[groupKey] || groupKey) + ' settings saved.' });
                } else {
                    this.$dispatch('flash', { type: 'error', message: json.message || 'Failed to save.' });
                }
            } catch (e) {
                this.$dispatch('flash', { type: 'error', message: 'Network error.' });
            } finally {
                this.saving = false;
            }
        },

        async saveAll() {
            this.saving = true;
            const payload = Object.keys(this.groups).flatMap(k => this.buildPayload(k));
            try {
                const res = await fetch('/admin/api/settings', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf()
                    },
                    body: JSON.stringify({ settings: payload })
                });
                const json = await res.json();
                if (json.success) {
                    this.$dispatch('flash', { type: 'success', message: 'All settings saved successfully.' });
                } else {
                    this.$dispatch('flash', { type: 'error', message: json.message || 'Failed to save.' });
                }
            } catch (e) {
                this.$dispatch('flash', { type: 'error', message: 'Network error.' });
            } finally {
                this.saving = false;
            }
        },

        // ─── Health ──────────────────────────────────────────────
        async loadHealth() {
            this.loadingHealth = true;
            try {
                const res = await fetch('/admin/api/settings/health', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }
                });
                const json = await res.json();
                if (json.success) {
                    this.health = json.data;
                    this.lastRefreshed = new Date().toLocaleTimeString();
                }
            } catch (e) {
                console.error('Health check failed', e);
            } finally {
                this.loadingHealth = false;
            }
        },

        async clearCache() {
            this.actionLoading = 'cache';
            try {
                const res = await fetch('/admin/api/settings/clear-cache', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }
                });
                const json = await res.json();
                this.$dispatch('flash', {
                    type: json.success ? 'success' : 'error',
                    message: json.message || (json.success ? 'Cache cleared.' : 'Failed.')
                });
                if (json.success) this.loadHealth();
            } catch (e) {
                this.$dispatch('flash', { type: 'error', message: 'Network error.' });
            } finally {
                this.actionLoading = null;
            }
        },

        async optimizeApp() {
            this.actionLoading = 'optimize';
            try {
                const res = await fetch('/admin/api/settings/optimize', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }
                });
                const json = await res.json();
                this.$dispatch('flash', {
                    type: json.success ? 'success' : 'error',
                    message: json.message || (json.success ? 'App optimized.' : 'Failed.')
                });
            } catch (e) {
                this.$dispatch('flash', { type: 'error', message: 'Network error.' });
            } finally {
                this.actionLoading = null;
            }
        },

        async toggleMaintenance() {
            this.actionLoading = 'maintenance';
            try {
                const res = await fetch('/admin/api/settings/toggle-maintenance', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }
                });
                const json = await res.json();
                this.$dispatch('flash', {
                    type: json.success ? 'success' : 'error',
                    message: json.message || (json.success ? 'Maintenance mode updated.' : 'Failed.')
                });
                if (json.success) this.loadHealth();
            } catch (e) {
                this.$dispatch('flash', { type: 'error', message: 'Network error.' });
            } finally {
                this.actionLoading = null;
            }
        },
    };
}
</script>
@endpush
