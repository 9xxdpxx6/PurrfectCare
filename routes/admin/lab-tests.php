<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Settings\LabTestTypeController;
use App\Http\Controllers\Admin\Settings\LabTestParamController;

Route::resource('lab-tests/types', LabTestTypeController::class)->parameters(['types' => 'type'])->names('lab-tests.types');
Route::resource('lab-tests/params', LabTestParamController::class)->parameters(['params' => 'param'])->names('lab-tests.params');
