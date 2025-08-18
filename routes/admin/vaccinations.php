<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VaccinationController;

Route::get('vaccinations/pet-options', [VaccinationController::class, 'petOptions'])->name('vaccinations.pet-options');
Route::get('vaccinations/veterinarian-options', [VaccinationController::class, 'veterinarianOptions'])->name('vaccinations.veterinarian-options');
Route::get('vaccinations/vaccination-type-options', [VaccinationController::class, 'vaccinationTypeOptions'])->name('vaccinations.vaccination-type-options');
Route::get('vaccinations/drug-options', [VaccinationController::class, 'drugOptions'])->name('vaccinations.drug-options');
Route::get('vaccinations/{vaccination}/drugs', [VaccinationController::class, 'getDrugs'])->name('vaccinations.drugs');
Route::resource('vaccinations', VaccinationController::class);


