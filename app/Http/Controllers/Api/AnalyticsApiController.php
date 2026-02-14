<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Analytics\Ga4Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Open Analytics API',
    description: 'Self-hosted GA4 Analytics Dashboard API. Access property data including overview stats, top pages, traffic sources, devices, geography, events, and AI-generated insights. All data is cached for 15-30 minutes.',
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    description: 'Use a Sanctum API token for authentication.',
)]
#[OA\Tag(name: 'Properties', description: 'GA4 account & property discovery')]
#[OA\Tag(name: 'Dashboard', description: 'Aggregated dashboard data')]
#[OA\Tag(name: 'Reports', description: 'Individual analytics reports')]
#[OA\Tag(name: 'Insights', description: 'AI-generated insights')]
class AnalyticsApiController extends Controller
{
    // ── Properties ────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/analytics/properties',
        summary: 'List all GA4 properties',
        description: 'Returns all GA4 accounts and their associated properties accessible by the configured service account. Cached for 30 minutes.',
        security: [['bearerAuth' => []], ['apiKeyAuth' => []]],
        tags: ['Properties'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of accounts with properties',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'name', type: 'string', example: 'accounts/123456'),
                                    new OA\Property(property: 'displayName', type: 'string', example: 'My Company'),
                                    new OA\Property(
                                        property: 'properties',
                                        type: 'array',
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: 'id', type: 'string', example: '456789012'),
                                                new OA\Property(property: 'name', type: 'string', example: 'My Website'),
                                                new OA\Property(property: 'resource', type: 'string', example: 'properties/456789012'),
                                            ],
                                        ),
                                    ),
                                ],
                            ),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
    public function properties(): JsonResponse
    {
        return response()->json(['data' => Ga4Service::listProperties()]);
    }

    // ── Full Dashboard ────────────────────────────────────────────

    #[OA\Get(
        path: '/api/analytics/{propertyId}/dashboard',
        summary: 'Get full dashboard payload',
        description: 'Returns all analytics data for a property in one request: overview, top pages, traffic sources, devices, geography, events, and insights.',
        security: [['bearerAuth' => []], ['apiKeyAuth' => []]],
        tags: ['Dashboard'],
        parameters: [
            new OA\Parameter(name: 'propertyId', in: 'path', required: true, description: 'GA4 Property ID', schema: new OA\Schema(type: 'string'), example: '456789012'),
            new OA\Parameter(
                name: 'range',
                in: 'query',
                required: false,
                description: 'Date range preset',
                schema: new OA\Schema(type: 'string', default: '30days', enum: ['today', 'yesterday', '7days', '14days', '30days', '90days', '6months', '12months', 'this_month', 'last_month', 'this_year']),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Full dashboard data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'property_id', type: 'string', example: '456789012'),
                                new OA\Property(property: 'range', type: 'string', example: '30days'),
                                new OA\Property(property: 'range_label', type: 'string', example: 'Last 30 days'),
                                new OA\Property(property: 'compare_label', type: 'string', example: 'vs previous 30 days'),
                                new OA\Property(property: 'overview', type: 'object'),
                                new OA\Property(property: 'top_pages', type: 'object'),
                                new OA\Property(property: 'traffic_sources', type: 'object'),
                                new OA\Property(property: 'devices', type: 'object'),
                                new OA\Property(property: 'geography', type: 'object'),
                                new OA\Property(property: 'events', type: 'object'),
                                new OA\Property(property: 'insights', type: 'array', items: new OA\Items(type: 'object')),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function dashboard(Request $request, string $propertyId): JsonResponse
    {
        $request->validate(['range' => 'sometimes|string|in:today,yesterday,7days,14days,30days,90days,6months,12months,this_month,last_month,this_year']);

        $range = $request->query('range', '30days');
        $config = Ga4Service::rangeConfig($range);
        $ga = new Ga4Service($propertyId);

        $overview = $ga->dashboardOverview($config['start'], $config['end'], $config['prev_start'], $config['prev_end']);
        $topPages = $ga->topPages($config['start'], $config['end']);
        $traffic = $ga->trafficSources($config['start'], $config['end']);
        $devices = $ga->devices($config['start'], $config['end']);
        $geo = $ga->geography($config['start'], $config['end']);
        $events = $ga->events($config['start'], $config['end']);
        $insights = $ga->generateInsights($overview, $traffic, $devices);

        return response()->json([
            'data' => [
                'property_id' => $propertyId,
                'range' => $range,
                'range_label' => $config['label'],
                'compare_label' => $config['compare_label'],
                'overview' => $overview,
                'top_pages' => $topPages,
                'traffic_sources' => $traffic,
                'devices' => $devices,
                'geography' => $geo,
                'events' => $events,
                'insights' => $insights,
            ],
        ]);
    }

    // ── Overview ──────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/analytics/{propertyId}/overview',
        summary: 'Get overview stats with comparison',
        description: 'Returns current & previous period totals, percentage deltas, and daily chart data. Metrics: sessions, activeUsers, newUsers, screenPageViews, engagementRate, bounceRate, averageSessionDuration, sessionsPerUser.',
        security: [['bearerAuth' => []], ['apiKeyAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'propertyId', in: 'path', required: true, description: 'GA4 Property ID', schema: new OA\Schema(type: 'string'), example: '456789012'),
            new OA\Parameter(name: 'range', in: 'query', required: false, description: 'Date range preset', schema: new OA\Schema(type: 'string', default: '30days', enum: ['today', 'yesterday', '7days', '14days', '30days', '90days', '6months', '12months', 'this_month', 'last_month', 'this_year'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Overview with current, previous, delta, and chart data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current', type: 'object', description: 'Current period totals', example: '{"sessions":1250,"activeUsers":890,"newUsers":420,"screenPageViews":3200,"engagementRate":0.65,"bounceRate":0.35,"averageSessionDuration":125.4,"sessionsPerUser":1.4}'),
                                new OA\Property(property: 'previous', type: 'object', description: 'Previous period totals'),
                                new OA\Property(property: 'delta', type: 'object', description: 'Percentage change per metric', example: '{"sessions":13.6,"activeUsers":11.3}'),
                                new OA\Property(
                                    property: 'chart',
                                    type: 'object',
                                    description: 'Daily time series for charting',
                                    properties: [
                                        new OA\Property(property: 'labels', type: 'array', items: new OA\Items(type: 'string'), example: '["01/01","01/02","01/03"]'),
                                        new OA\Property(property: 'sessions', type: 'array', items: new OA\Items(type: 'integer'), example: '[40,45,38]'),
                                        new OA\Property(property: 'users', type: 'array', items: new OA\Items(type: 'integer'), example: '[30,35,28]'),
                                        new OA\Property(property: 'pageviews', type: 'array', items: new OA\Items(type: 'integer'), example: '[100,120,95]'),
                                    ],
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function overview(Request $request, string $propertyId): JsonResponse
    {
        $request->validate(['range' => 'sometimes|string|in:today,yesterday,7days,14days,30days,90days,6months,12months,this_month,last_month,this_year']);

        $range = $request->query('range', '30days');
        $config = Ga4Service::rangeConfig($range);
        $ga = new Ga4Service($propertyId);

        return response()->json(['data' => $ga->dashboardOverview($config['start'], $config['end'], $config['prev_start'], $config['prev_end'])]);
    }

    // ── Top Pages ────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/analytics/{propertyId}/top-pages',
        summary: 'Get top pages by pageviews',
        description: 'Returns pages ranked by pageviews with path, title, view count, average session duration (seconds), and bounce rate.',
        security: [['bearerAuth' => []], ['apiKeyAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'propertyId', in: 'path', required: true, description: 'GA4 Property ID', schema: new OA\Schema(type: 'string'), example: '456789012'),
            new OA\Parameter(name: 'range', in: 'query', required: false, description: 'Date range preset', schema: new OA\Schema(type: 'string', default: '30days', enum: ['today', 'yesterday', '7days', '14days', '30days', '90days', '6months', '12months', 'this_month', 'last_month', 'this_year'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Top pages report',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'headers', type: 'array', items: new OA\Items(type: 'string'), example: '["pagePath","pageTitle","screenPageViews","averageSessionDuration","bounceRate"]'),
                                new OA\Property(
                                    property: 'rows',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'pagePath', type: 'string', example: '/'),
                                            new OA\Property(property: 'pageTitle', type: 'string', example: 'Home'),
                                            new OA\Property(property: 'screenPageViews', type: 'string', example: '500'),
                                            new OA\Property(property: 'averageSessionDuration', type: 'string', example: '65.3'),
                                            new OA\Property(property: 'bounceRate', type: 'string', example: '0.32'),
                                        ],
                                    ),
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function topPages(Request $request, string $propertyId): JsonResponse
    {
        $request->validate(['range' => 'sometimes|string|in:today,yesterday,7days,14days,30days,90days,6months,12months,this_month,last_month,this_year']);

        $range = $request->query('range', '30days');
        $config = Ga4Service::rangeConfig($range);
        $ga = new Ga4Service($propertyId);

        return response()->json(['data' => $ga->topPages($config['start'], $config['end'])]);
    }

    // ── Traffic Sources ──────────────────────────────────────────

    #[OA\Get(
        path: '/api/analytics/{propertyId}/traffic-sources',
        summary: 'Get traffic sources breakdown',
        description: 'Returns sessions and new users grouped by source/medium. Common sources: google/organic, direct/(none), facebook/referral.',
        security: [['bearerAuth' => []], ['apiKeyAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'propertyId', in: 'path', required: true, description: 'GA4 Property ID', schema: new OA\Schema(type: 'string'), example: '456789012'),
            new OA\Parameter(name: 'range', in: 'query', required: false, description: 'Date range preset', schema: new OA\Schema(type: 'string', default: '30days', enum: ['today', 'yesterday', '7days', '14days', '30days', '90days', '6months', '12months', 'this_month', 'last_month', 'this_year'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Traffic sources report',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'headers', type: 'array', items: new OA\Items(type: 'string'), example: '["sessionSource","sessionMedium","sessions","newUsers"]'),
                                new OA\Property(
                                    property: 'rows',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'sessionSource', type: 'string', example: 'google'),
                                            new OA\Property(property: 'sessionMedium', type: 'string', example: 'organic'),
                                            new OA\Property(property: 'sessions', type: 'string', example: '600'),
                                            new OA\Property(property: 'newUsers', type: 'string', example: '200'),
                                        ],
                                    ),
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function trafficSources(Request $request, string $propertyId): JsonResponse
    {
        $request->validate(['range' => 'sometimes|string|in:today,yesterday,7days,14days,30days,90days,6months,12months,this_month,last_month,this_year']);

        $range = $request->query('range', '30days');
        $config = Ga4Service::rangeConfig($range);
        $ga = new Ga4Service($propertyId);

        return response()->json(['data' => $ga->trafficSources($config['start'], $config['end'])]);
    }

    // ── Devices ──────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/analytics/{propertyId}/devices',
        summary: 'Get device category breakdown',
        description: 'Returns sessions grouped by device category: desktop, mobile, tablet.',
        security: [['bearerAuth' => []], ['apiKeyAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'propertyId', in: 'path', required: true, description: 'GA4 Property ID', schema: new OA\Schema(type: 'string'), example: '456789012'),
            new OA\Parameter(name: 'range', in: 'query', required: false, description: 'Date range preset', schema: new OA\Schema(type: 'string', default: '30days', enum: ['today', 'yesterday', '7days', '14days', '30days', '90days', '6months', '12months', 'this_month', 'last_month', 'this_year'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Device breakdown',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'headers', type: 'array', items: new OA\Items(type: 'string'), example: '["deviceCategory","sessions"]'),
                                new OA\Property(
                                    property: 'rows',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'deviceCategory', type: 'string', example: 'desktop', enum: ['desktop', 'mobile', 'tablet']),
                                            new OA\Property(property: 'sessions', type: 'string', example: '700'),
                                        ],
                                    ),
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function devices(Request $request, string $propertyId): JsonResponse
    {
        $request->validate(['range' => 'sometimes|string|in:today,yesterday,7days,14days,30days,90days,6months,12months,this_month,last_month,this_year']);

        $range = $request->query('range', '30days');
        $config = Ga4Service::rangeConfig($range);
        $ga = new Ga4Service($propertyId);

        return response()->json(['data' => $ga->devices($config['start'], $config['end'])]);
    }

    // ── Geography ────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/analytics/{propertyId}/geography',
        summary: 'Get geography breakdown',
        description: 'Returns sessions grouped by country, sorted by count descending.',
        security: [['bearerAuth' => []], ['apiKeyAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'propertyId', in: 'path', required: true, description: 'GA4 Property ID', schema: new OA\Schema(type: 'string'), example: '456789012'),
            new OA\Parameter(name: 'range', in: 'query', required: false, description: 'Date range preset', schema: new OA\Schema(type: 'string', default: '30days', enum: ['today', 'yesterday', '7days', '14days', '30days', '90days', '6months', '12months', 'this_month', 'last_month', 'this_year'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Geography report',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'headers', type: 'array', items: new OA\Items(type: 'string'), example: '["country","sessions"]'),
                                new OA\Property(
                                    property: 'rows',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'country', type: 'string', example: 'United States'),
                                            new OA\Property(property: 'sessions', type: 'string', example: '500'),
                                        ],
                                    ),
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function geography(Request $request, string $propertyId): JsonResponse
    {
        $request->validate(['range' => 'sometimes|string|in:today,yesterday,7days,14days,30days,90days,6months,12months,this_month,last_month,this_year']);

        $range = $request->query('range', '30days');
        $config = Ga4Service::rangeConfig($range);
        $ga = new Ga4Service($propertyId);

        return response()->json(['data' => $ga->geography($config['start'], $config['end'])]);
    }

    // ── Events ───────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/analytics/{propertyId}/events',
        summary: 'Get events breakdown',
        description: 'Returns event names with total counts, sorted descending. Common GA4 events: page_view, session_start, first_visit, scroll, click.',
        security: [['bearerAuth' => []], ['apiKeyAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'propertyId', in: 'path', required: true, description: 'GA4 Property ID', schema: new OA\Schema(type: 'string'), example: '456789012'),
            new OA\Parameter(name: 'range', in: 'query', required: false, description: 'Date range preset', schema: new OA\Schema(type: 'string', default: '30days', enum: ['today', 'yesterday', '7days', '14days', '30days', '90days', '6months', '12months', 'this_month', 'last_month', 'this_year'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Events report',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'headers', type: 'array', items: new OA\Items(type: 'string'), example: '["eventName","eventCount"]'),
                                new OA\Property(
                                    property: 'rows',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'eventName', type: 'string', example: 'page_view'),
                                            new OA\Property(property: 'eventCount', type: 'string', example: '3200'),
                                        ],
                                    ),
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function events(Request $request, string $propertyId): JsonResponse
    {
        $request->validate(['range' => 'sometimes|string|in:today,yesterday,7days,14days,30days,90days,6months,12months,this_month,last_month,this_year']);

        $range = $request->query('range', '30days');
        $config = Ga4Service::rangeConfig($range);
        $ga = new Ga4Service($propertyId);

        return response()->json(['data' => $ga->events($config['start'], $config['end'])]);
    }

    // ── Pages & Screens (detailed) ─────────────────────────────

    #[OA\Get(
        path: '/api/analytics/{propertyId}/pages',
        summary: 'Get detailed pages & screens report',
        description: 'Returns all pages grouped by page title with views, active users, views per user, engagement duration, and event count. Also includes a daily time-series chart for the top 10 pages.',
        security: [['bearerAuth' => []], ['apiKeyAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'propertyId', in: 'path', required: true, description: 'GA4 Property ID', schema: new OA\Schema(type: 'string'), example: '456789012'),
            new OA\Parameter(name: 'range', in: 'query', required: false, description: 'Date range preset', schema: new OA\Schema(type: 'string', default: '30days', enum: ['today', 'yesterday', '7days', '14days', '30days', '90days', '6months', '12months', 'this_month', 'last_month', 'this_year'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detailed pages report with chart data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'table',
                                    type: 'object',
                                    description: 'All pages with metrics',
                                    properties: [
                                        new OA\Property(property: 'headers', type: 'array', items: new OA\Items(type: 'string'), example: '["pageTitle","screenPageViews","activeUsers","screenPageViewsPerUser","userEngagementDuration","eventCount"]'),
                                        new OA\Property(
                                            property: 'rows',
                                            type: 'array',
                                            items: new OA\Items(
                                                properties: [
                                                    new OA\Property(property: 'pageTitle', type: 'string', example: 'Home'),
                                                    new OA\Property(property: 'screenPageViews', type: 'string', example: '1200'),
                                                    new OA\Property(property: 'activeUsers', type: 'string', example: '450'),
                                                    new OA\Property(property: 'screenPageViewsPerUser', type: 'string', example: '2.67'),
                                                    new OA\Property(property: 'userEngagementDuration', type: 'string', example: '8520.5'),
                                                    new OA\Property(property: 'eventCount', type: 'string', example: '3400'),
                                                ],
                                            ),
                                        ),
                                    ],
                                ),
                                new OA\Property(
                                    property: 'chart',
                                    type: 'object',
                                    description: 'Daily pageview time series for top 10 pages',
                                    properties: [
                                        new OA\Property(property: 'labels', type: 'array', items: new OA\Items(type: 'string'), example: '["01/01","01/02","01/03"]'),
                                        new OA\Property(
                                            property: 'datasets',
                                            type: 'array',
                                            items: new OA\Items(
                                                properties: [
                                                    new OA\Property(property: 'name', type: 'string', example: 'Home'),
                                                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'integer'), example: '[40,55,38]'),
                                                ],
                                            ),
                                        ),
                                    ],
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function pages(Request $request, string $propertyId): JsonResponse
    {
        $request->validate(['range' => 'sometimes|string|in:today,yesterday,7days,14days,30days,90days,6months,12months,this_month,last_month,this_year']);

        $range = $request->query('range', '30days');
        $config = Ga4Service::rangeConfig($range);
        $ga = new Ga4Service($propertyId);

        return response()->json([
            'data' => [
                'table' => $ga->pagesDetailed($config['start'], $config['end']),
                'chart' => $ga->pagesChart($config['start'], $config['end']),
            ],
        ]);
    }

    // ── Insights ─────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/analytics/{propertyId}/insights',
        summary: 'Get AI-generated insights',
        description: 'Returns up to 3 insight banners with severity (warning, success, opportunity, info) based on analytics data. Covers traffic drops, growth, source diversity, mobile optimization.',
        security: [['bearerAuth' => []], ['apiKeyAuth' => []]],
        tags: ['Insights'],
        parameters: [
            new OA\Parameter(name: 'propertyId', in: 'path', required: true, description: 'GA4 Property ID', schema: new OA\Schema(type: 'string'), example: '456789012'),
            new OA\Parameter(name: 'range', in: 'query', required: false, description: 'Date range preset', schema: new OA\Schema(type: 'string', default: '30days', enum: ['today', 'yesterday', '7days', '14days', '30days', '90days', '6months', '12months', 'this_month', 'last_month', 'this_year'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Generated insights',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'type', type: 'string', enum: ['warning', 'success', 'opportunity', 'info'], example: 'warning'),
                                    new OA\Property(property: 'text', type: 'string', example: 'Sessions dropped 25% compared to the previous period. Investigate traffic sources for changes.'),
                                ],
                            ),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function insights(Request $request, string $propertyId): JsonResponse
    {
        $request->validate(['range' => 'sometimes|string|in:today,yesterday,7days,14days,30days,90days,6months,12months,this_month,last_month,this_year']);

        $range = $request->query('range', '30days');
        $config = Ga4Service::rangeConfig($range);
        $ga = new Ga4Service($propertyId);

        $overview = $ga->dashboardOverview($config['start'], $config['end'], $config['prev_start'], $config['prev_end']);
        $traffic = $ga->trafficSources($config['start'], $config['end']);
        $devices = $ga->devices($config['start'], $config['end']);

        return response()->json(['data' => $ga->generateInsights($overview, $traffic, $devices)]);
    }
}
