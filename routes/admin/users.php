<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;

Route::resource('users', UserController::class);
Route::get('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');


