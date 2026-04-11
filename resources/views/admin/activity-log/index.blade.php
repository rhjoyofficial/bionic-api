@extends('layouts.admin')

@section('title', 'Activity Log')

@section('content')
    <div class="bg-white border border-gray-200 rounded-xl">
        <div class="p-4 border-b border-gray-100 flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
            <h2 class="text-sm font-semibold text-gray-700">System Activity</h2>

            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search description/log"
                    class="w-52 rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600">
                <select name="log"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-600 cursor-pointer">
                    <option value="">All Logs</option>
                    <option value="admin-auth" @selected(request('log') === 'admin-auth')>Admin Auth</option>
                    <option value="default" @selected(request('log') === 'default')>Default</option>
                </select>
                <button type="submit"
                    class="rounded-lg bg-green-700 text-white px-3 py-2 text-sm font-medium hover:bg-green-800 cursor-pointer transition">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="w-40 px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">When</th>
                <th class="w-24 px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Log</th>
                <th class="w-64 px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                <th class="w-32 px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Properties</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($activities as $activity)
                <tr>
                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                        {{ $activity->created_at?->format('Y-m-d H:i:s') }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                            {{ $activity->log_name ?: 'default' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-800">{{ $activity->description }}</td>
                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                        {{ $activity->causer?->name ?? 'System' }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 min-w-0 break-all">
                        @if (!empty($activity->properties))
                            <code class="text-xs">{{ json_encode($activity->properties) }}</code>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">No activity found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

        <div class="p-4 border-t border-gray-100">
            {{ $activities->links() }}
        </div>
    </div>
@endsection
