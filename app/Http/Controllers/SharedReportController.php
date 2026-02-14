<?php

namespace App\Http\Controllers;

use App\Models\SharedReport;
use Illuminate\Http\Request;

class SharedReportController extends Controller
{
    public function store(Request $request, string $propertyId)
    {
        $validated = $request->validate([
            'widget_type' => 'required|in:full_dashboard,overview_chart,stat_cards,devices,countries,events,traffic_sources,top_pages,pages_report',
            'date_range' => 'required|in:today,yesterday,7days,14days,30days,90days,6months,12months,this_month,last_month,this_year',
            'label' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $share = SharedReport::create([
            'property_id' => $propertyId,
            'widget_type' => $validated['widget_type'],
            'date_range' => $validated['date_range'],
            'label' => $validated['label'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        $embedUrl = url('/embed/' . $share->token);

        return response()->json([
            'data' => $share,
            'embed_url' => $embedUrl,
            'iframe_snippet' => '<iframe src="' . $embedUrl . '" width="100%" height="600" frameborder="0" style="border:none;"></iframe>',
        ]);
    }

    public function index(string $propertyId)
    {
        $shares = SharedReport::where('property_id', $propertyId)
            ->orderByDesc('created_at')
            ->get();

        return view('analytics.shares', compact('shares', 'propertyId'));
    }

    public function destroy(SharedReport $share)
    {
        $share->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Share revoked successfully.');
    }
}
