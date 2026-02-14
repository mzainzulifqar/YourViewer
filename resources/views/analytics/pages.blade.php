<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pages & Screens</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
@include('layouts.navigation')

<div class="max-w-[1400px] mx-auto px-6 py-8">

    {{-- HEADER --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <a href="/analytics/{{ $propertyId }}?range={{ $range }}{{ $range === 'custom' && $customStart && $customEnd ? '&start='.$customStart.'&end='.$customEnd : '' }}" class="text-gray-400 hover:text-blue-500 transition-colors" title="Back to overview">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Pages & Screens</h1>
            </div>
            <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-400">
                <span class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    GA4 Property {{ $propertyId }}
                </span>
                <span class="text-gray-300">|</span>
                <span>Data cached up to 15 min</span>
            </div>
        </div>

        {{-- Date range pills --}}
        <div class="flex items-center gap-2" x-data="{ open: false, customStart: '{{ $customStart ?? '' }}', customEnd: '{{ $customEnd ?? '' }}', applying: false }">
            @foreach(['7days' => 'Last 7 days', '30days' => 'Last 30 days'] as $key => $label)
                <a href="?range={{ $key }}"
                   class="px-4 py-2 text-sm rounded-md border transition-colors
                   {{ $range === $key ? 'bg-white border-blue-500 text-blue-600 font-semibold shadow-sm' : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300' }}">
                    {{ $label }}
                </a>
            @endforeach

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

    {{-- CHART --}}
    <div class="bg-white rounded-lg border border-gray-200 p-5 mb-6" x-data="pagesChartComponent()" x-init="initChart()">
        <h2 class="text-base font-bold text-gray-900 mb-4">Page Views Over Time</h2>
        <div style="height: 320px;">
            <canvas id="pagesChart"></canvas>
        </div>
        {{-- Clickable legend --}}
        <div class="flex flex-wrap items-center gap-5 mt-4 pt-3 border-t border-gray-100 text-sm">
            <template x-for="(item, i) in legendItems" :key="i">
                <button @click="toggle(i)" class="flex items-center gap-1.5 transition-opacity cursor-pointer" :class="item.hidden ? 'opacity-30' : 'opacity-100'">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="'background:' + item.color"></span>
                    <span class="truncate max-w-[200px] text-gray-600" :class="item.hidden ? 'line-through' : ''" x-text="item.name" :title="item.name"></span>
                </button>
            </template>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-lg border border-gray-200 p-5"
         x-data="pagesTable()"
         x-init="init()">

        {{-- Search + rows per page --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" x-model="search" placeholder="Search page titles..." class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg w-72 focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400">
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span>Rows per page:</span>
                <select x-model.number="perPage" @change="currentPage = 1" class="border border-gray-200 rounded-md px-2 py-1.5 text-sm focus:outline-none focus:border-blue-400">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left">
                        <th class="py-3 px-3 text-gray-500 font-medium w-10">#</th>
                        <th class="py-3 px-3 text-gray-500 font-medium cursor-pointer hover:text-gray-900 select-none" @click="sortBy('pageTitle')">
                            <div class="flex items-center gap-1">
                                Page title
                                <template x-if="sortCol === 'pageTitle'">
                                    <svg class="w-3.5 h-3.5" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                                </template>
                            </div>
                        </th>
                        <th class="py-3 px-3 text-gray-500 font-medium text-right cursor-pointer hover:text-gray-900 select-none" @click="sortBy('screenPageViews')">
                            <div class="flex items-center justify-end gap-1">
                                Views
                                <template x-if="sortCol === 'screenPageViews'">
                                    <svg class="w-3.5 h-3.5" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                                </template>
                            </div>
                        </th>
                        <th class="py-3 px-3 text-gray-500 font-medium text-right cursor-pointer hover:text-gray-900 select-none" @click="sortBy('activeUsers')">
                            <div class="flex items-center justify-end gap-1">
                                Active users
                                <template x-if="sortCol === 'activeUsers'">
                                    <svg class="w-3.5 h-3.5" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                                </template>
                            </div>
                        </th>
                        <th class="py-3 px-3 text-gray-500 font-medium text-right cursor-pointer hover:text-gray-900 select-none" @click="sortBy('screenPageViewsPerUser')">
                            <div class="flex items-center justify-end gap-1">
                                Views / user
                                <template x-if="sortCol === 'screenPageViewsPerUser'">
                                    <svg class="w-3.5 h-3.5" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                                </template>
                            </div>
                        </th>
                        <th class="py-3 px-3 text-gray-500 font-medium text-right cursor-pointer hover:text-gray-900 select-none" @click="sortBy('userEngagementDuration')">
                            <div class="flex items-center justify-end gap-1">
                                Avg. engagement time
                                <template x-if="sortCol === 'userEngagementDuration'">
                                    <svg class="w-3.5 h-3.5" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                                </template>
                            </div>
                        </th>
                        <th class="py-3 px-3 text-gray-500 font-medium text-right cursor-pointer hover:text-gray-900 select-none" @click="sortBy('eventCount')">
                            <div class="flex items-center justify-end gap-1">
                                Event count
                                <template x-if="sortCol === 'eventCount'">
                                    <svg class="w-3.5 h-3.5" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                                </template>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Totals row --}}
                    <tr class="border-b border-gray-100 bg-gray-50 font-semibold text-gray-900">
                        <td class="py-3 px-3"></td>
                        <td class="py-3 px-3">Totals</td>
                        <td class="py-3 px-3 text-right" x-text="fmt(totals.screenPageViews)"></td>
                        <td class="py-3 px-3 text-right" x-text="fmt(totals.activeUsers)"></td>
                        <td class="py-3 px-3 text-right" x-text="totals.activeUsers > 0 ? (totals.screenPageViews / totals.activeUsers).toFixed(2) : '0.00'"></td>
                        <td class="py-3 px-3 text-right" x-text="fmtDuration(totals.userEngagementDuration / (totals.activeUsers || 1))"></td>
                        <td class="py-3 px-3 text-right" x-text="fmt(totals.eventCount)"></td>
                    </tr>
                    {{-- Data rows --}}
                    <template x-for="(row, idx) in pagedRows()" :key="idx">
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-3 text-gray-400 text-xs" x-text="(currentPage - 1) * perPage + idx + 1"></td>
                            <td class="py-3 px-3 text-gray-700 max-w-xs truncate" x-text="row.pageTitle" :title="row.pageTitle"></td>
                            <td class="py-3 px-3 text-right text-gray-900 font-medium" x-text="fmt(row.screenPageViews)"></td>
                            <td class="py-3 px-3 text-right text-gray-900" x-text="fmt(row.activeUsers)"></td>
                            <td class="py-3 px-3 text-right text-gray-900" x-text="parseFloat(row.screenPageViewsPerUser).toFixed(2)"></td>
                            <td class="py-3 px-3 text-right text-gray-900" x-text="fmtDuration(row.userEngagementDuration / (row.activeUsers || 1))"></td>
                            <td class="py-3 px-3 text-right text-gray-900" x-text="fmt(row.eventCount)"></td>
                        </tr>
                    </template>
                    <template x-if="filteredRows().length === 0">
                        <tr><td colspan="7" class="py-8 text-center text-gray-400">No pages match your search.</td></tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-3 border-t border-gray-100 text-sm text-gray-500">
            <span>
                Showing <span x-text="filteredRows().length === 0 ? 0 : (currentPage - 1) * perPage + 1"></span>–<span x-text="Math.min(currentPage * perPage, filteredRows().length)"></span> of <span x-text="filteredRows().length"></span>
            </span>
            <div class="flex items-center gap-1">
                <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1"
                        class="px-3 py-1.5 rounded-md border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                    Prev
                </button>
                <template x-for="p in totalPages()" :key="p">
                    <button @click="currentPage = p"
                            :class="p === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 hover:bg-gray-50'"
                            class="px-3 py-1.5 rounded-md border transition-colors" x-text="p"></button>
                </template>
                <button @click="currentPage = Math.min(totalPages().length, currentPage + 1)" :disabled="currentPage >= totalPages().length"
                        class="px-3 py-1.5 rounded-md border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                    Next
                </button>
            </div>
        </div>
    </div>

</div>

<script>
const _pagesChartData = @json($pagesChart);
const _chartColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16'];
let _pagesChartInstance = null;

function pagesChartComponent() {
    return {
        legendItems: [],

        initChart() {
            const chartData = _pagesChartData;
            const colors = _chartColors;

            this.legendItems = chartData.datasets.map((ds, i) => ({
                name: ds.name,
                color: colors[i % colors.length],
                hidden: i >= 5,
            }));

            if (chartData.labels.length === 0 || chartData.datasets.length === 0) return;

            const ctx = document.getElementById('pagesChart').getContext('2d');

            const hexToRgba = (hex, alpha) => {
                const r = parseInt(hex.slice(1, 3), 16);
                const g = parseInt(hex.slice(3, 5), 16);
                const b = parseInt(hex.slice(5, 7), 16);
                return `rgba(${r},${g},${b},${alpha})`;
            };

            const fillAlphas = [0.12, 0.10, 0.08, 0.06, 0.05];

            const datasets = chartData.datasets.map((ds, i) => {
                const c = colors[i % colors.length];
                const grad = ctx.createLinearGradient(0, 0, 0, 320);
                grad.addColorStop(0, hexToRgba(c, fillAlphas[i] || 0.05));
                grad.addColorStop(1, hexToRgba(c, 0));
                return {
                    label: ds.name,
                    data: ds.data,
                    borderColor: c,
                    backgroundColor: grad,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: c,
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                    borderWidth: 2,
                    hidden: i >= 5,
                };
            });

            _pagesChartInstance = new Chart(ctx, {
                type: 'line',
                data: { labels: chartData.labels, datasets },
                options: {
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
                            callbacks: {
                                label: function(item) {
                                    return item.dataset.label + ': ' + item.formattedValue;
                                }
                            }
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 }, color: '#9ca3af', maxRotation: 0, maxTicksLimit: 12 },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: {
                                font: { size: 11 },
                                color: '#9ca3af',
                                callback: function(v) { return v.toLocaleString(); },
                            },
                        },
                    },
                },
            });
        },

        toggle(index) {
            if (!_pagesChartInstance) return;
            this.legendItems[index].hidden = !this.legendItems[index].hidden;
            _pagesChartInstance.setDatasetVisibility(index, !this.legendItems[index].hidden);
            _pagesChartInstance.update();
        },
    };
}

function pagesTable() {
    return {
        rows: @json($pagesData['rows']),
        search: '',
        sortCol: 'screenPageViews',
        sortDir: 'desc',
        perPage: 10,
        currentPage: 1,
        totals: {},

        init() {
            const t = { screenPageViews: 0, activeUsers: 0, userEngagementDuration: 0, eventCount: 0 };
            this.rows.forEach(r => {
                t.screenPageViews += parseInt(r.screenPageViews);
                t.activeUsers += parseInt(r.activeUsers);
                t.userEngagementDuration += parseFloat(r.userEngagementDuration);
                t.eventCount += parseInt(r.eventCount);
            });
            this.totals = t;

            this.$watch('search', () => { this.currentPage = 1; });
        },

        filteredRows() {
            let data = this.rows;
            if (this.search.trim()) {
                const q = this.search.toLowerCase();
                data = data.filter(r => r.pageTitle.toLowerCase().includes(q));
            }

            const col = this.sortCol;
            const dir = this.sortDir === 'asc' ? 1 : -1;
            const numericCols = ['screenPageViews', 'activeUsers', 'screenPageViewsPerUser', 'userEngagementDuration', 'eventCount'];

            data = [...data].sort((a, b) => {
                if (numericCols.includes(col)) {
                    return (parseFloat(a[col]) - parseFloat(b[col])) * dir;
                }
                return a[col].localeCompare(b[col]) * dir;
            });

            return data;
        },

        pagedRows() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredRows().slice(start, start + this.perPage);
        },

        totalPages() {
            const count = Math.ceil(this.filteredRows().length / this.perPage);
            if (count <= 7) return Array.from({ length: count }, (_, i) => i + 1);
            // Show first, last, and pages around current
            const pages = new Set([1, count]);
            for (let i = Math.max(2, this.currentPage - 1); i <= Math.min(count - 1, this.currentPage + 1); i++) {
                pages.add(i);
            }
            return [...pages].sort((a, b) => a - b);
        },

        sortBy(col) {
            if (this.sortCol === col) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortCol = col;
                this.sortDir = col === 'pageTitle' ? 'asc' : 'desc';
            }
            this.currentPage = 1;
        },

        fmt(n) {
            return parseInt(n).toLocaleString();
        },

        fmtDuration(seconds) {
            const s = Math.round(parseFloat(seconds));
            if (s < 60) return s + 's';
            const m = Math.floor(s / 60);
            const rem = s % 60;
            if (m < 60) return m + 'm ' + rem + 's';
            const h = Math.floor(m / 60);
            return h + 'h ' + (m % 60) + 'm';
        },
    };
}
</script>

{{-- Share Modal --}}
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
                    <option value="pages_report">Pages Report</option>
                    <option value="full_dashboard">Full Dashboard</option>
                    <option value="overview_chart">Overview Chart</option>
                    <option value="stat_cards">Stat Cards</option>
                    <option value="devices">Device Breakdown</option>
                    <option value="countries">Top Countries</option>
                    <option value="events">Events</option>
                    <option value="traffic_sources">Traffic Sources</option>
                    <option value="top_pages">Top Pages</option>
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
                <input type="text" x-model="label" placeholder="e.g. Client pages report" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400">
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
        widgetType: 'pages_report',
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

</body>
</html>
