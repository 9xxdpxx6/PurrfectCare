<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DrugController;

Route::get('drugs/supplier-options', [DrugController::class, 'supplierOptions'])->name('drugs.supplier-options');
Route::get('drugs/export', [DrugController::class, 'export'])->name('drugs.export');
Route::resource('drugs', DrugController::class);


