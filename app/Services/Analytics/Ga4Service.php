<?php

namespace App\Services\Analytics;

use Google\Analytics\Admin\V1beta\Client\AnalyticsAdminServiceClient;
use Google\Analytics\Admin\V1beta\ListAccountSummariesRequest;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Illuminate\Support\Facades\Cache;

class Ga4Service
{
    private BetaAnalyticsDataClient $client;
    private string $property;

    private static function credentialsPath(): string
    {
        return env('GOOGLE_APPLICATION_CREDENTIALS', storage_path('app/service-account.json'));
    }

    public function __construct(string $propertyId)
    {
        $this->client = new BetaAnalyticsDataClient([
            'credentials' => self::credentialsPath(),
        ]);
        $this->property = 'properties/' . $propertyId;
    }

    // ── List All Properties ─────────────────────────────────────────

    public static function listProperties(): array
    {
        return Cache::remember('ga4.properties', now()->addMinutes(30), function () {
            $admin = new AnalyticsAdminServiceClient([
                'credentials' => self::credentialsPath(),
            ]);

            $request = new ListAccountSummariesRequest();
            $response = $admin->listAccountSummaries($request);

            $accounts = [];
            foreach ($response as $accountSummary) {
                $account = [
                    'name' => $accountSummary->getAccount(),
                    'displayName' => $accountSummary->getDisplayName(),
                    'properties' => [],
                ];

                foreach ($accountSummary->getPropertySummaries() as $prop) {
                    $propertyId = str_replace('properties/', '', $prop->getProperty());
                    $account['properties'][] = [
                        'id' => $propertyId,
                        'name' => $prop->getDisplayName(),
                        'resource' => $prop->getProperty(),
                    ];
                }

                $accounts[] = $account;
            }

            return $accounts;
        });
    }

    // ── Date Range Helpers ──────────────────────────────────────────

    public static function rangeConfig(string $range, ?string $customStart = null, ?string $customEnd = null): array
    {
        $today = now();

        if ($range === 'custom' && $customStart && $customEnd) {
            $start = \Carbon\Carbon::parse($customStart);
            $end = \Carbon\Carbon::parse($customEnd);
            $days = $start->diffInDays($end) + 1;
            $prevEnd = $start->copy()->subDay();
            $prevStart = $prevEnd->copy()->subDays($days - 1);

            return [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'prev_start' => $prevStart->format('Y-m-d'),
                'prev_end' => $prevEnd->format('Y-m-d'),
                'label' => $start->format('M j') . ' – ' . $end->format('M j, Y'),
                'compare_label' => 'vs previous ' . $days . ' days',
            ];
        }

        return match ($range) {
            'today' => [
                'start' => 'today',
                'end' => 'today',
                'prev_start' => 'yesterday',
                'prev_end' => 'yesterday',
                'label' => 'Today',
                'compare_label' => 'vs yesterday',
            ],
            'yesterday' => [
                'start' => 'yesterday',
                'end' => 'yesterday',
                'prev_start' => '2daysAgo',
                'prev_end' => '2daysAgo',
                'label' => 'Yesterday',
                'compare_label' => 'vs day before',
            ],
            '7days' => [
                'start' => '7daysAgo',
                'end' => 'today',
                'prev_start' => '14daysAgo',
                'prev_end' => '8daysAgo',
                'label' => 'Last 7 days',
                'compare_label' => 'vs previous 7 days',
            ],
            '14days' => [
                'start' => '14daysAgo',
                'end' => 'today',
                'prev_start' => '28daysAgo',
                'prev_end' => '15daysAgo',
                'label' => 'Last 14 days',
                'compare_label' => 'vs previous 14 days',
            ],
            '30days' => [
                'start' => '30daysAgo',
                'end' => 'today',
                'prev_start' => '60daysAgo',
                'prev_end' => '31daysAgo',
                'label' => 'Last 30 days',
                'compare_label' => 'vs previous 30 days',
            ],
            '90days' => [
                'start' => '90daysAgo',
                'end' => 'today',
                'prev_start' => '180daysAgo',
                'prev_end' => '91daysAgo',
                'label' => 'Last 90 days',
                'compare_label' => 'vs previous 90 days',
            ],
            '6months' => [
                'start' => $today->copy()->subMonths(6)->format('Y-m-d'),
                'end' => 'today',
                'prev_start' => $today->copy()->subMonths(12)->format('Y-m-d'),
                'prev_end' => $today->copy()->subMonths(6)->subDay()->format('Y-m-d'),
                'label' => 'Last 6 months',
                'compare_label' => 'vs previous 6 months',
            ],
            '12months' => [
                'start' => $today->copy()->subMonths(12)->format('Y-m-d'),
                'end' => 'today',
                'prev_start' => $today->copy()->subMonths(24)->format('Y-m-d'),
                'prev_end' => $today->copy()->subMonths(12)->subDay()->format('Y-m-d'),
                'label' => 'Last 12 months',
                'compare_label' => 'vs previous 12 months',
            ],
            'this_month' => [
                'start' => $today->copy()->startOfMonth()->format('Y-m-d'),
                'end' => 'today',
                'prev_start' => $today->copy()->subMonth()->startOfMonth()->format('Y-m-d'),
                'prev_end' => $today->copy()->subMonth()->endOfMonth()->format('Y-m-d'),
                'label' => 'This month',
                'compare_label' => 'vs last month',
            ],
            'last_month' => [
                'start' => $today->copy()->subMonth()->startOfMonth()->format('Y-m-d'),
                'end' => $today->copy()->subMonth()->endOfMonth()->format('Y-m-d'),
                'prev_start' => $today->copy()->subMonths(2)->startOfMonth()->format('Y-m-d'),
                'prev_end' => $today->copy()->subMonths(2)->endOfMonth()->format('Y-m-d'),
                'label' => 'Last month',
                'compare_label' => 'vs month before',
            ],
            'this_year' => [
                'start' => $today->copy()->startOfYear()->format('Y-m-d'),
                'end' => 'today',
                'prev_start' => $today->copy()->subYear()->startOfYear()->format('Y-m-d'),
                'prev_end' => $today->copy()->subYear()->endOfYear()->format('Y-m-d'),
                'label' => 'This year',
                'compare_label' => 'vs last year',
            ],
            default => [
                'start' => '30daysAgo',
                'end' => 'today',
                'prev_start' => '60daysAgo',
                'prev_end' => '31daysAgo',
                'label' => 'Last 30 days',
                'compare_label' => 'vs previous 30 days',
            ],
        };
    }

    // ── Dashboard Overview ──────────────────────────────────────────

    public function dashboardOverview(string $startDate, string $endDate, string $prevStart, string $prevEnd): array
    {
        return Cache::remember("ga4.overview.{$this->property}.{$startDate}.{$endDate}", now()->addMinutes(15), function () use ($startDate, $endDate, $prevStart, $prevEnd) {
            $metricNames = [
                ['name' => 'sessions'],
                ['name' => 'activeUsers'],
                ['name' => 'newUsers'],
                ['name' => 'screenPageViews'],
                ['name' => 'engagementRate'],
                ['name' => 'bounceRate'],
                ['name' => 'averageSessionDuration'],
                ['name' => 'sessionsPerUser'],
            ];

            $current = $this->runReport(
                dimensions: [['name' => 'date']],
                metrics: $metricNames,
                startDate: $startDate,
                endDate: $endDate,
            );

            $previous = $this->runReport(
                dimensions: [['name' => 'date']],
                metrics: $metricNames,
                startDate: $prevStart,
                endDate: $prevEnd,
            );

            $sumKeys = ['sessions', 'activeUsers', 'newUsers', 'screenPageViews'];
            $avgKeys = ['engagementRate', 'bounceRate', 'averageSessionDuration', 'sessionsPerUser'];

            $currentTotals = $this->sumMetrics($current['rows'], $sumKeys);
            foreach ($avgKeys as $key) {
                $currentTotals[$key] = $this->avgMetric($current['rows'], $key);
            }

            $previousTotals = $this->sumMetrics($previous['rows'], $sumKeys);
            foreach ($avgKeys as $key) {
                $previousTotals[$key] = $this->avgMetric($previous['rows'], $key);
            }

            return [
                'current' => $currentTotals,
                'previous' => $previousTotals,
                'delta' => $this->calcDelta($currentTotals, $previousTotals),
                'chart' => $this->buildChartData($current['rows']),
            ];
        });
    }

    // ── Top Pages ───────────────────────────────────────────────────

    public function topPages(string $startDate, string $endDate): array
    {
        return Cache::remember("ga4.pages.{$this->property}.{$startDate}.{$endDate}", now()->addMinutes(15), function () use ($startDate, $endDate) {
            return $this->runReport(
                dimensions: [['name' => 'pagePath'], ['name' => 'pageTitle']],
                metrics: [
                    ['name' => 'screenPageViews'],
                    ['name' => 'averageSessionDuration'],
                    ['name' => 'bounceRate'],
                ],
                startDate: $startDate,
                endDate: $endDate,
            );
        });
    }

    // ── Pages Detailed (for drill-down page) ──────────────────────

    public function pagesDetailed(string $startDate, string $endDate): array
    {
        return Cache::remember("ga4.pages_detailed.{$this->property}.{$startDate}.{$endDate}", now()->addMinutes(15), function () use ($startDate, $endDate) {
            return $this->runReport(
                dimensions: [['name' => 'pageTitle']],
                metrics: [
                    ['name' => 'screenPageViews'],
                    ['name' => 'activeUsers'],
                    ['name' => 'screenPageViewsPerUser'],
                    ['name' => 'userEngagementDuration'],
                    ['name' => 'eventCount'],
                ],
                startDate: $startDate,
                endDate: $endDate,
            );
        });
    }

    // ── Pages Chart (daily time series by page) ─────────────────

    public function pagesChart(string $startDate, string $endDate): array
    {
        return Cache::remember("ga4.pages_chart.{$this->property}.{$startDate}.{$endDate}", now()->addMinutes(15), function () use ($startDate, $endDate) {
            $report = $this->runReport(
                dimensions: [['name' => 'date'], ['name' => 'pageTitle']],
                metrics: [['name' => 'screenPageViews']],
                startDate: $startDate,
                endDate: $endDate,
            );

            // Sum views per page title to find top 5
            $pageTotals = [];
            foreach ($report['rows'] as $row) {
                $title = $row['pageTitle'];
                $pageTotals[$title] = ($pageTotals[$title] ?? 0) + (int) $row['screenPageViews'];
            }
            arsort($pageTotals);
            $topPages = array_slice(array_keys($pageTotals), 0, 10);

            // Build per-page daily data
            $dailyData = []; // [pageTitle => [date => views]]
            foreach ($report['rows'] as $row) {
                if (in_array($row['pageTitle'], $topPages)) {
                    $dailyData[$row['pageTitle']][$row['date']] = (int) $row['screenPageViews'];
                }
            }

            // Collect all dates and sort
            $allDates = [];
            foreach ($report['rows'] as $row) {
                $allDates[$row['date']] = true;
            }
            ksort($allDates);
            $sortedDates = array_keys($allDates);

            // Format labels as MM/DD
            $labels = array_map(fn ($d) => substr($d, 4, 2) . '/' . substr($d, 6, 2), $sortedDates);

            // Build datasets
            $datasets = [];
            foreach ($topPages as $page) {
                $data = [];
                foreach ($sortedDates as $date) {
                    $data[] = $dailyData[$page][$date] ?? 0;
                }
                $datasets[] = ['name' => $page, 'data' => $data];
            }

            return ['labels' => $labels, 'datasets' => $datasets];
        });
    }

    // ── Traffic Sources ─────────────────────────────────────────────

    public function trafficSources(string $startDate, string $endDate): array
    {
        return Cache::remember("ga4.traffic.{$this->property}.{$startDate}.{$endDate}", now()->addMinutes(15), function () use ($startDate, $endDate) {
            return $this->runReport(
                dimensions: [['name' => 'sessionSource'], ['name' => 'sessionMedium']],
                metrics: [
                    ['name' => 'sessions'],
                    ['name' => 'newUsers'],
                ],
                startDate: $startDate,
                endDate: $endDate,
            );
        });
    }

    // ── Device Breakdown ────────────────────────────────────────────

    public function devices(string $startDate, string $endDate): array
    {
        return Cache::remember("ga4.devices.{$this->property}.{$startDate}.{$endDate}", now()->addMinutes(15), function () use ($startDate, $endDate) {
            return $this->runReport(
                dimensions: [['name' => 'deviceCategory']],
                metrics: [['name' => 'sessions']],
                startDate: $startDate,
                endDate: $endDate,
            );
        });
    }

    // ── Geography ───────────────────────────────────────────────────

    public function geography(string $startDate, string $endDate): array
    {
        return Cache::remember("ga4.geo.{$this->property}.{$startDate}.{$endDate}", now()->addMinutes(15), function () use ($startDate, $endDate) {
            return $this->runReport(
                dimensions: [['name' => 'country']],
                metrics: [['name' => 'sessions']],
                startDate: $startDate,
                endDate: $endDate,
            );
        });
    }

    // ── Events ──────────────────────────────────────────────────────

    public function events(string $startDate, string $endDate): array
    {
        return Cache::remember("ga4.events.{$this->property}.{$startDate}.{$endDate}", now()->addMinutes(15), function () use ($startDate, $endDate) {
            return $this->runReport(
                dimensions: [['name' => 'eventName']],
                metrics: [['name' => 'eventCount']],
                startDate: $startDate,
                endDate: $endDate,
            );
        });
    }

    // ── Insights Generator ──────────────────────────────────────────

    public function generateInsights(array $overview, array $traffic, array $devices): array
    {
        $insights = [];
        $sessions = $overview['current']['sessions'] ?? 0;
        $sessionsDelta = $overview['delta']['sessions'] ?? 0;

        // Big drop in sessions — highest priority
        if ($sessionsDelta < -20) {
            $insights[] = [
                'type' => 'warning',
                'text' => 'Sessions dropped ' . abs($sessionsDelta) . '% compared to the previous period. Investigate traffic sources for changes.',
            ];
        }

        // Low session warning
        if ($sessions < 10) {
            $insights[] = [
                'type' => 'warning',
                'text' => 'Traffic is very low this period. Trends will stabilize as visits increase.',
            ];
        }

        // Single source dependency
        if (count($traffic['rows']) === 1) {
            $insights[] = [
                'type' => 'warning',
                'text' => 'All your traffic comes from a single source. Diversifying traffic sources reduces risk.',
            ];
        }

        // Mobile traffic check
        $deviceMap = [];
        foreach ($devices['rows'] as $d) {
            $deviceMap[strtolower($d['deviceCategory'])] = (int) $d['sessions'];
        }
        $total = array_sum($deviceMap) ?: 1;
        $mobilePct = round(($deviceMap['mobile'] ?? 0) / $total * 100);
        if ($mobilePct < 20 && $total > 5) {
            $insights[] = [
                'type' => 'opportunity',
                'text' => "Only {$mobilePct}% of traffic is mobile. Most websites see 50%+ mobile — consider mobile optimization.",
            ];
        }

        // Big growth — opportunity to double down
        if ($sessionsDelta > 30) {
            $insights[] = [
                'type' => 'success',
                'text' => 'Sessions grew ' . $sessionsDelta . '% compared to the previous period. Keep doing what works.',
            ];
        }

        // Top traffic source — always useful context
        if (!empty($traffic['rows'])) {
            $top = $traffic['rows'][0];
            $source = $top['sessionSource'];
            if ($top['sessionMedium'] !== '(none)') {
                $source .= ' / ' . $top['sessionMedium'];
            }
            $insights[] = [
                'type' => 'info',
                'text' => "{$source} is your top traffic source with " . number_format($top['sessions']) . " sessions.",
            ];
        }

        return array_slice($insights, 0, 3);
    }

    // ── Core Report Runner ──────────────────────────────────────────

    private function runReport(array $dimensions, array $metrics, string $startDate, string $endDate): array
    {
        $request = new RunReportRequest([
            'property' => $this->property,
            'date_ranges' => [
                new DateRange(['start_date' => $startDate, 'end_date' => $endDate]),
            ],
            'dimensions' => array_map(fn ($d) => new Dimension($d), $dimensions),
            'metrics' => array_map(fn ($m) => new Metric($m), $metrics),
        ]);

        $response = $this->client->runReport($request);

        $dimensionHeaders = [];
        foreach ($response->getDimensionHeaders() as $header) {
            $dimensionHeaders[] = $header->getName();
        }

        $metricHeaders = [];
        foreach ($response->getMetricHeaders() as $header) {
            $metricHeaders[] = $header->getName();
        }

        $rows = [];
        foreach ($response->getRows() as $row) {
            $r = [];
            foreach ($dimensionHeaders as $i => $name) {
                $r[$name] = $row->getDimensionValues()[$i]->getValue();
            }
            foreach ($metricHeaders as $i => $name) {
                $r[$name] = $row->getMetricValues()[$i]->getValue();
            }
            $rows[] = $r;
        }

        return ['headers' => array_merge($dimensionHeaders, $metricHeaders), 'rows' => $rows];
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function sumMetrics(array $rows, array $keys): array
    {
        $totals = array_fill_keys($keys, 0);
        foreach ($rows as $row) {
            foreach ($keys as $key) {
                $totals[$key] += (float) ($row[$key] ?? 0);
            }
        }
        return $totals;
    }

    private function avgMetric(array $rows, string $key): float
    {
        if (empty($rows)) {
            return 0;
        }
        $sum = array_sum(array_map(fn ($r) => (float) ($r[$key] ?? 0), $rows));
        return round($sum / count($rows), 4);
    }

    private function calcDelta(array $current, array $previous): array
    {
        $delta = [];
        foreach ($current as $key => $value) {
            $prev = $previous[$key] ?? 0;
            if ($prev > 0) {
                $delta[$key] = round((($value - $prev) / $prev) * 100, 1);
            } else {
                $delta[$key] = $value > 0 ? 100.0 : 0.0;
            }
        }
        return $delta;
    }

    private function buildChartData(array $rows): array
    {
        usort($rows, fn ($a, $b) => ($a['date'] ?? '') <=> ($b['date'] ?? ''));

        $labels = [];
        $sessions = [];
        $users = [];
        $pageviews = [];

        foreach ($rows as $row) {
            $d = $row['date'] ?? '';
            $labels[] = substr($d, 4, 2) . '/' . substr($d, 6, 2);
            $sessions[] = (int) ($row['sessions'] ?? 0);
            $users[] = (int) ($row['activeUsers'] ?? 0);
            $pageviews[] = (int) ($row['screenPageViews'] ?? 0);
        }

        return [
            'labels' => $labels,
            'sessions' => $sessions,
            'users' => $users,
            'pageviews' => $pageviews,
        ];
    }
}
