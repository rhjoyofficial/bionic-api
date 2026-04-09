@extends('layouts.admin')

@section('title', 'Notification Center')

@section('content')
<div x-data="notificationCenter()" x-init="init()" x-cloak>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notification Center</h1>
            <p class="text-sm text-gray-500 mt-0.5">Monitor alerts, manage failed jobs, and broadcast messages</p>
        </div>
        @can('notification.send')
        <button @click="switchTab('send')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            <i class="fas fa-paper-plane"></i> Send Notification
        </button>
        @endcan
    </div>

    {{-- Tab Nav --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex gap-6">
            <button @click="switchTab('overview')"
                    :class="tab === 'overview' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-1.5 pb-3 text-sm font-medium border-b-2 transition">
                <i class="fas fa-chart-bar text-xs"></i> Overview
            </button>
            <button @click="switchTab('notifications')"
                    :class="tab === 'notifications' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-1.5 pb-3 text-sm font-medium border-b-2 transition">
                <i class="fas fa-bell text-xs"></i> Sent
                <span x-show="stats && stats.total_notifications > 0"
                      class="ml-1 bg-gray-100 text-gray-600 text-xs px-1.5 py-0.5 rounded-full"
                      x-text="stats ? stats.total_notifications.toLocaleString() : ''"></span>
            </button>
            <button @click="switchTab('failed')"
                    :class="tab === 'failed' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-1.5 pb-3 text-sm font-medium border-b-2 transition">
                <i class="fas fa-triangle-exclamation text-xs"></i> Failed Jobs
                <span x-show="stats && stats.total_failed_jobs > 0"
                      :class="stats && stats.recent_failed_jobs > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'"
                      class="ml-1 text-xs px-1.5 py-0.5 rounded-full"
                      x-text="stats ? stats.total_failed_jobs : ''"></span>
            </button>
            @can('notification.send')
            <button @click="switchTab('send')"
                    :class="tab === 'send' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center gap-1.5 pb-3 text-sm font-medium border-b-2 transition">
                <i class="fas fa-paper-plane text-xs"></i> Send
            </button>
            @endcan
        </nav>
    </div>

    {{-- ─────────────────────────────────────────────── --}}
    {{-- TAB: Overview                                   --}}
    {{-- ─────────────────────────────────────────────── --}}
    <div x-show="tab === 'overview'">
        <div x-show="statsLoading" class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <template x-for="i in 5">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 animate-pulse">
                    <div class="h-3 bg-gray-200 rounded w-2/3 mb-3"></div>
                    <div class="h-7 bg-gray-200 rounded w-1/3"></div>
                </div>
            </template>
        </div>

        <div x-show="!statsLoading && stats" class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            {{-- Total Notifications --}}
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total Sent</p>
                <p class="text-2xl font-bold text-gray-900" x-text="stats ? stats.total_notifications.toLocaleString() : 0"></p>
                <p class="text-xs text-gray-400 mt-1">All time</p>
            </div>
            {{-- Today Sent --}}
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Today</p>
                <p class="text-2xl font-bold text-indigo-600" x-text="stats ? stats.today_sent.toLocaleString() : 0"></p>
                <p class="text-xs text-gray-400 mt-1">Sent today</p>
            </div>
            {{-- Unread --}}
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Unread</p>
                <p class="text-2xl font-bold text-amber-600" x-text="stats ? stats.unread_notifications.toLocaleString() : 0"></p>
                <p class="text-xs text-gray-400 mt-1">Not yet read</p>
            </div>
            {{-- Failed Jobs Total --}}
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Failed Jobs</p>
                <p class="text-2xl font-bold"
                   :class="stats && stats.total_failed_jobs > 0 ? 'text-red-600' : 'text-gray-900'"
                   x-text="stats ? stats.total_failed_jobs.toLocaleString() : 0"></p>
                <p class="text-xs text-gray-400 mt-1">In queue</p>
            </div>
            {{-- Recent Failed --}}
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Last 24h Failures</p>
                <p class="text-2xl font-bold"
                   :class="stats && stats.recent_failed_jobs > 0 ? 'text-red-600' : 'text-green-600'"
                   x-text="stats ? stats.recent_failed_jobs : 0"></p>
                <p class="text-xs text-gray-400 mt-1">Recent failures</p>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-bell text-indigo-500"></i> Notifications
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    View all sent notifications, track read status, and search by content.
                </p>
                <button @click="switchTab('notifications')" class="text-sm text-indigo-600 font-medium hover:underline">
                    View Notifications →
                </button>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-triangle-exclamation text-red-500"></i> Failed Jobs
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Inspect failed queue jobs, view exception details, and retry or discard them.
                </p>
                <button @click="switchTab('failed')" class="text-sm text-indigo-600 font-medium hover:underline">
                    View Failed Jobs →
                </button>
            </div>

            @can('notification.send')
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 md:col-span-2">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-paper-plane text-green-500"></i> Manual Broadcast
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Send in-app or email notifications to all users, specific roles, or individual customers.
                </p>
                <button @click="switchTab('send')" class="text-sm text-indigo-600 font-medium hover:underline">
                    Compose & Send →
                </button>
            </div>
            @endcan
        </div>
    </div>

    {{-- ─────────────────────────────────────────────── --}}
    {{-- TAB: Sent Notifications                         --}}
    {{-- ─────────────────────────────────────────────── --}}
    <div x-show="tab === 'notifications'">
        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                <input x-model="notifSearch" @input.debounce.400ms="notifPage = 1; loadNotifications()"
                       type="text" placeholder="Search by subject or message…"
                       class="pl-9 pr-4 py-2 w-full text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <select x-model="notifRead" @change="notifPage = 1; loadNotifications()"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                <option value="1">Read</option>
                <option value="0">Unread</option>
            </select>
            <button @click="notifSearch = ''; notifRead = ''; notifPage = 1; loadNotifications()"
                    class="text-sm text-gray-500 hover:text-gray-700 px-3">
                <i class="fas fa-rotate-left mr-1"></i> Reset
            </button>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div x-show="notifLoading" class="p-8 flex justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <table x-show="!notifLoading" class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Recipient</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Subject</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Sent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="n in notifications" :key="n.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-user text-indigo-500 text-xs"></i>
                                    </div>
                                    <span class="text-sm text-gray-700" x-text="n.recipient"></span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 max-w-xs">
                                <p class="text-sm font-medium text-gray-800 truncate" x-text="n.subject || '(no subject)'"></p>
                                <p class="text-xs text-gray-400 truncate mt-0.5" x-text="n.message || ''"></p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-mono"
                                      x-text="n.type"></span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span :class="n.read_at ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                                      class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium">
                                    <span :class="n.read_at ? 'bg-green-500' : 'bg-amber-500'"
                                          class="w-1.5 h-1.5 rounded-full"></span>
                                    <span x-text="n.read_at ? 'Read' : 'Unread'"></span>
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-gray-500" x-text="formatDate(n.created_at)"></td>
                        </tr>
                    </template>
                    <tr x-show="!notifLoading && notifications.length === 0">
                        <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                            <i class="fas fa-bell-slash text-3xl mb-3 block"></i>
                            No notifications found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div x-show="notifMeta.last_page > 1" class="flex items-center justify-between mt-4">
            <p class="text-sm text-gray-500">
                Page <span x-text="notifMeta.current_page"></span> of <span x-text="notifMeta.last_page"></span>
                &nbsp;·&nbsp; <span x-text="notifMeta.total?.toLocaleString()"></span> total
            </p>
            <div class="flex gap-2">
                <button @click="notifPage--; loadNotifications()" :disabled="notifPage <= 1"
                        class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
                <button @click="notifPage++; loadNotifications()" :disabled="notifPage >= notifMeta.last_page"
                        class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ─────────────────────────────────────────────── --}}
    {{-- TAB: Failed Jobs                                --}}
    {{-- ─────────────────────────────────────────────── --}}
    <div x-show="tab === 'failed'">
        {{-- Toolbar --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex flex-wrap gap-3 items-center">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                <input x-model="failedSearch" @input.debounce.400ms="failedPage = 1; loadFailedJobs()"
                       type="text" placeholder="Search job name, queue, exception…"
                       class="pl-9 pr-4 py-2 w-full text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <select x-model="failedQueue" @change="failedPage = 1; loadFailedJobs()"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                <option value="">All Queues</option>
                <template x-for="q in failedQueues" :key="q">
                    <option :value="q" x-text="q"></option>
                </template>
            </select>
            <button @click="failedSearch = ''; failedQueue = ''; failedPage = 1; loadFailedJobs()"
                    class="text-sm text-gray-500 hover:text-gray-700 px-2">
                <i class="fas fa-rotate-left mr-1"></i> Reset
            </button>

            @can('notification.manage')
            <div class="ml-auto flex gap-2">
                <button @click="confirmRetryAll()"
                        :disabled="failedMeta.total === 0"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 disabled:opacity-40 transition">
                    <i class="fas fa-rotate-right text-xs"></i> Retry All
                </button>
            </div>
            @endcan
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div x-show="failedLoading" class="p-8 flex justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-500"></div>
            </div>

            <table x-show="!failedLoading" class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Job</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Queue</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Exception</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Failed At</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="job in failedJobs" :key="job.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-medium text-gray-800" x-text="job.display_name"></p>
                                <p class="text-xs text-gray-400 font-mono mt-0.5" x-text="job.uuid.substring(0,13) + '…'"></p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium"
                                      x-text="job.queue"></span>
                            </td>
                            <td class="px-5 py-3.5 max-w-xs">
                                <p class="text-xs text-red-600 font-mono truncate" x-text="job.exception_short.split('\n')[0]"></p>
                                <button @click="previewJob = job"
                                        class="text-xs text-indigo-500 hover:underline mt-0.5">
                                    View full trace
                                </button>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-gray-500" x-text="formatDate(job.failed_at)"></td>
                            <td class="px-5 py-3.5 text-right">
                                @can('notification.manage')
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="retryJob(job.uuid)"
                                            :disabled="retryingJob === job.uuid"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 disabled:opacity-50 transition">
                                        <i class="fas fa-rotate-right text-xs"
                                           :class="retryingJob === job.uuid ? 'animate-spin' : ''"></i>
                                        Retry
                                    </button>
                                    <button @click="deleteJobData = job; showDeleteJobModal = true"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 transition">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                                @endcan
                                @cannot('notification.manage')
                                <span class="text-xs text-gray-400">View only</span>
                                @endcannot
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!failedLoading && failedJobs.length === 0">
                        <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                            <i class="fas fa-circle-check text-3xl text-green-400 mb-3 block"></i>
                            No failed jobs — queue is healthy!
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div x-show="failedMeta.last_page > 1" class="flex items-center justify-between mt-4">
            <p class="text-sm text-gray-500">
                Page <span x-text="failedMeta.current_page"></span> of <span x-text="failedMeta.last_page"></span>
                &nbsp;·&nbsp; <span x-text="failedMeta.total"></span> total
            </p>
            <div class="flex gap-2">
                <button @click="failedPage--; loadFailedJobs()" :disabled="failedPage <= 1"
                        class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
                <button @click="failedPage++; loadFailedJobs()" :disabled="failedPage >= failedMeta.last_page"
                        class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ─────────────────────────────────────────────── --}}
    {{-- TAB: Send Notification                          --}}
    {{-- ─────────────────────────────────────────────── --}}
    @can('notification.send')
    <div x-show="tab === 'send'">
        <div class="max-w-2xl">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-5">Compose Broadcast</h2>

                {{-- Success banner --}}
                <div x-show="sendSuccess" x-transition
                     class="mb-4 p-3.5 bg-green-50 border border-green-200 rounded-lg flex items-start gap-2">
                    <i class="fas fa-circle-check text-green-600 mt-0.5"></i>
                    <p class="text-sm text-green-800" x-text="sendSuccess"></p>
                </div>

                {{-- Error banner --}}
                <div x-show="sendError" x-transition
                     class="mb-4 p-3.5 bg-red-50 border border-red-200 rounded-lg flex items-start gap-2">
                    <i class="fas fa-circle-xmark text-red-600 mt-0.5"></i>
                    <p class="text-sm text-red-800" x-text="sendError"></p>
                </div>

                <form @submit.prevent="sendNotification" class="space-y-5">

                    {{-- Recipients --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <template x-for="opt in [{v:'all',l:'All Active Users'},{v:'role',l:'By Role'},{v:'specific',l:'Specific IDs'}]" :key="opt.v">
                                <button type="button"
                                        @click="sendForm.recipient_type = opt.v; sendForm.recipient_ids = []"
                                        :class="sendForm.recipient_type === opt.v
                                            ? 'bg-indigo-600 text-white border-indigo-600'
                                            : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-400'"
                                        class="px-3 py-1.5 text-sm border rounded-lg transition font-medium"
                                        x-text="opt.l">
                                </button>
                            </template>
                        </div>

                        {{-- Role multi-select --}}
                        <div x-show="sendForm.recipient_type === 'role'" class="space-y-1.5">
                            <p class="text-xs text-gray-500 mb-2">Select one or more roles:</p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="role in availableRoles" :key="role">
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                        <input type="checkbox"
                                               :value="role"
                                               x-model="sendForm.recipient_ids"
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700" x-text="role"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- Specific user IDs --}}
                        <div x-show="sendForm.recipient_type === 'specific'">
                            <input x-model="recipientIdsText"
                                   @input="sendForm.recipient_ids = recipientIdsText.split(',').map(s => s.trim()).filter(Boolean)"
                                   type="text" placeholder="e.g. 1, 5, 42 (comma-separated user IDs)"
                                   class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <p class="text-xs text-gray-400 mt-1">Enter user IDs separated by commas</p>
                        </div>
                    </div>

                    {{-- Channels --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Channels</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="database" x-model="sendForm.channels"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700 flex items-center gap-1.5">
                                    <i class="fas fa-bell text-indigo-500 text-xs"></i> In-App
                                </span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="mail" x-model="sendForm.channels"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700 flex items-center gap-1.5">
                                    <i class="fas fa-envelope text-indigo-500 text-xs"></i> Email
                                </span>
                            </label>
                        </div>
                        <p x-show="sendForm.channels.length === 0" class="text-xs text-red-500 mt-1">
                            Select at least one channel.
                        </p>
                    </div>

                    {{-- Subject --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input x-model="sendForm.subject" type="text" maxlength="200"
                               placeholder="Notification subject…"
                               class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <p class="text-xs text-gray-400 mt-1 text-right" x-text="(sendForm.subject?.length || 0) + '/200'"></p>
                    </div>

                    {{-- Message --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea x-model="sendForm.message" rows="5" maxlength="5000"
                                  placeholder="Write your message here…"
                                  class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"></textarea>
                        <p class="text-xs text-gray-400 mt-1 text-right" x-text="(sendForm.message?.length || 0) + '/5000'"></p>
                    </div>

                    {{-- Preview summary --}}
                    <div x-show="sendForm.subject || sendForm.message"
                         class="p-4 bg-gray-50 rounded-lg border border-gray-200 text-sm">
                        <p class="font-medium text-gray-700 mb-1">Preview</p>
                        <p class="text-gray-800 font-semibold" x-text="sendForm.subject || '(no subject)'"></p>
                        <p class="text-gray-600 mt-1 text-xs leading-relaxed" x-text="sendForm.message"></p>
                        <div class="flex gap-3 mt-2">
                            <span class="text-xs text-gray-400">
                                To:
                                <span class="font-medium text-gray-600"
                                      x-text="sendForm.recipient_type === 'all' ? 'All active users'
                                           : sendForm.recipient_type === 'role' ? (sendForm.recipient_ids.join(', ') || '(select roles)')
                                           : 'User IDs: ' + (sendForm.recipient_ids.join(', ') || '(none)')">
                                </span>
                            </span>
                            <span class="text-xs text-gray-400">Via:
                                <span class="font-medium text-gray-600"
                                      x-text="sendForm.channels.map(c => c === 'mail' ? 'Email' : 'In-App').join(', ') || '(none)'"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center justify-between pt-1">
                        <button type="button" @click="resetSendForm()"
                                class="text-sm text-gray-500 hover:text-gray-700">
                            <i class="fas fa-rotate-left mr-1"></i> Clear form
                        </button>
                        <button type="submit"
                                :disabled="sendLoading || sendForm.channels.length === 0 || !sendForm.subject || !sendForm.message"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <i class="fas fa-paper-plane" :class="sendLoading ? 'animate-pulse' : ''"></i>
                            <span x-text="sendLoading ? 'Sending…' : 'Send Notification'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- ─────────────────────────────────────────────── --}}
    {{-- Exception Trace Modal                           --}}
    {{-- ─────────────────────────────────────────────── --}}
    <div x-show="previewJob" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div @click.outside="previewJob = null" x-show="previewJob"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[80vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-semibold text-gray-900" x-text="previewJob?.display_name"></h3>
                    <p class="text-xs text-gray-500 mt-0.5">Failed at:
                        <span x-text="previewJob ? formatDate(previewJob.failed_at) : ''"></span>
                        &nbsp;·&nbsp; Queue: <span x-text="previewJob?.queue"></span>
                    </p>
                </div>
                <button @click="previewJob = null" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div class="overflow-y-auto p-6 flex-1">
                <pre class="text-xs text-gray-700 font-mono whitespace-pre-wrap leading-relaxed bg-gray-50 p-4 rounded-lg border border-gray-200"
                     x-text="previewJob?.exception_full"></pre>
            </div>
            <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-100">
                @can('notification.manage')
                <button @click="retryJob(previewJob.uuid); previewJob = null"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                    <i class="fas fa-rotate-right text-xs"></i> Retry
                </button>
                <button @click="deleteJobData = previewJob; previewJob = null; showDeleteJobModal = true"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 transition">
                    <i class="fas fa-trash text-xs"></i> Delete
                </button>
                @endcan
                <button @click="previewJob = null"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- Delete Job Confirmation Modal --}}
    <div x-show="showDeleteJobModal" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div @click.outside="showDeleteJobModal = false; deleteJobData = null"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-trash text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Delete Failed Job?</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        This will permanently remove
                        <span class="font-medium text-gray-700" x-text="deleteJobData?.display_name"></span>
                        from the failed jobs list.
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button @click="showDeleteJobModal = false; deleteJobData = null"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button @click="deleteJob(deleteJobData.uuid)"
                        :disabled="deletingJob === deleteJobData?.uuid"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 transition">
                    <i class="fas fa-trash mr-1"></i>
                    <span x-text="deletingJob === deleteJobData?.uuid ? 'Deleting…' : 'Delete'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Retry All Confirmation --}}
    <div x-show="showRetryAllModal" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div @click.outside="showRetryAllModal = false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-rotate-right text-amber-600"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Retry All Failed Jobs?</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        All <span class="font-medium text-gray-700" x-text="failedMeta.total"></span>
                        failed job(s) will be re-queued for processing.
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button @click="showRetryAllModal = false"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button @click="retryAllJobs()"
                        :disabled="retryingAll"
                        class="px-4 py-2 text-sm bg-amber-600 text-white rounded-lg hover:bg-amber-700 disabled:opacity-50 transition">
                    <i class="fas fa-rotate-right mr-1" :class="retryingAll ? 'animate-spin' : ''"></i>
                    <span x-text="retryingAll ? 'Retrying…' : 'Retry All'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function notificationCenter() {
    return {
        tab: 'overview',

        // Stats
        stats: null,
        statsLoading: false,

        // Notifications tab
        notifications: [],
        notifPage: 1,
        notifMeta: {},
        notifLoading: false,
        notifSearch: '',
        notifRead: '',

        // Failed jobs tab
        failedJobs: [],
        failedPage: 1,
        failedMeta: {},
        failedLoading: false,
        failedSearch: '',
        failedQueue: '',
        failedQueues: [],

        // Send tab
        sendForm: {
            recipient_type: 'all',
            recipient_ids: [],
            channels: ['database'],
            subject: '',
            message: '',
        },
        recipientIdsText: '',
        availableRoles: ['Admin', 'Order Manager', 'Inventory Clerk', 'Marketing', 'Customer Support'],
        sendLoading: false,
        sendSuccess: null,
        sendError: null,

        // Modals
        previewJob: null,
        showDeleteJobModal: false,
        deleteJobData: null,
        deletingJob: null,
        retryingJob: null,
        showRetryAllModal: false,
        retryingAll: false,

        async init() {
            await this.loadStats();
        },

        switchTab(tab) {
            this.tab = tab;
            this.sendSuccess = null;
            this.sendError = null;
            if (tab === 'notifications' && this.notifications.length === 0) {
                this.loadNotifications();
            } else if (tab === 'failed' && this.failedJobs.length === 0) {
                this.loadFailedJobs();
            }
        },

        // ── Stats ────────────────────────────────────────────────────────────

        async loadStats() {
            this.statsLoading = true;
            try {
                const r = await fetch('/api/v1/admin/notifications/stats', {
                    headers: this.authHeaders(),
                });
                const data = await r.json();
                if (data.success) this.stats = data.data;
            } catch (e) {
                console.error('Failed to load stats', e);
            } finally {
                this.statsLoading = false;
            }
        },

        // ── Notifications ────────────────────────────────────────────────────

        async loadNotifications() {
            this.notifLoading = true;
            try {
                const params = new URLSearchParams({
                    page: this.notifPage,
                    ...(this.notifSearch && { q: this.notifSearch }),
                    ...(this.notifRead !== '' && { read: this.notifRead }),
                });
                const r = await fetch(`/api/v1/admin/notifications?${params}`, {
                    headers: this.authHeaders(),
                });
                const data = await r.json();
                if (data.success) {
                    this.notifications = data.data.data;
                    this.notifMeta    = data.data.meta;
                }
            } catch (e) {
                console.error('Failed to load notifications', e);
            } finally {
                this.notifLoading = false;
            }
        },

        // ── Failed Jobs ──────────────────────────────────────────────────────

        async loadFailedJobs() {
            this.failedLoading = true;
            try {
                const params = new URLSearchParams({
                    page: this.failedPage,
                    ...(this.failedSearch && { q: this.failedSearch }),
                    ...(this.failedQueue  && { queue: this.failedQueue }),
                });
                const r = await fetch(`/api/v1/admin/notifications/failed-jobs?${params}`, {
                    headers: this.authHeaders(),
                });
                const data = await r.json();
                if (data.success) {
                    this.failedJobs   = data.data.data;
                    this.failedMeta   = data.data.meta;
                    this.failedQueues = data.data.queues ?? [];
                    // Refresh stats badge
                    await this.loadStats();
                }
            } catch (e) {
                console.error('Failed to load failed jobs', e);
            } finally {
                this.failedLoading = false;
            }
        },

        async retryJob(uuid) {
            this.retryingJob = uuid;
            try {
                const r = await fetch(`/api/v1/admin/notifications/failed-jobs/${uuid}/retry`, {
                    method: 'POST',
                    headers: this.authHeaders(),
                });
                const data = await r.json();
                if (data.success) {
                    this.failedJobs = this.failedJobs.filter(j => j.uuid !== uuid);
                    this.failedMeta.total = Math.max(0, (this.failedMeta.total || 1) - 1);
                    this.showToast('Job queued for retry', 'success');
                    await this.loadStats();
                } else {
                    this.showToast(data.message || 'Retry failed', 'error');
                }
            } catch (e) {
                this.showToast('Network error', 'error');
            } finally {
                this.retryingJob = null;
            }
        },

        confirmRetryAll() {
            if (this.failedMeta.total > 0) {
                this.showRetryAllModal = true;
            }
        },

        async retryAllJobs() {
            this.retryingAll = true;
            try {
                const r = await fetch('/api/v1/admin/notifications/failed-jobs/retry-all', {
                    method: 'POST',
                    headers: this.authHeaders(),
                });
                const data = await r.json();
                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showRetryAllModal = false;
                    this.failedPage = 1;
                    await this.loadFailedJobs();
                } else {
                    this.showToast(data.message || 'Failed to retry all', 'error');
                    this.showRetryAllModal = false;
                }
            } catch (e) {
                this.showToast('Network error', 'error');
                this.showRetryAllModal = false;
            } finally {
                this.retryingAll = false;
            }
        },

        async deleteJob(uuid) {
            this.deletingJob = uuid;
            try {
                const r = await fetch(`/api/v1/admin/notifications/failed-jobs/${uuid}`, {
                    method: 'DELETE',
                    headers: this.authHeaders(),
                });
                const data = await r.json();
                if (data.success) {
                    this.failedJobs = this.failedJobs.filter(j => j.uuid !== uuid);
                    this.failedMeta.total = Math.max(0, (this.failedMeta.total || 1) - 1);
                    this.showDeleteJobModal = false;
                    this.deleteJobData = null;
                    this.showToast('Failed job deleted', 'success');
                    await this.loadStats();
                } else {
                    this.showToast(data.message || 'Delete failed', 'error');
                }
            } catch (e) {
                this.showToast('Network error', 'error');
            } finally {
                this.deletingJob = null;
            }
        },

        // ── Send Notification ────────────────────────────────────────────────

        async sendNotification() {
            if (this.sendForm.channels.length === 0 || !this.sendForm.subject || !this.sendForm.message) return;

            this.sendLoading = true;
            this.sendSuccess = null;
            this.sendError   = null;

            try {
                const r = await fetch('/api/v1/admin/notifications/send', {
                    method: 'POST',
                    headers: {
                        ...this.authHeaders(),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(this.sendForm),
                });
                const data = await r.json();
                if (r.ok && data.success) {
                    this.sendSuccess = data.message;
                    this.resetSendForm();
                    await this.loadStats();
                } else {
                    this.sendError = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Failed to send');
                }
            } catch (e) {
                this.sendError = 'Network error. Please try again.';
            } finally {
                this.sendLoading = false;
            }
        },

        resetSendForm() {
            this.sendForm = {
                recipient_type: 'all',
                recipient_ids: [],
                channels: ['database'],
                subject: '',
                message: '',
            };
            this.recipientIdsText = '';
        },

        // ── Helpers ──────────────────────────────────────────────────────────

        authHeaders() {
            return {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            };
        },

        formatDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleString('en-GB', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        },

        showToast(msg, type = 'success') {
            const div = document.createElement('div');
            div.className = `fixed bottom-5 right-5 z-[9999] px-4 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2 transition
                ${type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`;
            div.innerHTML = `<i class="fas ${type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${msg}`;
            document.body.appendChild(div);
            setTimeout(() => div.remove(), 3500);
        },
    };
}
</script>
@endpush
