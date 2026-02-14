<div class="bg-white rounded-lg border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-gray-900">Device Breakdown</h2>
        <span class="group relative cursor-help">
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
            <span class="absolute bottom-full right-0 mb-2 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg w-48 leading-relaxed opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity z-50 shadow-lg">Traffic split by device type</span>
        </span>
    </div>
    @php $totalDeviceSessions = array_sum(array_column($devices['rows'], 'sessions')) ?: 1; @endphp
    @if(!empty($devices['rows']))
        <div class="flex items-center gap-6">
            <div class="w-36 h-36 shrink-0">
                <canvas id="devicesDonut"></canvas>
            </div>
            <div class="space-y-3 text-sm">
                @php $deviceColors = ['desktop' => '#3b82f6', 'tablet' => '#93c5fd', 'mobile' => '#bfdbfe']; @endphp
                @foreach($devices['rows'] as $device)
                    @php $pct = round(($device['sessions'] / $totalDeviceSessions) * 100); @endphp
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $deviceColors[strtolower($device['deviceCategory'])] ?? '#d1d5db' }}"></span>
                        <span class="text-gray-600">{{ ucfirst($device['deviceCategory']) }}</span>
                        <span class="font-bold text-gray-900 ml-auto">{{ $pct }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="text-sm text-gray-400 py-4 text-center">No device data yet.</p>
    @endif
</div>
