<?php

use App\Http\Controllers\Api\AnalyticsApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('analytics')->group(function () {
    Route::get('/properties', [AnalyticsApiController::class, 'properties']);
    Route::get('/{propertyId}/dashboard', [AnalyticsApiController::class, 'dashboard']);
    Route::get('/{propertyId}/overview', [AnalyticsApiController::class, 'overview']);
    Route::get('/{propertyId}/top-pages', [AnalyticsApiController::class, 'topPages']);
    Route::get('/{propertyId}/traffic-sources', [AnalyticsApiController::class, 'trafficSources']);
    Route::get('/{propertyId}/devices', [AnalyticsApiController::class, 'devices']);
    Route::get('/{propertyId}/geography', [AnalyticsApiController::class, 'geography']);
    Route::get('/{propertyId}/events', [AnalyticsApiController::class, 'events']);
    Route::get('/{propertyId}/pages', [AnalyticsApiController::class, 'pages']);
    Route::get('/{propertyId}/insights', [AnalyticsApiController::class, 'insights']);
});
