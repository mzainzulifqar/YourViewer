<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Embedded Report</title>
    @vite(['resources/css/app.css'])
    @if(in_array($widgetType, ['full_dashboard', 'overview_chart', 'devices', 'pages_report']))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    @endif
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <style>
        body { background: transparent !important; }
    </style>
</head>
<body class="text-gray-800 antialiased">

<div class="p-4">
    {{-- Range label badge --}}
    <div class="flex items-center justify-between mb-4">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-500 text-xs font-medium rounded-full">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
            {{ $rangeLabel }}
        </span>
        <span class="text-[10px] text-gray-300 font-medium tracking-wide">Open Analytics</span>
    </div>

    @include('embed.partials.' . \Illuminate\Support\Str::slug($widgetType, '-'))
</div>

</body>
</html>
