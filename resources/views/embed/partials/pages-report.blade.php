{{-- Pages Chart --}}
<div class="bg-white rounded-lg border border-gray-200 p-5 mb-5" x-data="pagesChartComponent()" x-init="initChart()">
    <h2 class="text-base font-bold text-gray-900 mb-4">Page Views Over Time</h2>
    <div style="height: 320px;">
        <canvas id="pagesChart"></canvas>
    </div>
    <div class="flex flex-wrap items-center gap-5 mt-4 pt-3 border-t border-gray-100 text-sm">
        <template x-for="(item, i) in legendItems" :key="i">
            <button @click="toggle(i)" class="flex items-center gap-1.5 transition-opacity cursor-pointer" :class="item.hidden ? 'opacity-30' : 'opacity-100'">
                <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="'background:' + item.color"></span>
                <span class="truncate max-w-[200px] text-gray-600" :class="item.hidden ? 'line-through' : ''" x-text="item.name" :title="item.name"></span>
            </button>
        </template>
    </div>
</div>

{{-- Pages Table --}}
<div class="bg-white rounded-lg border border-gray-200 p-5" x-data="pagesTable()" x-init="init()">
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
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left">
                    <th class="py-3 px-3 text-gray-500 font-medium w-10">#</th>
                    <th class="py-3 px-3 text-gray-500 font-medium cursor-pointer hover:text-gray-900 select-none" @click="sortBy('pageTitle')">Page title</th>
                    <th class="py-3 px-3 text-gray-500 font-medium text-right cursor-pointer hover:text-gray-900 select-none" @click="sortBy('screenPageViews')">Views</th>
                    <th class="py-3 px-3 text-gray-500 font-medium text-right cursor-pointer hover:text-gray-900 select-none" @click="sortBy('activeUsers')">Active users</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-gray-100 bg-gray-50 font-semibold text-gray-900">
                    <td class="py-3 px-3"></td>
                    <td class="py-3 px-3">Totals</td>
                    <td class="py-3 px-3 text-right" x-text="fmt(totals.screenPageViews)"></td>
                    <td class="py-3 px-3 text-right" x-text="fmt(totals.activeUsers)"></td>
                </tr>
                <template x-for="(row, idx) in pagedRows()" :key="idx">
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-3 text-gray-400 text-xs" x-text="(currentPage - 1) * perPage + idx + 1"></td>
                        <td class="py-3 px-3 text-gray-700 max-w-xs truncate" x-text="row.pageTitle" :title="row.pageTitle"></td>
                        <td class="py-3 px-3 text-right text-gray-900 font-medium" x-text="fmt(row.screenPageViews)"></td>
                        <td class="py-3 px-3 text-right text-gray-900" x-text="fmt(row.activeUsers)"></td>
                    </tr>
                </template>
                <template x-if="filteredRows().length === 0">
                    <tr><td colspan="4" class="py-8 text-center text-gray-400">No pages match your search.</td></tr>
                </template>
            </tbody>
        </table>
    </div>
    <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-3 border-t border-gray-100 text-sm text-gray-500">
        <span>Showing <span x-text="filteredRows().length === 0 ? 0 : (currentPage - 1) * perPage + 1"></span>-<span x-text="Math.min(currentPage * perPage, filteredRows().length)"></span> of <span x-text="filteredRows().length"></span></span>
        <div class="flex items-center gap-1">
            <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1.5 rounded-md border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Prev</button>
            <button @click="currentPage = Math.min(totalPages(), currentPage + 1)" :disabled="currentPage >= totalPages()" class="px-3 py-1.5 rounded-md border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Next</button>
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
            this.legendItems = chartData.datasets.map((ds, i) => ({ name: ds.name, color: colors[i % colors.length], hidden: i >= 5 }));
            if (chartData.labels.length === 0 || chartData.datasets.length === 0) return;
            const ctx = document.getElementById('pagesChart').getContext('2d');
            const hexToRgba = (hex, alpha) => { const r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16); return `rgba(${r},${g},${b},${alpha})`; };
            const fillAlphas = [0.12, 0.10, 0.08, 0.06, 0.05];
            const datasets = chartData.datasets.map((ds, i) => {
                const c = colors[i % colors.length];
                const grad = ctx.createLinearGradient(0, 0, 0, 320);
                grad.addColorStop(0, hexToRgba(c, fillAlphas[i] || 0.05));
                grad.addColorStop(1, hexToRgba(c, 0));
                return { label: ds.name, data: ds.data, borderColor: c, backgroundColor: grad, fill: true, tension: 0.35, pointRadius: 0, pointHoverRadius: 5, borderWidth: 2, hidden: i >= 5 };
            });
            _pagesChartInstance = new Chart(ctx, {
                type: 'line', data: { labels: chartData.labels, datasets },
                options: { responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' }, plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1f2937', cornerRadius: 8 } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af', maxRotation: 0, maxTicksLimit: 12 } }, y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, color: '#9ca3af' } } } },
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
        search: '', sortCol: 'screenPageViews', sortDir: 'desc', perPage: 10, currentPage: 1, totals: {},
        init() {
            const t = { screenPageViews: 0, activeUsers: 0 };
            this.rows.forEach(r => { t.screenPageViews += parseInt(r.screenPageViews); t.activeUsers += parseInt(r.activeUsers); });
            this.totals = t;
            this.$watch('search', () => { this.currentPage = 1; });
        },
        filteredRows() {
            let data = this.rows;
            if (this.search.trim()) { const q = this.search.toLowerCase(); data = data.filter(r => r.pageTitle.toLowerCase().includes(q)); }
            const col = this.sortCol, dir = this.sortDir === 'asc' ? 1 : -1;
            data = [...data].sort((a, b) => col === 'pageTitle' ? a[col].localeCompare(b[col]) * dir : (parseFloat(a[col]) - parseFloat(b[col])) * dir);
            return data;
        },
        pagedRows() { const start = (this.currentPage - 1) * this.perPage; return this.filteredRows().slice(start, start + this.perPage); },
        totalPages() { return Math.ceil(this.filteredRows().length / this.perPage); },
        sortBy(col) { if (this.sortCol === col) { this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc'; } else { this.sortCol = col; this.sortDir = col === 'pageTitle' ? 'asc' : 'desc'; } this.currentPage = 1; },
        fmt(n) { return parseInt(n).toLocaleString(); },
    };
}
</script>
