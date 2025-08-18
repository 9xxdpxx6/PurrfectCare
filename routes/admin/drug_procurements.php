<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DrugProcurementController;

Route::get('drug-procurements/supplier-options', [DrugProcurementController::class, 'supplierOptions'])->name('drug-procurements.supplier-options');
Route::get('drug-procurements/drug-options', [DrugProcurementController::class, 'drugOptions'])->name('drug-procurements.drug-options');
Route::resource('drug-procurements', DrugProcurementController::class);


