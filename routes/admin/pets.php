<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PetController;

Route::get('pets/owner-options', [PetController::class, 'ownerOptions'])->name('pets.owner-options');
Route::resource('pets', PetController::class);


