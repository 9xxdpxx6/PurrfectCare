<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RoleController;

Route::get('roles/options', [RoleController::class, 'options'])->name('roles.options');
Route::resource('roles', RoleController::class);
