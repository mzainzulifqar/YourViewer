@include('analytics.partials.devices-widget')

<script>
document.addEventListener('DOMContentLoaded', function () {
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
