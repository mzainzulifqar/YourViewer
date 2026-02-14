<div class="bg-white rounded-lg border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-gray-900">Top Traffic Sources</h2>
        <span class="group relative cursor-help">
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
            <span class="absolute bottom-full right-0 mb-2 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg w-48 leading-relaxed opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity z-50 shadow-lg">Where your visitors come from</span>
        </span>
    </div>
    <div class="space-y-0">
        @forelse(array_slice($traffic['rows'], 0, 10) as $i => $src)
            <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded cursor-pointer transition-colors">
                <span class="text-xs text-gray-400 w-5 text-right shrink-0">{{ $i + 1 }}.</span>
                <div class="w-6 h-6 bg-gray-100 rounded flex items-center justify-center shrink-0">
                    <span class="text-[10px] font-bold text-gray-400 uppercase">{{ strtoupper(substr($src['sessionSource'], 0, 2)) }}</span>
                </div>
                <span class="text-sm text-gray-700 truncate">{{ $src['sessionSource'] }}{{ $src['sessionMedium'] !== '(none)' ? ' / ' . $src['sessionMedium'] : '' }}</span>
                <svg class="w-4 h-4 text-gray-300 ml-auto shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </div>
        @empty
            <p class="text-sm text-gray-400 py-4 text-center">No traffic source data yet.</p>
        @endforelse
    </div>
</div>
