<div class="bg-white rounded-lg border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-gray-900">Events</h2>
        <span class="group relative cursor-help">
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
            <span class="absolute bottom-full right-0 mb-2 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg w-48 leading-relaxed opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity z-50 shadow-lg">User interactions tracked as events</span>
        </span>
    </div>
    <div class="space-y-2">
        @forelse(array_slice($events['rows'], 0, 8) as $event)
            <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-50 last:border-0">
                <span class="text-gray-700">{{ $event['eventName'] }}</span>
                <span class="font-semibold text-gray-900">{{ number_format($event['eventCount']) }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-400 py-4 text-center">No events recorded yet. Events will appear once users interact with your site.</p>
        @endforelse
    </div>
</div>
