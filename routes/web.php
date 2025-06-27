<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PetController;
use App\Http\Controllers\Admin\VisitController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\DrugController;
use App\Http\Controllers\Admin\VaccinationController;
use App\Http\Controllers\Admin\LabTestController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\DrugProcurementController;

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

// Маршруты админ-панели
Route::middleware('web')->prefix('admin')->name('admin.')->group(function () {
// Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Главная страница админ-панели
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // Клиенты
    Route::resource('users', UserController::class);
    Route::get('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');

    // Питомцы
    Route::resource('pets', PetController::class);

    // Приемы
    Route::resource('visits', VisitController::class);

    // Заказы
    Route::resource('orders', OrderController::class);

    // Препараты
    Route::resource('drugs', DrugController::class);

    // Поставки препаратов
    Route::resource('drug-procurements', DrugProcurementController::class);

    // Вакцинации
    Route::resource('vaccinations', VaccinationController::class);

    // Лабораторные анализы
    Route::resource('lab-tests', LabTestController::class);

    // Сотрудники
    Route::resource('employees', EmployeeController::class);
    Route::get('employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.resetPassword');

    // Услуги
    Route::resource('services', ServiceController::class);

    // Филиалы
    Route::resource('branches', BranchController::class);

    // Поставщики
    Route::resource('suppliers', SupplierController::class);

    // Расписания
    Route::resource('schedules', ScheduleController::class);
    Route::get('schedules-week/create', [ScheduleController::class, 'createWeek'])->name('schedules.create-week');
    Route::post('schedules-week', [ScheduleController::class, 'storeWeek'])->name('schedules.store-week');
});

//Route::middleware('auth')->group(function() {
//    Route::get('/dashboard', [ClientController::class, 'dashboard']);
//    Route::resource('pets', PetController::class);
//});


Route::get('/login', function () {
    return 1111;
    // return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return 1111;
    // return view('auth.register');
});