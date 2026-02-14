<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AnalyticsDashboardController;
use App\Http\Controllers\EmbedController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SharedReportController;
use Illuminate\Support\Facades\Route;

// ── Public Embed Route ───────────────────────────────────────────
Route::get('/embed/{token}', [EmbedController::class, 'show'])->middleware(['share.token', 'throttle:30,1']);

Route::redirect('/', '/dashboard');

// ── Protected Views ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AnalyticsDashboardController::class, 'properties'])->name('dashboard');
    Route::get('/analytics/{propertyId}', [AnalyticsDashboardController::class, 'dashboard']);
    Route::get('/analytics/{propertyId}/pages', [AnalyticsDashboardController::class, 'pages']);

    Route::post('/analytics/{propertyId}/shares', [SharedReportController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/analytics/{propertyId}/shares', [SharedReportController::class, 'index']);
    Route::delete('/shares/{share}', [SharedReportController::class, 'destroy']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Admin ────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminUserController::class, 'index'])->name('index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
