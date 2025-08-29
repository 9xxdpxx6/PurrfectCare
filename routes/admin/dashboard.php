<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

// Тестовые маршруты для проверки страниц ошибок (удалить в продакшене)
Route::get('/test/404', function () {
    abort(404, 'Тестовая страница 404');
})->name('admin.test.404');

Route::get('/test/403', function () {
    abort(403, 'Тестовая страница 403');
})->name('admin.test.403');

Route::get('/test/500', function () {
    abort(500, 'Тестовая страница 500');
})->name('admin.test.500');

Route::get('/test/419', function () {
    abort(419, 'Тестовая страница 419');
})->name('admin.test.419');
