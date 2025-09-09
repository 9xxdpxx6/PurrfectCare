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
use App\Http\Controllers\Admin\Settings\LabTestTypeController;
use App\Http\Controllers\Admin\Settings\LabTestParamController;
use App\Http\Controllers\Admin\Settings\VaccinationTypeController;

Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsSettingsController::class, 'index'])->name('index');

    // Системные настройки
    Route::resource('statuses', StatusController::class)->parameters(['statuses' => 'status']);
    Route::resource('units', UnitController::class)->parameters(['units' => 'unit']);
    Route::get('branches/export', [SettingsBranchController::class, 'export'])->name('branches.export');
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
    
    // Анализы
    Route::resource('lab-test-types', LabTestTypeController::class)->parameters(['lab-test-types' => 'lab-test-type']);
    Route::resource('lab-test-params', LabTestParamController::class)->parameters(['lab-test-params' => 'lab-test-param']);
    
    // Единицы измерения опции для TomSelect
    Route::get('unit-options', [UnitController::class, 'options'])->name('unit-options');
    // Единицы измерения
    Route::resource('units', UnitController::class)->parameters(['units' => 'unit']);
    Route::get('units/options', [UnitController::class, 'options'])->name('units.options');
    
    // Вакцинации
    Route::resource('vaccination-types', VaccinationTypeController::class)->parameters(['vaccination-types' => 'vaccination-type']);
    Route::get('vaccination-types/{vaccination_type}/drugs', [VaccinationTypeController::class, 'getDrugs'])->name('vaccination-types.drugs');
});


