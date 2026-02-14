<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared Reports</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
@include('layouts.navigation')

<div class="max-w-[1400px] mx-auto px-6 py-8">

    {{-- HEADER --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <a href="/analytics/{{ $propertyId }}" class="text-gray-400 hover:text-blue-500 transition-colors" title="Back to dashboard">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Shared Reports</h1>
            </div>
            <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-400">
                <span class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    GA4 Property {{ $propertyId }}
                </span>
            </div>
        </div>
    </div>

    {{-- SUCCESS FLASH --}}
    @if(session('success'))
        <div class="mb-6 px-4 py-3 rounded-lg text-sm bg-green-50 text-green-800 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- SHARES TABLE --}}
    <div class="bg-white rounded-lg border border-gray-200 p-5">
        @if($shares->isEmpty())
            <div class="py-16 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"/></svg>
                <p class="text-gray-500 font-medium">No shared reports yet</p>
                <p class="text-gray-400 text-sm mt-1">Create a share from the dashboard using the Share button.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left">
                            <th class="py-3 px-3 text-gray-500 font-medium">Label</th>
                            <th class="py-3 px-3 text-gray-500 font-medium">Widget</th>
                            <th class="py-3 px-3 text-gray-500 font-medium">Date Range</th>
                            <th class="py-3 px-3 text-gray-500 font-medium">Status</th>
                            <th class="py-3 px-3 text-gray-500 font-medium">Created</th>
                            <th class="py-3 px-3 text-gray-500 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shares as $share)
                            @php
                                $widgetLabels = [
                                    'full_dashboard' => 'Full Dashboard',
                                    'overview_chart' => 'Overview Chart',
                                    'stat_cards' => 'Stat Cards',
                                    'devices' => 'Device Breakdown',
                                    'countries' => 'Top Countries',
                                    'events' => 'Events',
                                    'traffic_sources' => 'Traffic Sources',
                                    'top_pages' => 'Top Pages',
                                    'pages_report' => 'Pages Report',
                                ];
                                $rangeLabels = [
                                    'today' => 'Today',
                                    'yesterday' => 'Yesterday',
                                    '7days' => 'Last 7 days',
                                    '14days' => 'Last 14 days',
                                    '30days' => 'Last 30 days',
                                    '90days' => 'Last 90 days',
                                    'this_month' => 'This month',
                                    'last_month' => 'Last month',
                                    '6months' => 'Last 6 months',
                                    '12months' => 'Last 12 months',
                                    'this_year' => 'This year',
                                ];

                                if (!$share->is_active) {
                                    $status = 'Revoked';
                                    $statusClass = 'bg-gray-100 text-gray-600';
                                } elseif ($share->expires_at && $share->expires_at->isPast()) {
                                    $status = 'Expired';
                                    $statusClass = 'bg-amber-50 text-amber-700';
                                } else {
                                    $status = 'Active';
                                    $statusClass = 'bg-green-50 text-green-700';
                                }

                                $embedUrl = url('/embed/' . $share->token);
                            @endphp
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors" x-data="{ copied: false, confirmRevoke: false }">
                                <td class="py-3 px-3 text-gray-900 font-medium">
                                    {{ $share->label ?: '—' }}
                                </td>
                                <td class="py-3 px-3 text-gray-600">
                                    {{ $widgetLabels[$share->widget_type] ?? $share->widget_type }}
                                </td>
                                <td class="py-3 px-3 text-gray-600">
                                    {{ $rangeLabels[$share->date_range] ?? $share->date_range }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-gray-500 text-xs">
                                    {{ $share->created_at->format('M j, Y g:ia') }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Copy embed URL --}}
                                        <button
                                            @click="
                                                const t = document.createElement('textarea');
                                                t.value = '{{ $embedUrl }}';
                                                t.style.position = 'fixed';
                                                t.style.opacity = '0';
                                                document.body.appendChild(t);
                                                t.select();
                                                document.execCommand('copy');
                                                document.body.removeChild(t);
                                                copied = true;
                                                setTimeout(() => copied = false, 2000);
                                            "
                                            class="px-2.5 py-1.5 text-xs font-medium rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                                            <span x-text="copied ? 'Copied!' : 'Copy URL'"></span>
                                        </button>

                                        {{-- Revoke --}}
                                        @if($share->is_active)
                                            <template x-if="!confirmRevoke">
                                                <button @click="confirmRevoke = true"
                                                        class="px-2.5 py-1.5 text-xs font-medium rounded-md border border-red-200 text-red-600 hover:bg-red-50 transition-colors">
                                                    Revoke
                                                </button>
                                            </template>
                                            <template x-if="confirmRevoke">
                                                <form method="POST" action="/shares/{{ $share->id }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="px-2.5 py-1.5 text-xs font-medium rounded-md bg-red-600 text-white hover:bg-red-700 transition-colors">
                                                        Confirm Revoke
                                                    </button>
                                                </form>
                                            </template>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

</body>
</html>
