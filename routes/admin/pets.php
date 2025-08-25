<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PetController;

Route::get('pets/client-options', [PetController::class, 'clientOptions'])->name('pets.client-options');
Route::get('pets/breed-options', [PetController::class, 'breedOptions'])->name('pets.breed-options');
Route::get('pets/owner-options', [PetController::class, 'ownerOptions'])->name('pets.owner-options');
Route::resource('pets', PetController::class);


