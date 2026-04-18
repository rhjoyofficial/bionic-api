@extends('layouts.admin')

@section('title', 'Hero Banners')

@section('content')
<div x-data="heroBannersApp()" x-init="init()" x-cloak>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Hero Banners</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage homepage hero section banners</p>
        </div>
        <div class="flex items-center gap-2">
            @can('hero.create')
            <button @click="openCreate()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition cursor-pointer">
                <i class="fas fa-plus text-xs"></i> Add Banner
            </button>
            @endcan
        </div>
    </div>

    {{-- Success/Error Flash --}}
    <div x-show="flashMsg" x-cloak class="mb-4 rounded-xl border px-5 py-3 text-sm"
         :class="flashType === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-700'">
        <div class="flex items-center justify-between">
            <span x-text="flashMsg"></span>
            <button @click="flashMsg = null" class="text-xs opacity-60 hover:opacity-100">&times;</button>
        </div>
    </div>

    {{-- Loading Skeleton --}}
    <div x-show="loading" class="space-y-3">
        <template x-for="i in 3">
            <div class="bg-white rounded-xl border border-gray-200 p-4 animate-pulse flex gap-4">
                <div class="w-24 h-16 bg-gray-200 rounded-lg shrink-0"></div>
                <div class="flex-1 space-y-2 py-1">
                    <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                    <div class="h-3 bg-gray-100 rounded w-1/2"></div>
                </div>
            </div>
        </template>
    </div>

    {{-- Banners Table --}}
    <div x-show="!loading" class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Image</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Banner</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Schedule</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="banner in banners" :key="banner.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="w-24 h-16 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center shrink-0">
                                    <template x-if="banner.image_url">
                                        <img :src="banner.image_url" class="w-full h-full object-cover" :alt="banner.title">
                                    </template>
                                    <template x-if="!banner.image_url">
                                        <i class="fas fa-image text-gray-300 text-xl"></i>
                                    </template>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <template x-if="banner.badge">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 mb-1 mr-1" x-text="banner.badge"></span>
                                </template>
                                <p class="font-semibold text-gray-800" x-text="banner.title"></p>
                                <p class="text-xs text-gray-400 mt-0.5 line-clamp-1" x-text="banner.subtitle || banner.description || '—'"></p>
                            </td>
                            <td class="px-4 py-3">
                                <button @click="toggleActive(banner)"
                                    :class="banner.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition cursor-pointer hover:opacity-80">
                                    <i class="fas" :class="banner.is_active ? 'fa-circle-check' : 'fa-circle-xmark'"></i>
                                    <span x-text="banner.is_active ? 'Active' : 'Inactive'"></span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono" x-text="'#' + banner.sort_order"></td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                <template x-if="banner.starts_at || banner.ends_at">
                                    <div>
                                        <span x-show="banner.starts_at" x-text="'From: ' + (banner.starts_at ? new Date(banner.starts_at).toLocaleDateString('en-GB') : '')"></span>
                                        <span x-show="banner.ends_at" x-text="'To: ' + (banner.ends_at ? new Date(banner.ends_at).toLocaleDateString('en-GB') : '')"></span>
                                    </div>
                                </template>
                                <template x-if="!banner.starts_at && !banner.ends_at">
                                    <span>Always</span>
                                </template>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @can('hero.update')
                                    <button @click="openEdit(banner)"
                                        class="text-xs px-2.5 py-1 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition cursor-pointer">
                                        <i class="fas fa-pen"></i> Edit
                                    </button>
                                    @endcan
                                    @can('hero.delete')
                                    <button @click="deleteBanner(banner)"
                                        class="text-xs px-2.5 py-1 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition cursor-pointer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && banners.length === 0">
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                                <i class="fas fa-image text-2xl mb-2 block"></i>
                                No hero banners yet. Create your first banner!
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create / Edit Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4 py-8 overflow-y-auto">
        <div @click.away="closeModal()" class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl my-auto">

            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-800" x-text="editingId ? 'Edit Hero Banner' : 'Create Hero Banner'"></h3>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 cursor-pointer text-xl leading-none">&times;</button>
            </div>

            <form @submit.prevent="saveBanner()" enctype="multipart/form-data">
                <div class="p-6 space-y-5">

                    {{-- Image Upload --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-2">Banner Image</label>
                        <div class="flex items-start gap-4">
                            <div class="w-32 h-20 rounded-xl overflow-hidden bg-gray-100 flex items-center justify-center shrink-0 border border-gray-200">
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!imagePreview && form.image_url">
                                    <img :src="form.image_url" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!imagePreview && !form.image_url">
                                    <i class="fas fa-image text-gray-300 text-2xl"></i>
                                </template>
                            </div>
                            <div class="flex-1">
                                <input type="file" x-ref="imageInput" accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 cursor-pointer"
                                    @change="handleImagePreview">
                                <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — max 4MB</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Title --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                            <input x-model="form.title" type="text" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="e.g. Premium Nutrition for Peak Performance">
                        </div>

                        {{-- Badge --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Badge</label>
                            <input x-model="form.badge" type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="e.g. New Arrival">
                        </div>

                        {{-- Subtitle --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Subtitle</label>
                            <input x-model="form.subtitle" type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="e.g. Trusted by 10,000+ athletes">
                        </div>

                        {{-- Description --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                            <textarea x-model="form.description" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"
                                placeholder="Short supporting copy displayed beneath the title..."></textarea>
                        </div>

                        {{-- Button Text --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Button Text</label>
                            <input x-model="form.button_text" type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="e.g. Shop Now">
                        </div>

                        {{-- Button URL --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Button URL</label>
                            <input x-model="form.button_url" type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="e.g. /products or /shop">
                        </div>

                        {{-- Sort Order --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Sort Order</label>
                            <input x-model.number="form.sort_order" type="number" min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>

                        {{-- Active Toggle --}}
                        <div class="flex items-center gap-3 pt-5">
                            <span class="text-xs font-medium text-gray-700">Active</span>
                            <button type="button" @click="form.is_active = !form.is_active"
                                :class="form.is_active ? 'bg-green-600' : 'bg-gray-300'"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition cursor-pointer">
                                <span :class="form.is_active ? 'translate-x-6' : 'translate-x-1'"
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition"></span>
                            </button>
                        </div>

                        {{-- Schedule --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Start Date (optional)</label>
                            <input x-model="form.starts_at" type="datetime-local"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">End Date (optional)</label>
                            <input x-model="form.ends_at" type="datetime-local"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>

                    {{-- Error display --}}
                    <div x-show="formError" class="text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2" x-text="formError"></div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" :disabled="saving"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 transition cursor-pointer">
                        <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                        <span x-text="saving ? 'Saving…' : (editingId ? 'Update Banner' : 'Create Banner')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function heroBannersApp() {
    return {
        banners: [],
        loading: true,
        showModal: false,
        saving: false,
        editingId: null,
        imagePreview: null,
        formError: null,
        flashMsg: null,
        flashType: 'success',

        form: {
            title: '',
            badge: '',
            subtitle: '',
            description: '',
            button_text: '',
            button_url: '',
            sort_order: 0,
            is_active: true,
            starts_at: '',
            ends_at: '',
            image_url: null,
        },

        csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.content;
        },

        async init() {
            await this.loadBanners();
        },

        async loadBanners() {
            this.loading = true;
            try {
                const r = await fetch('/api/v1/admin/hero-banners', { headers: { 'Accept': 'application/json' } });
                const d = await r.json();
                this.banners = d.data ?? [];
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            this.editingId = null;
            this.imagePreview = null;
            this.formError = null;
            this.form = { title: '', badge: '', subtitle: '', description: '', button_text: '', button_url: '', sort_order: (this.banners.length), is_active: true, starts_at: '', ends_at: '', image_url: null };
            this.showModal = true;
        },

        openEdit(banner) {
            this.editingId = banner.id;
            this.imagePreview = null;
            this.formError = null;
            this.form = {
                title: banner.title ?? '',
                badge: banner.badge ?? '',
                subtitle: banner.subtitle ?? '',
                description: banner.description ?? '',
                button_text: banner.button_text ?? '',
                button_url: banner.button_url ?? '',
                sort_order: banner.sort_order ?? 0,
                is_active: banner.is_active,
                starts_at: banner.starts_at ? new Date(banner.starts_at).toISOString().slice(0, 16) : '',
                ends_at: banner.ends_at ? new Date(banner.ends_at).toISOString().slice(0, 16) : '',
                image_url: banner.image_url,
            };
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.editingId = null;
            this.imagePreview = null;
            this.formError = null;
            if (this.$refs.imageInput) this.$refs.imageInput.value = '';
        },

        handleImagePreview(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => this.imagePreview = ev.target.result;
            reader.readAsDataURL(file);
        },

        async saveBanner() {
            this.saving = true;
            this.formError = null;

            const formData = new FormData();
            const fields = ['title', 'badge', 'subtitle', 'description', 'button_text', 'button_url', 'sort_order', 'starts_at', 'ends_at'];
            fields.forEach(k => { if (this.form[k] !== null && this.form[k] !== undefined) formData.append(k, this.form[k]); });
            formData.append('is_active', this.form.is_active ? '1' : '0');

            const file = this.$refs.imageInput?.files?.[0];
            if (file) formData.append('image', file);

            let url = '/api/v1/admin/hero-banners';
            let method = 'POST';
            if (this.editingId) {
                url += `/${this.editingId}`;
                formData.append('_method', 'PUT');
            }

            try {
                const r = await fetch(url, {
                    method,
                    headers: { 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: formData,
                });
                const d = await r.json();
                if (r.ok) {
                    this.flash('success', d.message || 'Banner saved!');
                    this.closeModal();
                    await this.loadBanners();
                } else {
                    this.formError = d.message || (d.errors ? Object.values(d.errors).flat().join(' ') : 'Failed to save.');
                }
            } catch (e) {
                this.formError = 'Network error.';
            } finally {
                this.saving = false;
            }
        },

        async toggleActive(banner) {
            try {
                const r = await fetch(`/api/v1/admin/hero-banners/${banner.id}/toggle-active`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                });
                if (r.ok) {
                    const d = await r.json();
                    const idx = this.banners.findIndex(b => b.id === banner.id);
                    if (idx !== -1) this.banners[idx] = d.data;
                    this.flash('success', 'Status updated.');
                }
            } catch (e) {
                this.flash('error', 'Network error.');
            }
        },

        async deleteBanner(banner) {
            if (!confirm(`Delete "${banner.title}"? This cannot be undone.`)) return;
            try {
                const r = await fetch(`/api/v1/admin/hero-banners/${banner.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                });
                if (r.ok) {
                    this.banners = this.banners.filter(b => b.id !== banner.id);
                    this.flash('success', 'Banner deleted.');
                }
            } catch (e) {
                this.flash('error', 'Failed to delete.');
            }
        },

        flash(type, msg) {
            this.flashType = type;
            this.flashMsg = msg;
            setTimeout(() => this.flashMsg = null, 4000);
        },
    };
}
</script>
@endpush
