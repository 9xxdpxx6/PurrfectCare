<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OrderController;

Route::get('orders/client-options', [OrderController::class, 'clientOptions'])->name('orders.client-options');
Route::get('orders/pet-options', [OrderController::class, 'petOptions'])->name('orders.pet-options');
Route::get('orders/status-options', [OrderController::class, 'statusOptions'])->name('orders.status-options');
Route::get('orders/branch-options', [OrderController::class, 'branchOptions'])->name('orders.branch-options');
Route::get('orders/manager-options', [OrderController::class, 'managerOptions'])->name('orders.manager-options');
Route::get('orders/service-options', [OrderController::class, 'orderServiceOptions'])->name('orders.service-options');
Route::get('orders/drug-options', [OrderController::class, 'orderDrugOptions'])->name('orders.drug-options');
Route::get('orders/lab-test-options', [OrderController::class, 'orderLabTestOptions'])->name('orders.lab-test-options');
Route::get('orders/vaccination-options', [OrderController::class, 'orderVaccinationOptions'])->name('orders.vaccination-options');
Route::get('orders/visit-options', [OrderController::class, 'orderVisitOptions'])->name('orders.visit-options');
Route::resource('orders', OrderController::class);


