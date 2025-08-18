<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StatisticsController;

Route::prefix('statistics')->name('statistics.')->group(function () {
    Route::get('/', [StatisticsController::class, 'dashboard'])->name('dashboard');
    Route::get('/financial', [StatisticsController::class, 'financial'])->name('financial');
    Route::get('/operational', [StatisticsController::class, 'operational'])->name('operational');
    Route::get('/clients', [StatisticsController::class, 'clients'])->name('clients');
    Route::get('/medical', [StatisticsController::class, 'medical'])->name('medical');
    Route::get('/conversion', [StatisticsController::class, 'conversion'])->name('conversion');
});


