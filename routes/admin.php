<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BranchController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Маршруты для филиалов
Route::resource('branches', BranchController::class)->names([
    'index' => 'admin.branches.index',
    'create' => 'admin.branches.create',
    'store' => 'admin.branches.store',
    'show' => 'admin.branches.show',
    'edit' => 'admin.branches.edit',
    'update' => 'admin.branches.update',
    'destroy' => 'admin.branches.destroy',
]);

//Route::prefix('admin')->middleware(['auth:admin'])->group(function() {
//    Route::get('/visits', [AdminController::class, 'visits']);
//    Route::post('/services', [ServiceController::class, 'store']);
//});
