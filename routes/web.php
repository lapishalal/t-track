<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalyticsReportController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('analytics/report', AnalyticsReportController::class)
    ->middleware(['auth', 'verified'])
    ->name('analytics.report');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
