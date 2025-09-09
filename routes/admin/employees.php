<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EmployeeController;

Route::get('employees/specialty-options', [EmployeeController::class, 'specialtyOptions'])->name('employees.specialty-options');
Route::get('employees/export', [EmployeeController::class, 'export'])->name('employees.export');
Route::get('employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.resetPassword');
Route::resource('employees', EmployeeController::class);


