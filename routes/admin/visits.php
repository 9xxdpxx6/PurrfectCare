<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VisitController;

Route::get('visits/client-options', [VisitController::class, 'clientOptions'])->name('visits.client-options');
Route::get('visits/pet-options', [VisitController::class, 'petOptions'])->name('visits.pet-options');
Route::get('visits/schedule-options', [VisitController::class, 'scheduleOptions'])->name('visits.schedule-options');
Route::get('visits/status-options', [VisitController::class, 'statusOptions'])->name('visits.status-options');
Route::get('visits/symptom-options', [VisitController::class, 'symptomOptions'])->name('visits.symptom-options');
Route::get('visits/diagnosis-options', [VisitController::class, 'diagnosisOptions'])->name('visits.diagnosis-options');
Route::get('visits/available-time', [VisitController::class, 'getAvailableTime'])->name('visits.available-time');
Route::resource('visits', VisitController::class);


