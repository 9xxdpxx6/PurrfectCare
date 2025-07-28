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
use App\Http\Controllers\Admin\SettingsController;

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
    Route::get('visits/client-options', [VisitController::class, 'clientOptions'])->name('visits.client-options');
    Route::get('visits/pet-options', [VisitController::class, 'petOptions'])->name('visits.pet-options');
    Route::get('visits/schedule-options', [VisitController::class, 'scheduleOptions'])->name('visits.schedule-options');
    Route::get('visits/status-options', [VisitController::class, 'statusOptions'])->name('visits.status-options');
    Route::get('visits/symptom-options', [VisitController::class, 'symptomOptions'])->name('visits.symptom-options');
    Route::get('visits/diagnosis-options', [VisitController::class, 'diagnosisOptions'])->name('visits.diagnosis-options');
    Route::resource('visits', VisitController::class);

    // Заказы
    Route::get('orders/client-options', [OrderController::class, 'clientOptions'])->name('orders.client-options');
    Route::get('orders/pet-options', [OrderController::class, 'petOptions'])->name('orders.pet-options');
    Route::get('orders/status-options', [OrderController::class, 'statusOptions'])->name('orders.status-options');
    Route::get('orders/branch-options', [OrderController::class, 'branchOptions'])->name('orders.branch-options');
    Route::get('orders/manager-options', [OrderController::class, 'managerOptions'])->name('orders.manager-options');
    Route::get('orders/service-options', [OrderController::class, 'orderServiceOptions'])->name('orders.service-options');
    Route::get('orders/drug-options', [OrderController::class, 'orderDrugOptions'])->name('orders.drug-options');
    Route::get('orders/lab-test-options', [OrderController::class, 'labTestOptions'])->name('orders.lab-test-options');
    Route::get('orders/vaccination-options', [OrderController::class, 'vaccinationOptions'])->name('orders.vaccination-options');
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
    Route::get('lab-tests/pet-options', [LabTestController::class, 'petOptions'])->name('lab-tests.pet-options');
    Route::get('lab-tests/veterinarian-options', [LabTestController::class, 'veterinarianOptions'])->name('lab-tests.veterinarian-options');
    Route::get('lab-tests/lab-test-type-options', [LabTestController::class, 'labTestTypeOptions'])->name('lab-tests.lab-test-type-options');
    Route::get('lab-tests/lab-test-param-options', [LabTestController::class, 'labTestParamOptions'])->name('lab-tests.lab-test-param-options');
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
    
    Route::get('vaccinations/{vaccination}/drugs', [VaccinationController::class, 'getDrugs'])->name('vaccinations.drugs');
    
    // Настройки
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        
        // Lab Test Types
        Route::get('/lab-test-types', [SettingsController::class, 'labTestTypes'])->name('lab-test-types');
        Route::post('/lab-test-types', [SettingsController::class, 'storeLabTestType'])->name('lab-test-types.store');
        Route::put('/lab-test-types/{labTestType}', [SettingsController::class, 'updateLabTestType'])->name('lab-test-types.update');
        Route::delete('/lab-test-types/{labTestType}', [SettingsController::class, 'destroyLabTestType'])->name('lab-test-types.destroy');
        
        // Lab Test Params
        Route::get('/lab-test-params', [SettingsController::class, 'labTestParams'])->name('lab-test-params');
        Route::post('/lab-test-params', [SettingsController::class, 'storeLabTestParam'])->name('lab-test-params.store');
        Route::put('/lab-test-params/{labTestParam}', [SettingsController::class, 'updateLabTestParam'])->name('lab-test-params.update');
        Route::delete('/lab-test-params/{labTestParam}', [SettingsController::class, 'destroyLabTestParam'])->name('lab-test-params.destroy');
        
        // Statuses
        Route::get('/statuses', [SettingsController::class, 'statuses'])->name('statuses');
        Route::post('/statuses', [SettingsController::class, 'storeStatus'])->name('statuses.store');
        Route::put('/statuses/{status}', [SettingsController::class, 'updateStatus'])->name('statuses.update');
        Route::delete('/statuses/{status}', [SettingsController::class, 'destroyStatus'])->name('statuses.destroy');
        
        // Units
        Route::get('/units', [SettingsController::class, 'units'])->name('units');
        Route::post('/units', [SettingsController::class, 'storeUnit'])->name('units.store');
        Route::put('/units/{unit}', [SettingsController::class, 'updateUnit'])->name('units.update');
        Route::delete('/units/{unit}', [SettingsController::class, 'destroyUnit'])->name('units.destroy');
        
        // Branches
        Route::get('/branches', [SettingsController::class, 'branches'])->name('branches');
        Route::post('/branches', [SettingsController::class, 'storeBranch'])->name('branches.store');
        Route::put('/branches/{branch}', [SettingsController::class, 'updateBranch'])->name('branches.update');
        Route::delete('/branches/{branch}', [SettingsController::class, 'destroyBranch'])->name('branches.destroy');
        
        // Specialties
        Route::get('/specialties', [SettingsController::class, 'specialties'])->name('specialties');
        Route::post('/specialties', [SettingsController::class, 'storeSpecialty'])->name('specialties.store');
        Route::put('/specialties/{specialty}', [SettingsController::class, 'updateSpecialty'])->name('specialties.update');
        Route::delete('/specialties/{specialty}', [SettingsController::class, 'destroySpecialty'])->name('specialties.destroy');
        
        // Species
        Route::get('/species', [SettingsController::class, 'species'])->name('species');
        Route::post('/species', [SettingsController::class, 'storeSpecies'])->name('species.store');
        Route::put('/species/{species}', [SettingsController::class, 'updateSpecies'])->name('species.update');
        Route::delete('/species/{species}', [SettingsController::class, 'destroySpecies'])->name('species.destroy');
        
        // Breeds
        Route::get('/breeds', [SettingsController::class, 'breeds'])->name('breeds');
        Route::post('/breeds', [SettingsController::class, 'storeBreed'])->name('breeds.store');
        Route::put('/breeds/{breed}', [SettingsController::class, 'updateBreed'])->name('breeds.update');
        Route::delete('/breeds/{breed}', [SettingsController::class, 'destroyBreed'])->name('breeds.destroy');
        
        // Suppliers
        Route::get('/suppliers', [SettingsController::class, 'suppliers'])->name('suppliers');
        Route::post('/suppliers', [SettingsController::class, 'storeSupplier'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [SettingsController::class, 'updateSupplier'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SettingsController::class, 'destroySupplier'])->name('suppliers.destroy');
        
        // Dictionary Diagnoses
        Route::get('/dictionary-diagnoses', [SettingsController::class, 'dictionaryDiagnoses'])->name('dictionary-diagnoses');
        Route::post('/dictionary-diagnoses', [SettingsController::class, 'storeDictionaryDiagnosis'])->name('dictionary-diagnoses.store');
        Route::put('/dictionary-diagnoses/{dictionaryDiagnosis}', [SettingsController::class, 'updateDictionaryDiagnosis'])->name('dictionary-diagnoses.update');
        Route::delete('/dictionary-diagnoses/{dictionaryDiagnosis}', [SettingsController::class, 'destroyDictionaryDiagnosis'])->name('dictionary-diagnoses.destroy');
        
        // Dictionary Symptoms
        Route::get('/dictionary-symptoms', [SettingsController::class, 'dictionarySymptoms'])->name('dictionary-symptoms');
        Route::post('/dictionary-symptoms', [SettingsController::class, 'storeDictionarySymptom'])->name('dictionary-symptoms.store');
        Route::put('/dictionary-symptoms/{dictionarySymptom}', [SettingsController::class, 'updateDictionarySymptom'])->name('dictionary-symptoms.update');
        Route::delete('/dictionary-symptoms/{dictionarySymptom}', [SettingsController::class, 'destroyDictionarySymptom'])->name('dictionary-symptoms.destroy');
    });
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