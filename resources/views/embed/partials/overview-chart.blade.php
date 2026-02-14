@include('analytics.partials.overview-chart')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData = @json($overview['chart']);

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
            },
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af', maxRotation: 0 } },
            y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, color: '#9ca3af' } },
        },
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
});
</script>
