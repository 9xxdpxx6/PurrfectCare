<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Settings\VaccinationTypeController;

Route::resource('vaccination-types', VaccinationTypeController::class)->parameters(['vaccination-types' => 'vaccinationType']);
Route::get('vaccination-types/{vaccinationType}/drugs', [VaccinationTypeController::class, 'getDrugs'])->name('vaccination-types.drugs');
