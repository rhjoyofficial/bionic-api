@extends('layouts.admin')

@section('title', 'Categories')

@section('content')

<div x-data="categoryManager()" x-init="init()">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Categories</h2>
            <p class="text-sm text-gray-500 mt-0.5">Manage product categories</p>
        </div>
        @can('category.create')
        <button @click="openCreate()"
            class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition cursor-pointer">
            <i class="fa-solid fa-plus text-xs"></i>
            Add Category
        </button>
        @endcan
    </div>

    {{-- Search + Stats bar --}}
    <div class="bg-white border border-gray-200 rounded-xl mb-4 p-4 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
        <div class="relative w-full sm:w-72">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input
                type="text"
                x-model="search"
                @input.debounce.400ms="loadCategories(1)"
                placeholder="Search categories…"
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-green-600"
            >
        </div>
        <p class="text-sm text-gray-500" x-text="meta.total !== undefined ? meta.total + ' categories' : ''"></p>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-12">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Slug</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Products</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">

                    {{-- Loading skeleton --}}
                    <template x-if="loading">
                        <template x-for="i in 5" :key="i">
                            <tr>
                                <td colspan="7" class="px-5 py-4">
                                    <div class="h-4 bg-gray-100 rounded animate-pulse w-full"></div>
                                </td>
                            </tr>
                        </template>
                    </template>

                    {{-- Rows --}}
                    <template x-if="!loading">
                        <template x-for="cat in categories" :key="cat.id">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3 text-gray-400 text-xs" x-text="cat.id"></td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <template x-if="cat.image_url">
                                            <img :src="cat.image_url" class="w-9 h-9 rounded-lg object-cover border border-gray-100">
                                        </template>
                                        <template x-if="!cat.image_url">
                                            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                                                <i class="fa-solid fa-image text-gray-400 text-xs"></i>
                                            </div>
                                        </template>
                                        <span class="font-medium text-gray-800" x-text="cat.name"></span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 font-mono text-xs text-gray-500" x-text="cat.slug"></td>
                                <td class="px-5 py-3 text-gray-600" x-text="cat.products_count ?? '—'"></td>
                                <td class="px-5 py-3 text-gray-500" x-text="cat.sort_order ?? 0"></td>
                                <td class="px-5 py-3">
                                    <span :class="cat.is_active
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-gray-100 text-gray-500'"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                        x-text="cat.is_active ? 'Active' : 'Inactive'">
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        @can('category.update')
                                        <button @click="openEdit(cat)"
                                            class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium transition cursor-pointer">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        @endcan
                                        @can('category.delete')
                                        <button @click="confirmDelete(cat.id)"
                                            class="inline-flex items-center gap-1 text-xs text-red-500 hover:text-red-700 font-medium transition cursor-pointer">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>

                    {{-- Empty state --}}
                    <template x-if="!loading && categories.length === 0">
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                <i class="fa-solid fa-layer-group text-2xl mb-2 block"></i>
                                No categories found
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
                &bull; <span x-text="meta.total"></span> total
            </p>
            <div class="flex gap-2">
                <button
                    @click="loadCategories(meta.current_page - 1)"
                    :disabled="meta.current_page <= 1"
                    class="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition cursor-pointer disabled:cursor-not-allowed">
                    &larr; Prev
                </button>
                <button
                    @click="loadCategories(meta.current_page + 1)"
                    :disabled="meta.current_page >= meta.last_page"
                    class="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition cursor-pointer disabled:cursor-not-allowed">
                    Next &rarr;
                </button>
            </div>
        </div>
    </div>

    {{-- ============================
         Create / Edit Modal
    ============================= --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>

        {{-- Panel --}}
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-800"
                    x-text="isEditing ? 'Edit Category' : 'Add Category'"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Form --}}
            <form @submit.prevent="saveCategory()" class="p-6 space-y-4">

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.name"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600"
                        :class="errors.name ? 'border-red-400' : ''"
                        placeholder="e.g. Supplements">
                    <p x-show="errors.name" class="mt-1 text-xs text-red-600" x-text="errors.name?.[0]"></p>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea x-model="form.description" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 resize-none"
                        placeholder="Optional description…"></textarea>
                </div>

                {{-- Image --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category Image</label>
                    <div class="flex items-center gap-4">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                        </template>
                        <label class="cursor-pointer inline-flex items-center gap-2 border border-dashed border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-500 hover:border-green-500 hover:text-green-600 transition">
                            <i class="fa-solid fa-upload text-xs"></i>
                            <span x-text="imagePreview ? 'Change image' : 'Upload image'"></span>
                            <input type="file" accept="image/*" class="sr-only" @change="handleImageChange($event)">
                        </label>
                    </div>
                    <p x-show="errors.image" class="mt-1 text-xs text-red-600" x-text="errors.image?.[0]"></p>
                </div>

                {{-- Sort Order & Status --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                        <input type="number" x-model.number="form.sort_order" min="0"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                    </div>
                    <div class="flex flex-col justify-end pb-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" x-model="form.is_active" class="sr-only peer">
                                <div class="w-10 h-6 bg-gray-200 peer-checked:bg-green-600 rounded-full transition"></div>
                                <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition peer-checked:translate-x-4"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" @click="showModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" :disabled="saving"
                        class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 disabled:opacity-60 text-white text-sm font-medium px-5 py-2 rounded-lg transition cursor-pointer">
                        <i x-show="saving" class="fa-solid fa-spinner fa-spin text-xs"></i>
                        <span x-text="isEditing ? 'Update Category' : 'Create Category'"></span>
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- ============================
         Delete Confirm Modal
    ============================= --}}
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        <div class="absolute inset-0 bg-black/50" @click="showDeleteModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800 mb-1">Delete Category?</h3>
            <p class="text-sm text-gray-500 mb-5">This cannot be undone. Products in this category will be unlinked.</p>
            <div class="flex gap-3 justify-center">
                <button @click="showDeleteModal = false"
                    class="px-4 py-2 text-sm font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                    Cancel
                </button>
                <button @click="deleteCategory()"
                    class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg transition cursor-pointer">
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
function categoryManager() {
    return {
        categories: [],
        meta: {},
        loading: true,
        search: '',

        showModal: false,
        showDeleteModal: false,
        isEditing: false,
        saving: false,
        deleteId: null,
        errors: {},

        form: {
            id: null,
            name: '',
            description: '',
            is_active: true,
            sort_order: 0,
        },
        imageFile: null,
        imagePreview: null,

        async init() {
            await this.loadCategories();
        },

        async loadCategories(page = 1) {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page, per_page: 15 });
                if (this.search) params.set('q', this.search);

                const r = await fetch(`/api/v1/admin/categories?${params}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await r.json();
                this.categories = data.data ?? [];
                this.meta = data.meta ?? {};
            } catch (e) {
                console.error('Failed to load categories', e);
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            this.isEditing = false;
            this.form = { id: null, name: '', description: '', is_active: true, sort_order: 0 };
            this.imageFile = null;
            this.imagePreview = null;
            this.errors = {};
            this.showModal = true;
        },

        openEdit(category) {
            this.isEditing = true;
            this.form = {
                id: category.id,
                name: category.name,
                description: category.description ?? '',
                is_active: category.is_active,
                sort_order: category.sort_order ?? 0,
            };
            this.imagePreview = category.image_url ?? null;
            this.imageFile = null;
            this.errors = {};
            this.showModal = true;
        },

        handleImageChange(e) {
            this.imageFile = e.target.files[0] ?? null;
            if (this.imageFile) {
                const reader = new FileReader();
                reader.onload = (ev) => { this.imagePreview = ev.target.result; };
                reader.readAsDataURL(this.imageFile);
            }
        },

        async saveCategory() {
            this.saving = true;
            this.errors = {};

            const fd = new FormData();
            fd.append('name', this.form.name);
            fd.append('description', this.form.description ?? '');
            fd.append('is_active', this.form.is_active ? '1' : '0');
            fd.append('sort_order', this.form.sort_order ?? 0);
            if (this.imageFile) fd.append('image', this.imageFile);

            const isEdit = this.isEditing;
            const url = isEdit
                ? `/api/v1/admin/categories/${this.form.id}`
                : '/api/v1/admin/categories';

            if (isEdit) fd.append('_method', 'PUT');

            try {
                const r = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: fd,
                });

                const data = await r.json();

                if (r.ok) {
                    this.showModal = false;
                    await this.loadCategories(this.meta.current_page ?? 1);
                } else if (r.status === 422) {
                    this.errors = data.errors ?? {};
                } else {
                    alert(data.message ?? 'Something went wrong.');
                }
            } catch (e) {
                alert('Network error. Please try again.');
            } finally {
                this.saving = false;
            }
        },

        confirmDelete(id) {
            this.deleteId = id;
            this.showDeleteModal = true;
        },

        async deleteCategory() {
            try {
                const r = await fetch(`/api/v1/admin/categories/${this.deleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                if (r.ok) {
                    this.showDeleteModal = false;
                    await this.loadCategories(this.meta.current_page ?? 1);
                } else {
                    const data = await r.json();
                    alert(data.message ?? 'Delete failed.');
                }
            } catch (e) {
                alert('Network error. Please try again.');
            }
        },
    };
}
</script>
@endpush
