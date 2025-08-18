<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ServiceController;

Route::get('services/branch-options', [ServiceController::class, 'branchOptions'])->name('services.branch-options');
Route::resource('services', ServiceController::class);


