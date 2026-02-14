<div class="grid grid-cols-2 gap-5">
    @include('analytics.partials.stat-card', [
        'label' => 'Sessions',
        'value' => $overview['current']['sessions'],
        'delta' => $overview['delta']['sessions'],
        'icon' => 'none',
        'tooltip' => 'Total number of visits to your website.',
        'compareLabel' => $compareLabel,
    ])
    @include('analytics.partials.stat-card', [
        'label' => 'Pageviews',
        'value' => $overview['current']['screenPageViews'],
        'delta' => $overview['delta']['screenPageViews'],
        'icon' => 'none',
        'tooltip' => 'Total pages viewed, including repeated views.',
        'compareLabel' => $compareLabel,
    ])
    @include('analytics.partials.stat-card', [
        'label' => 'Avg. Session Time',
        'value' => $overview['current']['averageSessionDuration'],
        'delta' => $overview['delta']['averageSessionDuration'],
        'format' => 'duration',
        'icon' => 'none',
        'tooltip' => 'Average time a visitor spends per visit.',
        'compareLabel' => $compareLabel,
    ])
    @include('analytics.partials.stat-card', [
        'label' => 'Bounce Rate',
        'value' => $overview['current']['bounceRate'],
        'delta' => $overview['delta']['bounceRate'],
        'format' => 'percent',
        'icon' => 'none',
        'tooltip' => 'Inverse of Engagement Rate. A bounced session had no engagement (under 10s, no conversion, single page).',
        'compareLabel' => $compareLabel,
        'invertDelta' => true,
    ])
</div>
