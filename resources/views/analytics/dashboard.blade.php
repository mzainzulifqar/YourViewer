<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Overview</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
@include('layouts.navigation')

<div class="max-w-[1400px] mx-auto px-6 py-8">

    {{-- ════════════════════════════════════════════════════════════════
         HEADER
    ════════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Analytics Overview</h1>
            <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-400">
                <span class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    GA4 Property {{ $propertyId }}
                </span>
                <span class="text-gray-300">|</span>
                <span>Timezone UTC</span>
                <span class="text-gray-300">|</span>
                <span>Data cached up to 15 min</span>
            </div>
        </div>

        {{-- Date range pills + Share --}}
        <div class="flex items-center gap-2" x-data="{ open: false, customStart: '{{ $customStart ?? '' }}', customEnd: '{{ $customEnd ?? '' }}', applying: false }">
            @foreach(['7days' => 'Last 7 days', '30days' => 'Last 30 days'] as $key => $label)
                <a href="?range={{ $key }}"
                   class="px-4 py-2 text-sm rounded-md border transition-colors
                   {{ $range === $key ? 'bg-white border-blue-500 text-blue-600 font-semibold shadow-sm' : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300' }}">
                    {{ $label }}
                </a>
            @endforeach

            {{-- More ranges dropdown --}}
            <div class="relative">
                <button @click="open = !open"
                        class="px-4 py-2 text-sm rounded-md border transition-colors
                        {{ !in_array($range, ['7days','30days']) ? 'bg-blue-600 border-blue-600 text-white font-semibold shadow-sm' : 'bg-blue-600 border-blue-600 text-white hover:bg-blue-700' }}">
                    {{ !in_array($range, ['7days','30days']) ? $rangeLabel : 'Set Custom Date Range' }}
                </button>
                <div x-show="open" @click.outside="open = false" x-transition
                     class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-xl py-1 z-30 max-h-[28rem] overflow-y-auto">
                    <p class="px-4 py-1.5 text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Quick</p>
                    @foreach(['today' => 'Today', 'yesterday' => 'Yesterday'] as $key => $label)
                        <a href="?range={{ $key }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $range === $key ? 'text-blue-600 font-medium bg-blue-50' : 'text-gray-700' }}">{{ $label }}</a>
                    @endforeach
                    <div class="border-t border-gray-100 my-1"></div>
                    <p class="px-4 py-1.5 text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Rolling</p>
                    @foreach(['7days' => 'Last 7 days', '14days' => 'Last 14 days', '30days' => 'Last 30 days', '90days' => 'Last 90 days'] as $key => $label)
                        <a href="?range={{ $key }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $range === $key ? 'text-blue-600 font-medium bg-blue-50' : 'text-gray-700' }}">{{ $label }}</a>
                    @endforeach
                    <div class="border-t border-gray-100 my-1"></div>
                    <p class="px-4 py-1.5 text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Calendar</p>
                    @foreach(['this_month' => 'This month', 'last_month' => 'Last month', '6months' => 'Last 6 months', '12months' => 'Last 12 months', 'this_year' => 'This year'] as $key => $label)
                        <a href="?range={{ $key }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ $range === $key ? 'text-blue-600 font-medium bg-blue-50' : 'text-gray-700' }}">{{ $label }}</a>
                    @endforeach
                    <div class="border-t border-gray-100 my-1"></div>
                    <p class="px-4 py-1.5 text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Custom Range</p>
                    <div class="px-4 py-2 space-y-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Start date</label>
                            <input type="date" x-model="customStart" max="{{ date('Y-m-d') }}"
                                   class="w-full border border-gray-200 rounded-md px-2.5 py-1.5 text-sm focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">End date</label>
                            <input type="date" x-model="customEnd" max="{{ date('Y-m-d') }}"
                                   class="w-full border border-gray-200 rounded-md px-2.5 py-1.5 text-sm focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400">
                        </div>
                        <button @click="applying = true; window.location.href = '?range=custom&start=' + customStart + '&end=' + customEnd"
                                :disabled="!customStart || !customEnd || applying"
                                class="w-full py-1.5 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                            <span x-text="applying ? 'Applying...' : 'Apply'"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Share button --}}
            <button onclick="document.getElementById('shareModal').classList.remove('hidden')"
                    class="px-4 py-2 text-sm rounded-md border border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:text-gray-800 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"/></svg>
                Share
            </button>
        </div>
    </div>

    {{-- ── Insights Bar ──────────────────────────────────────────── --}}
    @if(!empty($insights))
        <div class="mb-6 space-y-2">
            @foreach($insights as $insight)
                @php
                    $bgMap = ['warning' => 'bg-amber-50 text-amber-800 border-amber-200', 'success' => 'bg-green-50 text-green-800 border-green-200', 'opportunity' => 'bg-purple-50 text-purple-800 border-purple-200', 'info' => 'bg-blue-50 text-blue-800 border-blue-200'];
                @endphp
                <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-lg text-sm border {{ $bgMap[$insight['type']] ?? $bgMap['info'] }}">
                    @if($insight['type'] === 'warning')
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.345 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    @elseif($insight['type'] === 'success')
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                    @elseif($insight['type'] === 'opportunity')
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a6 6 0 00-6 6c0 1.887.87 3.568 2.23 4.668A2 2 0 017 14.5V16a2 2 0 002 2h2a2 2 0 002-2v-1.5a2 2 0 01.77-1.832A6.001 6.001 0 0010 2zm-1 14v-1h2v1a1 1 0 01-1 1 1 1 0 01-1-1zm3.5-7.5a.5.5 0 00-1 0v1.72l-1.15-1.15a.5.5 0 00-.7.7L10.8 11H9.5a.5.5 0 000 1H12a.5.5 0 00.5-.5V8.5z" clip-rule="evenodd"/></svg>
                    @else
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                    @endif
                    {{ $insight['text'] }}
                </div>
            @endforeach
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         MAIN 3-COLUMN GRID
    ════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- ┌─────────────────────────────────────────────────────────┐
             │ LEFT COLUMN (5 cols) — Chart + 4 stat cards            │
             └─────────────────────────────────────────────────────────┘ --}}
        <div class="lg:col-span-5 space-y-5">

            {{-- Sessions / Pageviews chart with tabs --}}
            @include('analytics.partials.overview-chart')

            {{-- 4 Stat Cards (2x2) --}}
            @include('analytics.partials.stat-cards-left')
        </div>

        {{-- ┌─────────────────────────────────────────────────────────┐
             │ MIDDLE COLUMN (4 cols) — Devices + Geo + Events        │
             └─────────────────────────────────────────────────────────┘ --}}
        <div class="lg:col-span-4 space-y-5">

            {{-- Device Breakdown --}}
            @include('analytics.partials.devices-widget')

            {{-- Top Countries --}}
            @include('analytics.partials.countries-widget')

            {{-- Events --}}
            @include('analytics.partials.events-widget')
        </div>

        {{-- ┌─────────────────────────────────────────────────────────┐
             │ RIGHT COLUMN (3 cols) — Stat cards + Top Sources       │
             └─────────────────────────────────────────────────────────┘ --}}
        <div class="lg:col-span-3 space-y-5">

            {{-- 4 mini stat cards (2x2) --}}
            @include('analytics.partials.stat-cards-right')

            {{-- Top Traffic Sources --}}
            @include('analytics.partials.traffic-sources-widget')

            {{-- Top Pages compact --}}
            @include('analytics.partials.top-pages-widget')
        </div>

    </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     SHARE MODAL
════════════════════════════════════════════════════════════════ --}}
<div id="shareModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6" x-data="shareModal()" @click.stop>
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-gray-900">Share / Embed Report</h3>
            <button onclick="document.getElementById('shareModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Widget</label>
                <select x-model="widgetType" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400">
                    <option value="full_dashboard">Full Dashboard</option>
                    <option value="overview_chart">Overview Chart</option>
                    <option value="stat_cards">Stat Cards</option>
                    <option value="devices">Device Breakdown</option>
                    <option value="countries">Top Countries</option>
                    <option value="events">Events</option>
                    <option value="traffic_sources">Traffic Sources</option>
                    <option value="top_pages">Top Pages</option>
                    <option value="pages_report">Pages Report</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                <select x-model="dateRange" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="7days">Last 7 days</option>
                    <option value="14days">Last 14 days</option>
                    <option value="30days">Last 30 days</option>
                    <option value="90days">Last 90 days</option>
                    <option value="this_month">This month</option>
                    <option value="last_month">Last month</option>
                    <option value="6months">Last 6 months</option>
                    <option value="12months">Last 12 months</option>
                    <option value="this_year">This year</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Label <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="text" x-model="label" placeholder="e.g. Client dashboard" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expires at <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="datetime-local" x-model="expiresAt" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400">
            </div>

            <button @click="generate()" :disabled="loading"
                    class="w-full py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 cursor-pointer">
                <span x-show="!loading">Generate Embed Code</span>
                <span x-show="loading">Generating...</span>
            </button>

            <div x-show="iframeCode" x-transition class="mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Embed Code</label>
                <div class="relative">
                    <textarea x-ref="embedCode" x-text="iframeCode" readonly rows="3"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs font-mono bg-gray-50 focus:outline-none resize-none"></textarea>
                    <button @click="copyCode()" class="absolute top-2 right-2 px-2.5 py-1 bg-white border border-gray-200 rounded-md text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                    </button>
                </div>
            </div>

            <p x-show="error" x-text="error" class="text-sm text-red-500"></p>

            <div class="pt-3 border-t border-gray-100 mt-2">
                <a href="/analytics/{{ $propertyId }}/shares" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors">
                    Manage existing shares &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function shareModal() {
    return {
        widgetType: 'full_dashboard',
        dateRange: '{{ $range }}',
        label: '',
        expiresAt: '',
        loading: false,
        iframeCode: '',
        copied: false,
        error: '',

        async generate() {
            this.loading = true;
            this.error = '';
            this.iframeCode = '';
            try {
                const body = {
                    widget_type: this.widgetType,
                    date_range: this.dateRange,
                };
                if (this.label) body.label = this.label;
                if (this.expiresAt) body.expires_at = this.expiresAt;

                const res = await fetch('/analytics/{{ $propertyId }}/shares', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(body),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.error = data.message || 'Failed to create share.';
                } else {
                    this.iframeCode = data.iframe_snippet;
                }
            } catch (e) {
                this.error = 'Network error. Please try again.';
            } finally {
                this.loading = false;
            }
        },

        copyCode() {
            const textarea = this.$refs.embedCode;
            textarea.select();
            document.execCommand('copy');
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        },
    };
}
</script>

{{-- ════════════════════════════════════════════════════════════════
     CHARTS JS
════════════════════════════════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData = @json($overview['chart']);
    const fullDates = @json($overview['chart']['labels']);

    // Crosshair plugin
    const crosshairPlugin = {
        id: 'crosshair',
        afterDraw(chart) {
            if (chart.tooltip?._active?.length) {
                const x = chart.tooltip._active[0].element.x;
                const yAxis = chart.scales.y;
                const ctx = chart.ctx;
                ctx.save();
                ctx.beginPath();
                ctx.moveTo(x, yAxis.top);
                ctx.lineTo(x, yAxis.bottom);
                ctx.lineWidth = 1;
                ctx.strokeStyle = 'rgba(156,163,175,0.3)';
                ctx.setLineDash([4, 4]);
                ctx.stroke();
                ctx.restore();
            }
        }
    };

    const sharedOpts = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1f2937',
                titleFont: { size: 12, weight: '600' },
                bodyFont: { size: 12 },
                padding: { top: 10, bottom: 10, left: 14, right: 14 },
                cornerRadius: 8,
                displayColors: false,
                callbacks: {
                    title: function(items) {
                        return items[0].label;
                    },
                    label: function(item) {
                        return item.dataset.label + ': ' + item.formattedValue;
                    }
                }
            },
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af', maxRotation: 0 } },
            y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, color: '#9ca3af' } },
        },
        hover: { mode: 'index', intersect: false },
    };

    const singlePoint = chartData.labels.length <= 1;
    const lineStyle = {
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59,130,246,0.06)',
        fill: true,
        tension: 0.4,
        pointRadius: singlePoint ? 6 : 4,
        pointHoverRadius: 7,
        pointBackgroundColor: singlePoint ? '#3b82f6' : '#fff',
        pointBorderColor: '#3b82f6',
        pointBorderWidth: 2,
        pointHoverBorderWidth: 3,
        borderWidth: 2,
    };

    new Chart(document.getElementById('sessionsChart'), {
        type: 'line',
        data: { labels: chartData.labels, datasets: [{ ...lineStyle, label: 'Sessions', data: chartData.sessions }] },
        options: sharedOpts,
        plugins: [crosshairPlugin],
    });

    new Chart(document.getElementById('pageviewsChart'), {
        type: 'line',
        data: { labels: chartData.labels, datasets: [{ ...lineStyle, label: 'Pageviews', data: chartData.pageviews }] },
        options: sharedOpts,
        plugins: [crosshairPlugin],
    });

    // Devices donut
    const deviceRows = @json($devices['rows']);
    if (deviceRows.length && document.getElementById('devicesDonut')) new Chart(document.getElementById('devicesDonut'), {
        type: 'doughnut',
        data: {
            labels: deviceRows.map(r => r.deviceCategory.charAt(0).toUpperCase() + r.deviceCategory.slice(1)),
            datasets: [{
                data: deviceRows.map(r => parseInt(r.sessions)),
                backgroundColor: ['#3b82f6', '#93c5fd', '#bfdbfe'],
                borderWidth: 0,
            }]
        },
        options: {
            cutout: '60%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f2937',
                    cornerRadius: 8,
                    callbacks: {
                        label: function(item) {
                            const total = item.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = Math.round(item.raw / total * 100);
                            return item.label + ': ' + pct + '%';
                        }
                    }
                }
            },
        },
    });
});
</script>

</body>
</html>
