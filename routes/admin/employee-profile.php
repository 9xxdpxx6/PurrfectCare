<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EmployeeProfileController;

Route::prefix('employees')->name('employees.')->group(function () {
    Route::get('/profile', [EmployeeProfileController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [EmployeeProfileController::class, 'editProfile'])->name('profile.edit');
    Route::patch('/profile', [EmployeeProfileController::class, 'updateProfile'])->name('profile.update');
});
