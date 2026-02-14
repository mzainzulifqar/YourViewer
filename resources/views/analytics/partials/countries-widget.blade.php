<div class="bg-white rounded-lg border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-gray-900">Top Countries</h2>
        <span class="group relative cursor-help">
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
            <span class="absolute bottom-full right-0 mb-2 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg w-48 leading-relaxed opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity z-50 shadow-lg">Sessions by country</span>
        </span>
    </div>
    @php $geoSessions = array_column($geo['rows'], 'sessions'); $maxGeo = !empty($geoSessions) ? max($geoSessions) : 1; @endphp
    <div class="space-y-2">
        @forelse(array_slice($geo['rows'], 0, 8) as $country)
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-700">{{ $country['country'] }}</span>
                <div class="flex items-center gap-2">
                    <div class="w-20 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-400 rounded-full" style="width: {{ ($country['sessions'] / $maxGeo) * 100 }}%"></div>
                    </div>
                    <span class="font-semibold text-gray-900 w-10 text-right">{{ number_format($country['sessions']) }}</span>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400 py-4 text-center">No geographic data yet. Trends will appear as visits increase.</p>
        @endforelse
    </div>
</div>
