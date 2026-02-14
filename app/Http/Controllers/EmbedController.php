<?php

namespace App\Http\Controllers;

use App\Models\SharedReport;
use App\Services\Analytics\Ga4Service;
use Illuminate\Http\Request;

class EmbedController extends Controller
{
    public function show(Request $request, string $token)
    {
        /** @var SharedReport $share */
        $share = $request->attributes->get('shared_report');

        $config = Ga4Service::rangeConfig($share->date_range);
        $ga = new Ga4Service($share->property_id);
        $widgetType = $share->widget_type;

        $data = match ($widgetType) {
            'full_dashboard' => [
                'overview' => $ga->dashboardOverview($config['start'], $config['end'], $config['prev_start'], $config['prev_end']),
                'topPages' => $ga->topPages($config['start'], $config['end']),
                'traffic' => $ga->trafficSources($config['start'], $config['end']),
                'devices' => $ga->devices($config['start'], $config['end']),
                'geo' => $ga->geography($config['start'], $config['end']),
                'events' => $ga->events($config['start'], $config['end']),
            ],
            'overview_chart' => [
                'overview' => $ga->dashboardOverview($config['start'], $config['end'], $config['prev_start'], $config['prev_end']),
            ],
            'stat_cards' => [
                'overview' => $ga->dashboardOverview($config['start'], $config['end'], $config['prev_start'], $config['prev_end']),
            ],
            'devices' => [
                'devices' => $ga->devices($config['start'], $config['end']),
            ],
            'countries' => [
                'geo' => $ga->geography($config['start'], $config['end']),
            ],
            'events' => [
                'events' => $ga->events($config['start'], $config['end']),
            ],
            'traffic_sources' => [
                'traffic' => $ga->trafficSources($config['start'], $config['end']),
            ],
            'top_pages' => [
                'topPages' => $ga->topPages($config['start'], $config['end']),
            ],
            'pages_report' => [
                'pagesData' => $ga->pagesDetailed($config['start'], $config['end']),
                'pagesChart' => $ga->pagesChart($config['start'], $config['end']),
            ],
        };

        $data['widgetType'] = $widgetType;
        $data['rangeLabel'] = $config['label'];
        $data['compareLabel'] = $config['compare_label'] ?? '';
        $data['propertyId'] = $share->property_id;

        return response()
            ->view('embed.show', $data)
            ->header('X-Frame-Options', 'ALLOWALL')
            ->header('Content-Security-Policy', 'frame-ancestors *');
    }
}
