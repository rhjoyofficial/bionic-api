@extends('layouts.admin')

@section('title', 'Access Control')

@section('content')
    <div x-data="accessControl()" x-init="init()" x-cloak>

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Access Control</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage roles, permission matrix, and staff assignments</p>
            </div>
            <button x-show="tab === 'roles'" @click="createModal.open = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition cursor-pointer">
                <i class="fas fa-plus"></i> New Role
            </button>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex gap-6">
                @foreach ([['roles', 'fa-shield-halved', 'Roles'], ['matrix', 'fa-table-cells', 'Permission Matrix'], ['staff', 'fa-user-tie', 'Admin Staff']] as [$t, $icon, $label])
                    <button @click="switchTab('{{ $t }}')"
                        :class="tab === '{{ $t }}' ? 'border-indigo-600 text-indigo-600' :
                            'border-transparent text-gray-500 hover:text-gray-700'"
                        class="inline-flex items-center gap-1.5 pb-3 text-sm font-medium border-b-2 transition cursor-pointer">
                        <i class="fas {{ $icon }} text-xs"></i> {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- ───────────────────────────────────────────────────── --}}
        {{-- TAB: Roles                                            --}}
        {{-- ───────────────────────────────────────────────────── --}}
        <div x-show="tab === 'roles'">
            <div x-show="rolesLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="i in 6">
                    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 animate-pulse">
                        <div class="h-4 bg-gray-200 rounded w-1/2 mb-3"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/3 mb-2"></div>
                        <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                    </div>
                </template>
            </div>

            <div x-show="!rolesLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="role in roles" :key="role.id">
                    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
                        {{-- Header --}}
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div :class="role.is_protected ? 'bg-indigo-100' : 'bg-gray-100'"
                                    class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0">
                                    <i :class="role.is_protected ? 'fa-shield text-indigo-600' : 'fa-user-tag text-gray-500'"
                                        class="fas text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900" x-text="role.name"></p>
                                    <span x-show="role.is_protected"
                                        class="text-xs bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-medium">System</span>
                                    <span x-show="!role.is_protected"
                                        class="text-xs bg-gray-50 text-gray-500 px-1.5 py-0.5 rounded">Custom</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1" x-show="!role.is_protected">
                                <button @click="openEditModal(role)" title="Rename"
                                    class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition cursor-pointer">
                                    <i class="fas fa-pencil text-xs"></i>
                                </button>
                                <button @click="deleteModal = { open: true, role: role }" title="Delete"
                                    :disabled="role.users_count > 0"
                                    :class="role.users_count > 0 ? 'opacity-30 cursor-not-allowed' :
                                        'hover:text-red-600 hover:bg-red-50'"
                                    class="p-1.5 text-gray-400 rounded transition cursor-pointer">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="flex gap-4 mb-3">
                            <div class="text-center">
                                <p class="text-lg font-bold text-gray-900" x-text="role.users_count"></p>
                                <p class="text-xs text-gray-400">Users</p>
                            </div>
                            <div class="text-center">
                                <p class="text-lg font-bold text-gray-900" x-text="role.permissions_count"></p>
                                <p class="text-xs text-gray-400">Permissions</p>
                            </div>
                        </div>

                        {{-- Permission preview --}}
                        <div class="flex flex-wrap gap-1 mb-3 min-h-6">
                            <template x-for="perm in (role.permissions ?? []).slice(0,5)" :key="perm">
                                <span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded font-mono"
                                    x-text="perm.split('.')[0]"></span>
                            </template>
                            <span x-show="(role.permissions ?? []).length > 5" class="text-xs text-gray-400 px-1"
                                x-text="'+' + ((role.permissions ?? []).length - 5) + ' more'"></span>
                            <span x-show="(role.permissions ?? []).length === 0" class="text-xs text-gray-400 italic">No
                                permissions</span>
                        </div>

                        {{-- Footer actions --}}
                        <div class="flex gap-2 pt-3 border-t border-gray-100">
                            <button @click="openUsersModal(role)"
                                class="flex-1 text-xs text-center py-1.5 text-indigo-600 hover:bg-indigo-50 rounded transition cursor-pointer"
                                x-show="role.users_count > 0">
                                <i class="fas fa-users mr-1"></i>
                                View <span x-text="role.users_count"></span> Users
                            </button>
                            <button @click="switchTab('matrix')"
                                class="flex-1 text-xs text-center py-1.5 text-gray-500 hover:bg-gray-50 rounded transition cursor-pointer">
                                <i class="fas fa-sliders mr-1"></i> Edit Permissions
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────── --}}
        {{-- TAB: Permission Matrix                                --}}
        {{-- ───────────────────────────────────────────────────── --}}
        <div x-show="tab === 'matrix'">
            <div x-show="matrixLoading" class="bg-white rounded-xl p-8 text-center shadow-sm border border-gray-100">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mx-auto"></div>
                <p class="text-sm text-gray-500 mt-3">Loading permission matrix…</p>
            </div>

            <div x-show="!matrixLoading">
                {{-- Legend & hint --}}
                <div class="flex items-center gap-4 mb-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-indigo-600 inline-block"></span> Granted
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-gray-200 inline-block"></span> Not granted
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-indigo-100 inline-block border border-indigo-200"></span> System
                        (auto-granted)
                    </span>
                    <span class="ml-auto text-gray-400 italic">Changes auto-save per role after a short delay</span>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
                    <table class="text-xs border-collapse">
                        <thead>
                            {{-- Group header row --}}
                            <tr class="border-b border-gray-200">
                                <th
                                    class="sticky left-0 z-20 bg-white px-4 py-3 text-left text-xs font-semibold text-gray-700 min-w-35 border-r border-gray-200">
                                    Role
                                </th>
                                <template x-for="[group, perms] in Object.entries(matrixGroups)" :key="group">
                                    <th :colspan="perms.length"
                                        class="px-2 py-2 text-center font-semibold text-gray-600 uppercase tracking-wide border-r border-gray-200 bg-gray-50"
                                        :class="groupColors[group] ?? 'text-gray-600'" x-text="group"></th>
                                </template>
                            </tr>
                            {{-- Permission sub-header row --}}
                            <tr class="border-b-2 border-gray-200">
                                <th class="sticky left-0 z-20 bg-gray-50 px-4 py-2 border-r border-gray-200">
                                    <span class="text-xs text-gray-400">↓ role / perm →</span>
                                </th>
                                <template x-for="[group, perms] in Object.entries(matrixGroups)" :key="'h-' + group">
                                    <template x-for="perm in perms" :key="perm">
                                        <th class="px-1 py-2 text-center font-medium text-gray-500 border-r border-gray-100 whitespace-nowrap min-w-13"
                                            x-text="permLabel(perm)"></th>
                                    </template>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="role in matrixRoles" :key="role.id">
                                <tr class="border-b border-gray-100 hover:bg-gray-50/50 transition"
                                    :class="pendingRoles[role.id] ? 'bg-amber-50/30' : ''">
                                    {{-- Role name cell --}}
                                    <td class="sticky left-0 z-10 bg-inherit px-4 py-2.5 border-r border-gray-200">
                                        <div class="flex items-center justify-between gap-2">
                                            <div>
                                                <p class="font-semibold text-gray-800 whitespace-nowrap"
                                                    x-text="role.name"></p>
                                                <p class="text-gray-400 mt-0.5"
                                                    x-text="role.users_count + ' user' + (role.users_count !== 1 ? 's' : '')">
                                                </p>
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                {{-- Save indicator --}}
                                                <span x-show="savingRoles[role.id]"
                                                    class="text-xs text-amber-600 flex items-center gap-1 whitespace-nowrap">
                                                    <i class="fas fa-circle-notch fa-spin text-xs"></i> Saving
                                                </span>
                                                <span x-show="savedRoles[role.id] && !savingRoles[role.id]"
                                                    class="text-xs text-green-600 flex items-center gap-1">
                                                    <i class="fas fa-check text-xs"></i> Saved
                                                </span>
                                                {{-- Grant/Revoke All --}}
                                                <div x-show="!role.is_protected" class="flex gap-1">
                                                    <button @click="grantAll(role.id)"
                                                        class="px-1.5 py-0.5 bg-green-100 text-green-700 rounded text-xs hover:bg-green-200 transition cursor-pointer"
                                                        title="Grant all">All</button>
                                                    <button @click="revokeAll(role.id)"
                                                        class="px-1.5 py-0.5 bg-red-100 text-red-700 rounded text-xs hover:bg-red-200 transition cursor-pointer"
                                                        title="Revoke all">None</button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Permission cells --}}
                                    <template x-for="[group, perms] in Object.entries(matrixGroups)"
                                        :key="'r' + role.id + group">
                                        <template x-for="perm in perms" :key="'c' + role.id + perm">
                                            <td class="px-1 py-2 text-center border-r border-gray-100">
                                                <button @click="role.is_protected ? null : toggleCell(role.id, perm)"
                                                    :disabled="role.is_protected"
                                                    :title="role.is_protected ? role.name + ' always has ' + perm : (matrix[role
                                                        .id]?.[perm] ? 'Revoke' : 'Grant') + ': ' + perm"
                                                    :class="{
                                                        'bg-indigo-600 text-white hover:bg-indigo-700': matrix[role.id]
                                                            ?.[perm] && !role.is_protected,
                                                        'bg-indigo-100 text-indigo-400 cursor-default': matrix[role.id]
                                                            ?.[perm] && role.is_protected,
                                                        'bg-gray-200 text-gray-400 hover:bg-gray-300': !matrix[role.id]
                                                            ?.[perm] && !role.is_protected,
                                                        'bg-gray-100 text-gray-300 cursor-default': !matrix[role.id]?.[
                                                            perm
                                                        ] && role.is_protected,
                                                    }"
                                                    class="w-7 h-7 rounded flex items-center justify-center mx-auto transition cursor-pointer">
                                                    <i :class="matrix[role.id]?.[perm] ? 'fa-check' : 'fa-minus'"
                                                        class="fas text-xs"></i>
                                                </button>
                                            </td>
                                        </template>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────── --}}
        {{-- TAB: Admin Staff                                      --}}
        {{-- ───────────────────────────────────────────────────── --}}
        <div x-show="tab === 'staff'">

            {{-- Search --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
                <div class="relative max-w-md">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                    <input x-model="staffSearch" @input.debounce.400ms="staffPage=1; loadStaff()" type="text"
                        placeholder="Search by name or email…"
                        class="pl-9 pr-4 py-2 w-full text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div x-show="staffLoading" class="p-8 flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                </div>
                <table x-show="!staffLoading" class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Current Role</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Last Login</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Assign Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="user in staffUsers" :key="user.id">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                                            <span class="text-xs font-bold text-indigo-600"
                                                x-text="user.name.charAt(0).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800" x-text="user.name"></p>
                                            <p class="text-xs text-gray-400" x-text="user.email"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span x-show="user.primary_role"
                                        :class="user.primary_role === 'Super Admin' || user.primary_role === 'Admin' ?
                                            'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700'"
                                        class="text-xs px-2 py-0.5 rounded-full font-medium"
                                        x-text="user.primary_role"></span>
                                    <span x-show="!user.primary_role" class="text-xs text-gray-400 italic">No role</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        :class="user.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                        class="text-xs px-2 py-0.5 rounded-full font-medium"
                                        x-text="user.is_active ? 'Active' : 'Inactive'"></span>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-500"
                                    x-text="user.last_login_at ? fmtDate(user.last_login_at) : 'Never'"></td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <select :id="'role-' + user.id"
                                            class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-indigo-500"
                                            @change.stop>
                                            <option value="">— select —</option>
                                            <template x-for="r in allRoles" :key="r">
                                                <option :value="r" :selected="r === user.primary_role"
                                                    x-text="r"></option>
                                            </template>
                                        </select>
                                        <button @click="assignRole(user)" :disabled="savingRole[user.id]"
                                            class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition cursor-pointer">
                                            <i class="fas fa-check"
                                                :class="savingRole[user.id] ? 'animate-pulse' : ''"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="!staffLoading && staffUsers.length === 0">
                            <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                                <i class="fas fa-user-slash text-3xl mb-3 block"></i> No admin users found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div x-show="staffMeta.last_page > 1" class="flex items-center justify-between mt-4">
                <p class="text-sm text-gray-500">
                    Page <span x-text="staffMeta.current_page"></span> of <span x-text="staffMeta.last_page"></span>
                    &nbsp;·&nbsp; <span x-text="staffMeta.total"></span> users
                </p>
                <div class="flex gap-2">
                    <button @click="staffPage--; loadStaff()" :disabled="staffPage <= 1"
                        class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition cursor-pointer">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    <button @click="staffPage++; loadStaff()" :disabled="staffPage >= staffMeta.last_page"
                        class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition cursor-pointer">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ───────────────── Modals ────────────────────────────── --}}

        {{-- Create Role Modal --}}
        <div x-show="createModal.open" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
            <div @click.outside="createModal.open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Create New Role</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role Name</label>
                    <input x-model="createModal.name" @keyup.enter="createRole()" type="text"
                        placeholder="e.g. Content Manager"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p x-show="createModal.error" class="text-xs text-red-500 mt-1" x-text="createModal.error"></p>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button @click="createModal.open = false; createModal.name = ''; createModal.error = null"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                        Cancel
                    </button>
                    <button @click="createRole()" :disabled="createModal.loading || !createModal.name.trim()"
                        class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition cursor-pointer">
                        <i class="fas fa-plus mr-1"
                            :class="createModal.loading ? 'animate-spin fa-circle-notch' : ''"></i>
                        <span x-text="createModal.loading ? 'Creating…' : 'Create Role'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Edit Role Modal --}}
        <div x-show="editModal.open" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
            <div @click.outside="editModal.open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Rename Role</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Name</label>
                    <input x-model="editModal.name" @keyup.enter="saveRoleName()" type="text"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p x-show="editModal.error" class="text-xs text-red-500 mt-1" x-text="editModal.error"></p>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button @click="editModal.open = false"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                        Cancel
                    </button>
                    <button @click="saveRoleName()" :disabled="editModal.loading || !editModal.name.trim()"
                        class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition cursor-pointer">
                        <span x-text="editModal.loading ? 'Saving…' : 'Save'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Delete Role Modal --}}
        <div x-show="deleteModal.open" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
            <div @click.outside="deleteModal.open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                        <i class="fas fa-trash text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Delete Role?</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            This will permanently remove the
                            <strong class="text-gray-700" x-text="deleteModal.role?.name"></strong> role.
                            This cannot be undone.
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="deleteModal.open = false"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                        Cancel
                    </button>
                    <button @click="deleteRole()" :disabled="deleteModal.loading"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 transition cursor-pointer">
                        <span x-text="deleteModal.loading ? 'Deleting…' : 'Delete'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Role Users Modal --}}
        <div x-show="usersModal.open" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
            <div @click.outside="usersModal.open = false"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900">
                        Users with role: <span class="text-indigo-600" x-text="usersModal.role?.name"></span>
                    </h3>
                    <button @click="usersModal.open = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                <div class="overflow-y-auto flex-1">
                    <div x-show="usersModal.loading" class="p-8 flex justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                    </div>
                    <table x-show="!usersModal.loading" class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Last Login
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="u in usersModal.users" :key="u.id">
                                <tr>
                                    <td class="px-5 py-3 text-sm font-medium text-gray-800" x-text="u.name"></td>
                                    <td class="px-5 py-3 text-sm text-gray-500" x-text="u.email"></td>
                                    <td class="px-5 py-3">
                                        <span
                                            :class="u.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                            class="text-xs px-2 py-0.5 rounded-full font-medium"
                                            x-text="u.is_active ? 'Active' : 'Inactive'"></span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-500"
                                        x-text="u.last_login_at ? fmtDate(u.last_login_at) : 'Never'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="usersModal.meta?.last_page > 1"
                    class="flex items-center justify-between px-5 py-3 border-t border-gray-100">
                    <p class="text-xs text-gray-500">
                        <span x-text="usersModal.meta?.total"></span> total users
                    </p>
                    <div class="flex gap-2">
                        <button @click="loadRoleUsers(usersModal.page - 1)" :disabled="usersModal.page <= 1"
                            class="px-3 py-1 text-xs border border-gray-200 rounded disabled:opacity-40 hover:bg-gray-50 cursor-pointer">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button @click="loadRoleUsers(usersModal.page + 1)"
                            :disabled="usersModal.page >= usersModal.meta?.last_page"
                            class="px-3 py-1 text-xs border border-gray-200 rounded disabled:opacity-40 hover:bg-gray-50 cursor-pointer">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function accessControl() {
            return {
                tab: 'roles',

                // ── Roles tab state ───────────────────────────────────────────────
                roles: [],
                rolesLoading: false,
                createModal: {
                    open: false,
                    name: '',
                    loading: false,
                    error: null
                },
                editModal: {
                    open: false,
                    role: null,
                    name: '',
                    loading: false,
                    error: null
                },
                deleteModal: {
                    open: false,
                    role: null,
                    loading: false
                },
                usersModal: {
                    open: false,
                    role: null,
                    users: [],
                    meta: {},
                    page: 1,
                    loading: false
                },

                // ── Matrix tab state ──────────────────────────────────────────────
                matrixLoading: false,
                matrixRoles: [],
                matrixGroups: {},
                matrix: {},
                pendingRoles: {},
                savingRoles: {},
                savedRoles: {},
                saveTimers: {},
                groupColors: {
                    category: 'text-blue-600',
                    product: 'text-green-600',
                    order: 'text-purple-600',
                    coupon: 'text-pink-600',
                    shipping: 'text-orange-600',
                    customer: 'text-teal-600',
                    notification: 'text-indigo-600',
                    system: 'text-gray-600',
                    analytics: 'text-cyan-600',
                    role: 'text-red-600',
                },

                // ── Staff tab state ───────────────────────────────────────────────
                staffUsers: [],
                staffMeta: {},
                staffLoading: false,
                staffSearch: '',
                staffPage: 1,
                allRoles: [],
                savingRole: {},

                // ── Init ──────────────────────────────────────────────────────────
                async init() {
                    await this.loadRoles();
                },

                switchTab(tab) {
                    this.tab = tab;
                    if (tab === 'matrix' && this.matrixRoles.length === 0) this.loadMatrix();
                    if (tab === 'staff' && this.staffUsers.length === 0) this.loadStaff();
                },

                // ── Roles ─────────────────────────────────────────────────────────
                async loadRoles() {
                    this.rolesLoading = true;
                    try {
                        const r = await fetch('/api/v1/admin/access-control/roles', {
                            headers: this.h()
                        });
                        const d = await r.json();
                        if (d.success) this.roles = d.data.roles;
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.rolesLoading = false;
                    }
                },

                async createRole() {
                    if (!this.createModal.name.trim()) return;
                    this.createModal.loading = true;
                    this.createModal.error = null;
                    try {
                        const r = await fetch('/api/v1/admin/access-control/roles', {
                            method: 'POST',
                            headers: {
                                ...this.h(),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                name: this.createModal.name.trim()
                            }),
                        });
                        const d = await r.json();
                        if (r.ok && d.success) {
                            this.roles.push(d.data);
                            this.createModal.open = false;
                            this.createModal.name = '';
                            this.toast('Role created', 'success');
                        } else {
                            this.createModal.error = d.errors?.name?.[0] ?? d.message ?? 'Failed to create role';
                        }
                    } catch (e) {
                        this.createModal.error = 'Network error';
                    } finally {
                        this.createModal.loading = false;
                    }
                },

                openEditModal(role) {
                    this.editModal.role = role;
                    this.editModal.name = role.name;
                    this.editModal.error = null;
                    this.editModal.open = true;
                },

                async saveRoleName() {
                    if (!this.editModal.name.trim()) return;
                    this.editModal.loading = true;
                    this.editModal.error = null;
                    try {
                        const r = await fetch(`/api/v1/admin/access-control/roles/${this.editModal.role.id}`, {
                            method: 'PUT',
                            headers: {
                                ...this.h(),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                name: this.editModal.name.trim()
                            }),
                        });
                        const d = await r.json();
                        if (r.ok && d.success) {
                            const idx = this.roles.findIndex(r => r.id === this.editModal.role.id);
                            if (idx > -1) this.roles[idx].name = d.data.name;
                            // Update matrix role name too
                            const mi = this.matrixRoles.findIndex(r => r.id === this.editModal.role.id);
                            if (mi > -1) this.matrixRoles[mi].name = d.data.name;
                            this.editModal.open = false;
                            this.toast('Role renamed', 'success');
                        } else {
                            this.editModal.error = d.errors?.name?.[0] ?? d.message ?? 'Failed';
                        }
                    } catch (e) {
                        this.editModal.error = 'Network error';
                    } finally {
                        this.editModal.loading = false;
                    }
                },

                async deleteRole() {
                    this.deleteModal.loading = true;
                    try {
                        const r = await fetch(`/api/v1/admin/access-control/roles/${this.deleteModal.role.id}`, {
                            method: 'DELETE',
                            headers: this.h(),
                        });
                        const d = await r.json();
                        if (r.ok && d.success) {
                            this.roles = this.roles.filter(r => r.id !== this.deleteModal.role.id);
                            this.matrixRoles = this.matrixRoles.filter(r => r.id !== this.deleteModal.role.id);
                            delete this.matrix[this.deleteModal.role.id];
                            this.deleteModal.open = false;
                            this.toast('Role deleted', 'success');
                        } else {
                            this.toast(d.message || 'Failed to delete role', 'error');
                            this.deleteModal.open = false;
                        }
                    } catch (e) {
                        this.toast('Network error', 'error');
                    } finally {
                        this.deleteModal.loading = false;
                    }
                },

                async openUsersModal(role) {
                    this.usersModal.role = role;
                    this.usersModal.page = 1;
                    this.usersModal.open = true;
                    await this.loadRoleUsers(1);
                },

                async loadRoleUsers(page) {
                    this.usersModal.page = page;
                    this.usersModal.loading = true;
                    try {
                        const r = await fetch(
                            `/api/v1/admin/access-control/roles/${this.usersModal.role.id}/users?page=${page}`, {
                                headers: this.h()
                            }
                        );
                        const d = await r.json();
                        if (d.success) {
                            this.usersModal.users = d.data.data;
                            this.usersModal.meta = d.data.meta;
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.usersModal.loading = false;
                    }
                },

                // ── Matrix ────────────────────────────────────────────────────────
                async loadMatrix() {
                    this.matrixLoading = true;
                    try {
                        const r = await fetch('/api/v1/admin/access-control/matrix', {
                            headers: this.h()
                        });
                        const d = await r.json();
                        if (d.success) {
                            this.matrixRoles = d.data.roles;
                            this.matrixGroups = d.data.groups;
                            this.matrix = d.data.matrix;
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.matrixLoading = false;
                    }
                },

                toggleCell(roleId, perm) {
                    if (!this.matrix[roleId]) return;
                    this.matrix[roleId][perm] = !this.matrix[roleId][perm];
                    this.scheduleSave(roleId);
                },

                grantAll(roleId) {
                    if (!this.matrix[roleId]) return;
                    Object.keys(this.matrix[roleId]).forEach(p => this.matrix[roleId][p] = true);
                    this.scheduleSave(roleId);
                },

                revokeAll(roleId) {
                    if (!this.matrix[roleId]) return;
                    Object.keys(this.matrix[roleId]).forEach(p => this.matrix[roleId][p] = false);
                    this.scheduleSave(roleId);
                },

                scheduleSave(roleId) {
                    this.pendingRoles[roleId] = true;
                    clearTimeout(this.saveTimers[roleId]);
                    this.saveTimers[roleId] = setTimeout(() => this.saveRolePermissions(roleId), 700);
                },

                async saveRolePermissions(roleId) {
                    const granted = Object.entries(this.matrix[roleId] ?? {})
                        .filter(([, v]) => v)
                        .map(([k]) => k);

                    this.savingRoles[roleId] = true;
                    delete this.pendingRoles[roleId];
                    try {
                        const r = await fetch(`/api/v1/admin/access-control/roles/${roleId}/permissions`, {
                            method: 'PUT',
                            headers: {
                                ...this.h(),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                permissions: granted
                            }),
                        });
                        const d = await r.json();
                        if (r.ok && d.success) {
                            this.savedRoles[roleId] = true;
                            // Also update roles tab permission_count
                            const ri = this.roles.findIndex(r => r.id == roleId);
                            if (ri > -1) this.roles[ri].permissions_count = d.data.permissions_count ?? granted.length;
                            setTimeout(() => {
                                delete this.savedRoles[roleId];
                            }, 2500);
                        } else {
                            this.toast(d.message || 'Failed to save permissions', 'error');
                        }
                    } catch (e) {
                        this.toast('Network error saving permissions', 'error');
                    } finally {
                        this.savingRoles[roleId] = false;
                    }
                },

                permLabel(perm) {
                    const action = perm.split('.')[1] ?? perm;
                    const labels = {
                        view: 'View',
                        create: 'Create',
                        update: 'Edit',
                        delete: 'Delete',
                        export: 'Export',
                        send: 'Send',
                        manage: 'Manage',
                        settings: 'Settings',
                        webhooks: 'Webhooks',
                        activity_log: 'Activity',
                        deactivate: 'Deactivate',
                    };
                    return labels[action] ?? action;
                },

                // ── Staff ─────────────────────────────────────────────────────────
                async loadStaff() {
                    this.staffLoading = true;
                    try {
                        const p = new URLSearchParams({
                            page: this.staffPage,
                            ...(this.staffSearch && {
                                q: this.staffSearch
                            }),
                        });
                        const r = await fetch(`/api/v1/admin/access-control/admin-users?${p}`, {
                            headers: this.h()
                        });
                        const d = await r.json();
                        if (d.success) {
                            this.staffUsers = d.data.data;
                            this.staffMeta = d.data.meta;
                            this.allRoles = d.data.all_roles;
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.staffLoading = false;
                    }
                },

                async assignRole(user) {
                    const sel = document.getElementById('role-' + user.id);
                    const newRole = sel?.value;
                    if (!newRole) return;
                    this.savingRole[user.id] = true;
                    try {
                        const r = await fetch(`/api/v1/admin/access-control/admin-users/${user.id}/role`, {
                            method: 'PATCH',
                            headers: {
                                ...this.h(),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                role: newRole
                            }),
                        });
                        const d = await r.json();
                        if (r.ok && d.success) {
                            const idx = this.staffUsers.findIndex(u => u.id === user.id);
                            if (idx > -1) this.staffUsers[idx].primary_role = d.data.primary_role;
                            this.toast(`Role assigned to ${user.name}`, 'success');
                        } else {
                            this.toast(d.message || 'Failed to assign role', 'error');
                        }
                    } catch (e) {
                        this.toast('Network error', 'error');
                    } finally {
                        this.savingRole[user.id] = false;
                    }
                },

                // ── Helpers ───────────────────────────────────────────────────────
                h() {
                    return {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    };
                },

                fmtDate(d) {
                    if (!d) return '—';
                    return new Date(d).toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });
                },

                toast(msg, type = 'success') {
                    const el = document.createElement('div');
                    el.className = `fixed bottom-5 right-5 z-[9999] px-4 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2
                ${type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`;
                    el.innerHTML =
                        `<i class="fas ${type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${msg}`;
                    document.body.appendChild(el);
                    setTimeout(() => el.remove(), 3500);
                },
            };
        }
    </script>
@endpush
