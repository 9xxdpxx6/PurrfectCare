<?php

use Illuminate\Support\Facades\Route;

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


//Route::prefix('admin')->middleware(['auth:admin'])->group(function() {
//    Route::get('/visits', [AdminController::class, 'visits']);
//    Route::post('/services', [ServiceController::class, 'store']);
//});
