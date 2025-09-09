<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ServiceController;

Route::get('services/branch-options', [ServiceController::class, 'branchOptions'])->name('services.branch-options');
Route::get('services/export', [ServiceController::class, 'export'])->name('services.export');
Route::resource('services', ServiceController::class);


