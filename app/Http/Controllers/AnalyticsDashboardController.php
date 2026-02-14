<?php

namespace App\Http\Controllers;

use App\Services\Analytics\Ga4Service;
use Illuminate\Http\Request;

class AnalyticsDashboardController extends Controller
{
    public function properties(Request $request)
    {
        $user = $request->user();

        if (!$user->is_admin) {
            $assignedIds = $user->assignedAccountIds();

            if (empty($assignedIds)) {
                return view('analytics.pending');
            }

            $accounts = Ga4Service::listProperties();
            $accounts = array_values(array_filter($accounts, fn ($account) => in_array($account['name'], $assignedIds)));
        } else {
            $accounts = Ga4Service::listProperties();
        }

        return view('analytics.properties', compact('accounts'));
    }

    public function dashboard(Request $request, string $propertyId)
    {
        $this->authorizeProperty($request, $propertyId);

        $range = $request->query('range', '30days');
        $customStart = $request->query('start');
        $customEnd = $request->query('end');
        $config = Ga4Service::rangeConfig($range, $customStart, $customEnd);

        $ga = new Ga4Service($propertyId);

        $overview = $ga->dashboardOverview($config['start'], $config['end'], $config['prev_start'], $config['prev_end']);
        $topPages = $ga->topPages($config['start'], $config['end']);
        $traffic = $ga->trafficSources($config['start'], $config['end']);
        $devices = $ga->devices($config['start'], $config['end']);
        $geo = $ga->geography($config['start'], $config['end']);
        $events = $ga->events($config['start'], $config['end']);
        $insights = $ga->generateInsights($overview, $traffic, $devices);

        $rangeLabel = $config['label'];
        $compareLabel = $config['compare_label'];

        return view('analytics.dashboard', compact(
            'propertyId',
            'range',
            'rangeLabel',
            'compareLabel',
            'overview',
            'topPages',
            'traffic',
            'devices',
            'geo',
            'events',
            'insights',
            'customStart',
            'customEnd',
        ));
    }

    public function pages(Request $request, string $propertyId)
    {
        $this->authorizeProperty($request, $propertyId);

        $range = $request->query('range', '30days');
        $customStart = $request->query('start');
        $customEnd = $request->query('end');
        $config = Ga4Service::rangeConfig($range, $customStart, $customEnd);

        $ga = new Ga4Service($propertyId);

        $pagesData = $ga->pagesDetailed($config['start'], $config['end']);
        $pagesChart = $ga->pagesChart($config['start'], $config['end']);

        $rangeLabel = $config['label'];

        return view('analytics.pages', compact(
            'propertyId',
            'range',
            'rangeLabel',
            'pagesData',
            'pagesChart',
            'customStart',
            'customEnd',
        ));
    }

    private function authorizeProperty(Request $request, string $propertyId): void
    {
        $user = $request->user();

        if ($user->is_admin) {
            return;
        }

        $assignedIds = $user->assignedAccountIds();
        $accounts = Ga4Service::listProperties();

        foreach ($accounts as $account) {
            if (!in_array($account['name'], $assignedIds)) {
                continue;
            }
            foreach ($account['properties'] as $property) {
                if ($property['id'] === $propertyId) {
                    return;
                }
            }
        }

        abort(403, 'You do not have access to this property.');
    }
}
