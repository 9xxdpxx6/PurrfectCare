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
    Route::get('pets/owner-options', [PetController::class, 'ownerOptions'])->name('pets.owner-options');
    Route::resource('pets', PetController::class);

    // Приемы
    Route::get('visits/client-options', [\App\Http\Controllers\Admin\VisitController::class, 'clientOptions'])->name('visits.client-options');
    Route::get('visits/pet-options', [\App\Http\Controllers\Admin\VisitController::class, 'petOptions'])->name('visits.pet-options');
    Route::get('visits/schedule-options', [\App\Http\Controllers\Admin\VisitController::class, 'scheduleOptions'])->name('visits.schedule-options');
    Route::get('visits/status-options', [\App\Http\Controllers\Admin\VisitController::class, 'statusOptions'])->name('visits.status-options');
    Route::get('visits/symptom-options', [\App\Http\Controllers\Admin\VisitController::class, 'symptomOptions'])->name('visits.symptom-options');
    Route::get('visits/diagnosis-options', [\App\Http\Controllers\Admin\VisitController::class, 'diagnosisOptions'])->name('visits.diagnosis-options');
    Route::resource('visits', VisitController::class);

    // Заказы
    Route::resource('orders', OrderController::class);

    // Препараты
    Route::get('drugs/supplier-options', [DrugController::class, 'supplierOptions'])->name('drugs.supplier-options');
    Route::resource('drugs', DrugController::class);

    // Поставки препаратов
    Route::get('drug-procurements/supplier-options', [DrugProcurementController::class, 'supplierOptions'])->name('drug-procurements.supplier-options');
    Route::get('drug-procurements/drug-options', [DrugProcurementController::class, 'drugOptions'])->name('drug-procurements.drug-options');
    Route::resource('drug-procurements', DrugProcurementController::class);

    // Вакцинации
    Route::get('vaccinations/pet-options', [VaccinationController::class, 'petOptions'])->name('vaccinations.pet-options');
    Route::get('vaccinations/veterinarian-options', [VaccinationController::class, 'veterinarianOptions'])->name('vaccinations.veterinarian-options');
    Route::get('vaccinations/drug-options', [VaccinationController::class, 'drugOptions'])->name('vaccinations.drug-options');
    Route::resource('vaccinations', VaccinationController::class);

    // Лабораторные анализы
    Route::resource('lab-tests', LabTestController::class);

    // Сотрудники
    Route::get('employees/specialty-options', [EmployeeController::class, 'specialtyOptions'])->name('employees.specialty-options');
    Route::resource('employees', EmployeeController::class);
    Route::get('employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.resetPassword');

    // Услуги
    Route::get('services/branch-options', [ServiceController::class, 'branchOptions'])->name('services.branch-options');
    Route::resource('services', ServiceController::class);

    // Филиалы
    Route::resource('branches', BranchController::class);

    // Поставщики
    Route::resource('suppliers', SupplierController::class);

    // Расписания
    Route::get('schedules/veterinarian-options', [ScheduleController::class, 'veterinarianOptions'])->name('schedules.veterinarian-options');
    Route::get('schedules/branch-options', [ScheduleController::class, 'branchOptions'])->name('schedules.branch-options');
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