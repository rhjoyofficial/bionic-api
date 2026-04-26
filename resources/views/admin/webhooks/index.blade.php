@extends('layouts.admin')

@section('title', 'Webhooks')

@section('content')
<div x-data="webhooksApp()" x-init="init()" x-cloak>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Webhooks</h1>
            <p class="text-sm text-gray-500 mt-0.5">Register external URLs to receive real-time event notifications</p>
        </div>
        <button @click="openCreateModal()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition cursor-pointer">
            <i class="fas fa-plus"></i> Add Webhook
        </button>
    </div>

    {{-- Event Reference Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mb-6">
        <template x-for="ev in allowedEvents" :key="ev">
            <div class="bg-white border border-gray-200 rounded-xl px-3 py-2.5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full shrink-0"
                    :class="webhooks.some(w => w.event === ev && w.is_active) ? 'bg-green-500' : 'bg-gray-300'"></span>
                <span class="text-xs font-mono text-gray-700 truncate" x-text="ev"></span>
            </div>
        </template>
    </div>

    {{-- Loading skeleton --}}
    <div x-show="loading" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        <template x-for="i in 3" :key="i">
            <div class="px-5 py-4 flex items-center gap-4 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-40"></div>
                <div class="h-4 bg-gray-100 rounded flex-1"></div>
                <div class="h-6 bg-gray-100 rounded w-16"></div>
            </div>
        </template>
    </div>

    {{-- Webhooks Table --}}
    <div x-show="!loading" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-100" x-show="webhooks.length > 0">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Event</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">URL</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Secret</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Registered</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <template x-for="wh in webhooks" :key="wh.id">
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-mono font-medium"
                                :class="eventColors[wh.event] ?? 'bg-gray-100 text-gray-700'"
                                x-text="wh.event"></span>
                        </td>
                        <td class="px-5 py-3.5 max-w-xs">
                            <span class="text-sm text-gray-700 truncate block" x-text="wh.url" :title="wh.url"></span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="text-xs font-mono text-gray-400" x-text="wh.secret_hint ?? '—'"></span>
                        </td>
                        <td class="px-5 py-3.5">
                            <button @click="toggleActive(wh)"
                                :class="wh.is_active
                                    ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                    : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition cursor-pointer">
                                <span class="w-1.5 h-1.5 rounded-full"
                                    :class="wh.is_active ? 'bg-green-500' : 'bg-gray-400'"></span>
                                <span x-text="wh.is_active ? 'Active' : 'Inactive'"></span>
                            </button>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-400"
                            x-text="wh.created_at ? fmtDate(wh.created_at) : '—'"></td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="testWebhook(wh)"
                                    :disabled="!wh.is_active || testing[wh.id]"
                                    :class="(!wh.is_active || testing[wh.id]) ? 'opacity-40 cursor-not-allowed' : 'hover:text-indigo-600 hover:bg-indigo-50 cursor-pointer'"
                                    class="p-1.5 text-gray-400 rounded transition"
                                    title="Send test ping">
                                    <i class="fas text-xs" :class="testing[wh.id] ? 'fa-circle-notch fa-spin' : 'fa-paper-plane'"></i>
                                </button>
                                <button @click="confirmDelete(wh)"
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition cursor-pointer"
                                    title="Delete">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        {{-- Empty state --}}
        <div x-show="webhooks.length === 0"
            class="flex flex-col items-center justify-center py-20 text-center px-6">
            <div class="w-14 h-14 rounded-full bg-indigo-50 flex items-center justify-center mb-4">
                <i class="fas fa-link-slash text-2xl text-indigo-300"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-700 mb-1">No webhooks registered</h3>
            <p class="text-xs text-gray-400 max-w-sm mb-5">
                Register an external URL to start receiving real-time event notifications from your store.
            </p>
            <button @click="openCreateModal()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition cursor-pointer">
                <i class="fas fa-plus"></i> Add Your First Webhook
            </button>
        </div>
    </div>

    {{-- How-It-Works Info Box --}}
    <div class="mt-6 bg-indigo-50 border border-indigo-100 rounded-xl p-5">
        <h4 class="text-sm font-semibold text-indigo-800 mb-3 flex items-center gap-2">
            <i class="fas fa-circle-info text-indigo-500"></i> How webhooks work
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-indigo-700">
            <div class="flex gap-2">
                <i class="fas fa-lock mt-0.5 shrink-0 text-indigo-400"></i>
                <div>
                    <p class="font-semibold mb-0.5">HMAC Signature</p>
                    <p class="text-indigo-500">Every request includes an <code class="bg-indigo-100 px-1 rounded">X-Bionic-Signature</code> header — an HMAC-SHA256 of the JSON body signed with your secret. Verify it on your end to confirm authenticity.</p>
                </div>
            </div>
            <div class="flex gap-2">
                <i class="fas fa-rotate mt-0.5 shrink-0 text-indigo-400"></i>
                <div>
                    <p class="font-semibold mb-0.5">Automatic Retries</p>
                    <p class="text-indigo-500">Failed deliveries are retried up to 3 times with exponential backoff (30s → 120s → 300s) via the queue system.</p>
                </div>
            </div>
            <div class="flex gap-2">
                <i class="fas fa-bolt mt-0.5 shrink-0 text-indigo-400"></i>
                <div>
                    <p class="font-semibold mb-0.5">Async Delivery</p>
                    <p class="text-indigo-500">Webhooks are dispatched on a background queue — they never delay your store's HTTP responses regardless of your receiver's speed.</p>
                </div>
            </div>
        </div>

        <div class="mt-4 border-t border-indigo-100 pt-4">
            <p class="text-xs font-semibold text-indigo-800 mb-2">Payload structure</p>
            <pre class="bg-white border border-indigo-100 rounded-lg p-3 text-xs text-gray-700 overflow-x-auto"><code>{
  "event": "order.created",
  "data": { ... event-specific fields ... }
}</code></pre>
        </div>
    </div>

    {{-- ─── Add Webhook Modal ───────────────────────────────────────────── --}}
    <div x-show="createModal.open" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div @click.outside="createModal.open = false"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">

            <h3 class="text-base font-semibold text-gray-900 mb-1">Register Webhook</h3>
            <p class="text-xs text-gray-500 mb-5">Your URL will receive a signed POST request when the selected event fires.</p>

            <div class="space-y-4">
                {{-- Event --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Event <span class="text-red-500">*</span>
                    </label>
                    <select x-model="createModal.event"
                        class="w-full text-sm border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none font-mono"
                        :class="createModal.errors.event ? 'border-red-400' : 'border-gray-200'">
                        <option value="">— choose an event —</option>
                        <template x-for="ev in allowedEvents" :key="ev">
                            <option :value="ev" x-text="ev"></option>
                        </template>
                    </select>
                    <p x-show="createModal.errors.event" class="text-xs text-red-500 mt-1"
                        x-text="createModal.errors.event?.[0]"></p>
                </div>

                {{-- URL --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Endpoint URL <span class="text-red-500">*</span>
                    </label>
                    <input x-model="createModal.url" type="url"
                        placeholder="https://your-app.com/webhooks/bionic"
                        class="w-full text-sm border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none"
                        :class="createModal.errors.url ? 'border-red-400' : 'border-gray-200'">
                    <p x-show="createModal.errors.url" class="text-xs text-red-500 mt-1"
                        x-text="createModal.errors.url?.[0]"></p>
                </div>

                {{-- Secret --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-gray-700">
                            Signing Secret <span class="text-red-500">*</span>
                        </label>
                        <button type="button" @click="createModal.secret = generateSecret()"
                            class="text-xs text-indigo-600 hover:text-indigo-800 transition cursor-pointer">
                            <i class="fas fa-wand-magic-sparkles mr-1"></i>Generate
                        </button>
                    </div>
                    <div class="relative">
                        <input x-model="createModal.secret"
                            :type="createModal.showSecret ? 'text' : 'password'"
                            placeholder="Min. 16 characters"
                            class="w-full text-sm border rounded-lg px-3 py-2 pr-10 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none font-mono"
                            :class="createModal.errors.secret ? 'border-red-400' : 'border-gray-200'">
                        <button type="button"
                            @click="createModal.showSecret = !createModal.showSecret"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer">
                            <i class="fas text-xs" :class="createModal.showSecret ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    <p x-show="createModal.errors.secret" class="text-xs text-red-500 mt-1"
                        x-text="createModal.errors.secret?.[0]"></p>
                    <p class="text-xs text-gray-400 mt-1">
                        Used to sign each request. Store it securely — it won't be shown again after saving.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button @click="createModal.open = false"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                    Cancel
                </button>
                <button @click="createWebhook()" :disabled="createModal.loading"
                    class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition cursor-pointer">
                    <i class="fas mr-1"
                        :class="createModal.loading ? 'fa-circle-notch fa-spin' : 'fa-plus'"></i>
                    <span x-text="createModal.loading ? 'Registering…' : 'Register Webhook'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ─── Delete Confirmation Modal ───────────────────────────────────── --}}
    <div x-show="deleteModal.open" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div @click.outside="deleteModal.open = false"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-start gap-3 mb-5">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <i class="fas fa-trash text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Delete Webhook?</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        The endpoint
                        <span class="font-mono text-xs bg-gray-100 px-1 rounded"
                            x-text="deleteModal.webhook?.url"></span>
                        will stop receiving <strong x-text="deleteModal.webhook?.event"></strong> events.
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button @click="deleteModal.open = false"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                    Cancel
                </button>
                <button @click="deleteWebhook()" :disabled="deleteModal.loading"
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
function webhooksApp() {
    return {
        webhooks:      [],
        allowedEvents: [],
        loading:       true,
        testing:       {},

        createModal: {
            open: false, event: '', url: '', secret: '',
            showSecret: false, loading: false, errors: {}
        },
        deleteModal: { open: false, webhook: null, loading: false },

        eventColors: {
            'order.created':          'bg-purple-100 text-purple-700',
            'order.status_changed':   'bg-indigo-100 text-indigo-700',
            'order.payment_updated':  'bg-blue-100 text-blue-700',
            'coupon.expired':         'bg-pink-100 text-pink-700',
            'customer.registered':    'bg-teal-100 text-teal-700',
            'shipment.status_updated':'bg-orange-100 text-orange-700',
        },

        async init() {
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                const r = await fetch('/api/v1/admin/webhooks', { headers: this.h() });
                const d = await r.json();
                if (d.success) {
                    this.webhooks      = d.data.webhooks;
                    this.allowedEvents = d.data.allowed_events;
                }
            } catch (e) {
                console.error('Failed to load webhooks', e);
            } finally {
                this.loading = false;
            }
        },

        // ── Create ────────────────────────────────────────────────────────────
        openCreateModal() {
            this.createModal = {
                open: true, event: '', url: '', secret: '',
                showSecret: false, loading: false, errors: {}
            };
        },

        async createWebhook() {
            this.createModal.loading = true;
            this.createModal.errors  = {};
            try {
                const r = await fetch('/api/v1/admin/webhooks', {
                    method: 'POST',
                    headers: { ...this.h(), 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        event:  this.createModal.event,
                        url:    this.createModal.url,
                        secret: this.createModal.secret,
                    }),
                });
                const d = await r.json();
                if (r.ok && d.success) {
                    this.webhooks.push(d.data);
                    this.createModal.open = false;
                    this.toast('Webhook registered successfully', 'success');
                } else {
                    this.createModal.errors = d.errors ?? {};
                    if (!Object.keys(this.createModal.errors).length) {
                        this.toast(d.message || 'Failed to register webhook', 'error');
                    }
                }
            } catch (e) {
                this.toast('Network error', 'error');
            } finally {
                this.createModal.loading = false;
            }
        },

        // ── Toggle active ─────────────────────────────────────────────────────
        async toggleActive(webhook) {
            try {
                const r = await fetch(`/api/v1/admin/webhooks/${webhook.id}/toggle`, {
                    method: 'PATCH',
                    headers: this.h(),
                });
                const d = await r.json();
                if (r.ok && d.success) {
                    const idx = this.webhooks.findIndex(w => w.id === webhook.id);
                    if (idx > -1) this.webhooks[idx].is_active = d.data.is_active;
                    this.toast(d.message, 'success');
                } else {
                    this.toast(d.message || 'Failed to toggle webhook', 'error');
                }
            } catch (e) {
                this.toast('Network error', 'error');
            }
        },

        // ── Test ──────────────────────────────────────────────────────────────
        async testWebhook(webhook) {
            this.testing[webhook.id] = true;
            try {
                const r = await fetch(`/api/v1/admin/webhooks/${webhook.id}/test`, {
                    method: 'POST',
                    headers: this.h(),
                });
                const d = await r.json();
                if (r.ok && d.success) {
                    this.toast('Test ping queued — check your endpoint', 'success');
                } else {
                    this.toast(d.message || 'Test failed', 'error');
                }
            } catch (e) {
                this.toast('Network error', 'error');
            } finally {
                this.testing[webhook.id] = false;
            }
        },

        // ── Delete ────────────────────────────────────────────────────────────
        confirmDelete(webhook) {
            this.deleteModal = { open: true, webhook, loading: false };
        },

        async deleteWebhook() {
            this.deleteModal.loading = true;
            try {
                const r = await fetch(`/api/v1/admin/webhooks/${this.deleteModal.webhook.id}`, {
                    method: 'DELETE',
                    headers: this.h(),
                });
                const d = await r.json();
                if (r.ok && d.success) {
                    this.webhooks = this.webhooks.filter(w => w.id !== this.deleteModal.webhook.id);
                    this.deleteModal.open = false;
                    this.toast('Webhook deleted', 'success');
                } else {
                    this.deleteModal.open = false;
                    this.toast(d.message || 'Failed to delete webhook', 'error');
                }
            } catch (e) {
                this.toast('Network error', 'error');
            } finally {
                this.deleteModal.loading = false;
            }
        },

        // ── Helpers ───────────────────────────────────────────────────────────
        generateSecret() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            return Array.from(crypto.getRandomValues(new Uint8Array(32)))
                .map(b => chars[b % chars.length])
                .join('');
        },

        h() {
            return {
                'Accept':       'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            };
        },

        fmtDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString('en-GB', {
                day: '2-digit', month: 'short', year: 'numeric'
            });
        },

        toast(msg, type = 'success') {
            const el = document.createElement('div');
            el.className = `fixed bottom-5 right-5 z-[9999] px-4 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2 ${type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`;
            el.innerHTML = `<i class="fas ${type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${msg}`;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3500);
        },
    };
}
</script>
@endpush
