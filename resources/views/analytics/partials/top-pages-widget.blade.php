<div class="bg-white rounded-lg border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <a href="/analytics/{{ $propertyId }}/pages?range={{ $range ?? '30days' }}" class="text-base font-bold text-gray-900 hover:text-blue-600 transition-colors">Top Pages</a>
        <a href="/analytics/{{ $propertyId }}/pages?range={{ $range ?? '30days' }}" class="flex items-center gap-1 text-xs text-blue-500 hover:text-blue-700 font-medium transition-colors">
            View all
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        </a>
    </div>
    <div class="space-y-0">
        @forelse(array_slice($topPages['rows'], 0, 10) as $i => $page)
            <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded cursor-pointer transition-colors" title="{{ $page['pageTitle'] }}">
                <span class="text-xs text-gray-400 w-5 text-right shrink-0">{{ $i + 1 }}.</span>
                <span class="text-sm text-gray-700 truncate">{{ $page['pagePath'] }}</span>
                <span class="text-sm font-semibold text-gray-900 ml-auto shrink-0">{{ number_format($page['screenPageViews']) }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-400 py-4 text-center">No page data yet.</p>
        @endforelse
    </div>
</div>
