<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PetController;

Route::get('pets/client-options', [PetController::class, 'clientOptions'])->name('pets.client-options');
Route::get('pets/breed-options', [PetController::class, 'breedOptions'])->name('pets.breed-options');
Route::get('pets/owner-options', [PetController::class, 'ownerOptions'])->name('pets.owner-options');
Route::get('pets/export', [PetController::class, 'export'])->name('pets.export');
Route::get('pets/{pet}/export-medical-history', [PetController::class, 'exportMedicalHistory'])->name('pets.export-medical-history');
Route::resource('pets', PetController::class);


