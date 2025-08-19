<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Settings\MainSettingsController as SettingsSettingsController;
use App\Http\Controllers\Admin\Settings\StatusController;
use App\Http\Controllers\Admin\Settings\UnitController;
use App\Http\Controllers\Admin\Settings\BranchController as SettingsBranchController;
use App\Http\Controllers\Admin\Settings\SpecialtyController;
use App\Http\Controllers\Admin\Settings\SpeciesController;
use App\Http\Controllers\Admin\Settings\BreedController;
use App\Http\Controllers\Admin\Settings\SupplierController as SettingsSupplierController;
use App\Http\Controllers\Admin\Settings\DictionaryDiagnosisController;
use App\Http\Controllers\Admin\Settings\DictionarySymptomController;

Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsSettingsController::class, 'index'])->name('index');

    // Системные настройки
    Route::resource('statuses', StatusController::class)->parameters(['statuses' => 'status']);
    Route::resource('units', UnitController::class)->parameters(['units' => 'unit']);
    Route::resource('branches', SettingsBranchController::class)->parameters(['branches' => 'branch']);
    Route::resource('specialties', SpecialtyController::class)->parameters(['specialties' => 'specialty']);

    // Настройки животных
    Route::resource('species', SpeciesController::class)->parameters(['species' => 'species']);
    Route::resource('breeds', BreedController::class)->parameters(['breeds' => 'breed']);

    // Справочники
    Route::resource('diagnoses', DictionaryDiagnosisController::class)->parameters(['diagnoses' => 'dictionaryDiagnosis']);
    Route::resource('symptoms', DictionarySymptomController::class)->parameters(['symptoms' => 'dictionarySymptom']);

    // Поставщики
    Route::resource('suppliers', SettingsSupplierController::class)->parameters(['suppliers' => 'supplier']);
});


