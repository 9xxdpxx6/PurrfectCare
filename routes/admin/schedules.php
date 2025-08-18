<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ScheduleController;

Route::get('schedules/veterinarian-options', [ScheduleController::class, 'veterinarianOptions'])->name('schedules.veterinarian-options');
Route::get('schedules/branch-options', [ScheduleController::class, 'branchOptions'])->name('schedules.branch-options');
Route::resource('schedules', ScheduleController::class);
Route::get('schedules-week/create', [ScheduleController::class, 'createWeek'])->name('schedules.create-week');
Route::post('schedules-week', [ScheduleController::class, 'storeWeek'])->name('schedules.store-week');


