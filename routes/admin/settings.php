<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Settings\MainSettingsController as SettingsSettingsController;
use App\Http\Controllers\Admin\Settings\LabTestTypeController;
use App\Http\Controllers\Admin\Settings\LabTestParamController;
use App\Http\Controllers\Admin\Settings\StatusController;
use App\Http\Controllers\Admin\Settings\UnitController;
use App\Http\Controllers\Admin\Settings\BranchController as SettingsBranchController;
use App\Http\Controllers\Admin\Settings\SpecialtyController;
use App\Http\Controllers\Admin\Settings\SpeciesController;
use App\Http\Controllers\Admin\Settings\BreedController;
use App\Http\Controllers\Admin\Settings\SupplierController as SettingsSupplierController;
use App\Http\Controllers\Admin\Settings\DictionaryDiagnosisController;
use App\Http\Controllers\Admin\Settings\DictionarySymptomController;
use App\Http\Controllers\Admin\Settings\VaccinationTypeController;

Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsSettingsController::class, 'index'])->name('index');

    Route::prefix('lab-tests')->name('lab-tests.')->group(function () {
        Route::resource('types', LabTestTypeController::class)->parameters(['types' => 'type']);
        Route::resource('params', LabTestParamController::class)->parameters(['params' => 'param']);
    });

    Route::resource('vaccination-types', VaccinationTypeController::class)->parameters(['vaccination-types' => 'vaccinationType']);
    Route::get('vaccination-types/{vaccinationType}/drugs', [VaccinationTypeController::class, 'getDrugs'])->name('vaccination-types.drugs');

    Route::prefix('system')->name('system.')->group(function () {
        Route::resource('statuses', StatusController::class)->parameters(['statuses' => 'status']);
        Route::resource('units', UnitController::class)->parameters(['units' => 'unit']);
        Route::resource('branches', SettingsBranchController::class)->parameters(['branches' => 'branch']);
        Route::resource('specialties', SpecialtyController::class)->parameters(['specialties' => 'specialty']);
    });

    Route::prefix('animals')->name('animals.')->group(function () {
        Route::resource('species', SpeciesController::class)->parameters(['species' => 'species']);
        Route::resource('breeds', BreedController::class)->parameters(['breeds' => 'breed']);
    });

    Route::prefix('dictionary')->name('dictionary.')->group(function () {
        Route::resource('diagnoses', DictionaryDiagnosisController::class)->parameters(['diagnoses' => 'dictionaryDiagnosis']);
        Route::resource('symptoms', DictionarySymptomController::class)->parameters(['symptoms' => 'dictionarySymptom']);
    });

    Route::resource('suppliers', SettingsSupplierController::class)->parameters(['suppliers' => 'supplier']);
});


