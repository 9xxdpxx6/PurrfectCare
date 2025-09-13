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
    
    // Export routes
    Route::get('/dashboard/export', [StatisticsController::class, 'exportDashboard'])->name('dashboard.export');
    Route::get('/financial/export', [StatisticsController::class, 'exportFinancial'])->name('financial.export');
    Route::get('/orders/export', [StatisticsController::class, 'exportOrders'])->name('orders.export');
    Route::get('/category-revenue/export', [StatisticsController::class, 'exportCategoryRevenue'])->name('category-revenue.export');
    Route::get('/branch-revenue/export', [StatisticsController::class, 'exportBranchRevenue'])->name('branch-revenue.export');
    Route::get('/period-stats/export', [StatisticsController::class, 'exportPeriodStats'])->name('period-stats.export');
    Route::get('/top-services/export', [StatisticsController::class, 'exportTopServices'])->name('top-services.export');
    Route::get('/medical/export', [StatisticsController::class, 'exportMedical'])->name('medical.export');
    Route::get('/conversion/export', [StatisticsController::class, 'exportConversion'])->name('conversion.export');
    
    // AJAX routes for dynamic loading
    Route::get('/branch-options', [StatisticsController::class, 'branchOptions'])->name('branch-options');
    Route::get('/employee-load', [StatisticsController::class, 'employeeLoad'])->name('employee-load');
});


