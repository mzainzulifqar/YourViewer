<div class="grid grid-cols-2 gap-5">
    @include('analytics.partials.stat-card', [
        'label' => 'Users',
        'value' => $overview['current']['activeUsers'],
        'delta' => $overview['delta']['activeUsers'],
        'icon' => 'none',
        'tooltip' => 'Unique active visitors on your site.',
        'compareLabel' => $compareLabel,
    ])
    @include('analytics.partials.stat-card', [
        'label' => 'New Users',
        'value' => $overview['current']['newUsers'],
        'delta' => $overview['delta']['newUsers'],
        'icon' => 'none',
        'tooltip' => 'First-time visitors to your site.',
        'compareLabel' => $compareLabel,
    ])
    @include('analytics.partials.stat-card', [
        'label' => 'Engagement Rate',
        'value' => $overview['current']['engagementRate'],
        'delta' => $overview['delta']['engagementRate'],
        'format' => 'percent',
        'icon' => 'none',
        'tooltip' => 'Engaged sessions lasted 10s+, had a conversion, or 2+ page views. Inverse of bounce rate.',
        'compareLabel' => $compareLabel,
    ])
    @include('analytics.partials.stat-card', [
        'label' => 'Sessions/Visitor',
        'value' => $overview['current']['sessionsPerUser'],
        'delta' => $overview['delta']['sessionsPerUser'],
        'format' => 'decimal',
        'icon' => 'none',
        'tooltip' => 'Average number of sessions per visitor.',
        'compareLabel' => $compareLabel,
    ])
</div>
