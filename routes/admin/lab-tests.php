<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LabTestController;
use App\Http\Controllers\Admin\Settings\LabTestTypeController;
use App\Http\Controllers\Admin\Settings\LabTestParamController;

// TomSelect опции для анализов (должны быть перед основным маршрутом)
Route::get('lab-tests/pet-options', [LabTestController::class, 'petOptions'])->name('lab-tests.pet-options');
Route::get('lab-tests/veterinarian-options', [LabTestController::class, 'veterinarianOptions'])->name('lab-tests.veterinarian-options');
Route::get('lab-tests/lab-test-type-options', [LabTestController::class, 'labTestTypeOptions'])->name('lab-tests.lab-test-type-options');
Route::get('lab-tests/lab-test-param-options', [LabTestController::class, 'labTestParamOptions'])->name('lab-tests.lab-test-param-options');

Route::resource('lab-tests', LabTestController::class)->names('lab-tests');
Route::resource('lab-tests/types', LabTestTypeController::class)->parameters(['types' => 'type'])->names('lab-tests.types');
Route::resource('lab-tests/params', LabTestParamController::class)->parameters(['params' => 'param'])->names('lab-tests.params');
