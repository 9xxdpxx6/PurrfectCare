<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;

Route::get('users/export', [UserController::class, 'export'])->name('users.export');
Route::get('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');
Route::resource('users', UserController::class);


