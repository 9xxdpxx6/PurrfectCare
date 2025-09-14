<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Client\ServiceController;
use App\Http\Controllers\Client\AppointmentController;
use App\Http\Controllers\Client\VisitController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Client\PetController;
use App\Http\Controllers\Client\NotificationController;

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

// Главная страница
Route::get('/', [ClientController::class, 'index'])->name('client.index');

// Аутентификация клиентов
Route::get('/login', [ClientController::class, 'login'])->name('client.login');
Route::post('/login', [ClientController::class, 'authenticate'])->name('client.login');
Route::get('/register', [ClientController::class, 'register'])->name('client.register');
Route::post('/register', [ClientController::class, 'store'])->name('client.register');
Route::post('/logout', [ClientController::class, 'logout'])->name('client.logout');

// Личный кабинет (требует аутентификации)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ClientController::class, 'profile'])->name('client.profile');
    Route::post('/profile', [ClientController::class, 'updateProfile'])->name('client.profile.update');
    Route::post('/profile/password', [ClientController::class, 'changePassword'])->name('client.profile.password');
    
    // История визитов
    Route::get('/profile/visits', [VisitController::class, 'index'])->name('client.profile.visits');
    Route::get('/profile/visits/{visit}', [VisitController::class, 'show'])->name('client.profile.visits.show');
    Route::post('/profile/visits/{visit}/cancel', [VisitController::class, 'cancel'])->name('client.profile.visits.cancel');
    Route::post('/profile/visits/{visit}/reschedule', [VisitController::class, 'reschedule'])->name('client.profile.visits.reschedule');
    
    // История заказов
    Route::get('/profile/orders', [OrderController::class, 'index'])->name('client.profile.orders');
    Route::get('/profile/orders/{order}', [OrderController::class, 'show'])->name('client.profile.orders.show');
    Route::post('/profile/orders/{order}/reorder', [OrderController::class, 'reorder'])->name('client.profile.orders.reorder');
    Route::post('/profile/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('client.profile.orders.cancel');
    
    // Управление питомцами
    Route::get('/profile/pets', [PetController::class, 'index'])->name('client.profile.pets');
    Route::get('/profile/pets/create', [PetController::class, 'create'])->name('client.profile.pets.create');
    Route::post('/profile/pets', [PetController::class, 'store'])->name('client.profile.pets.store');
    Route::get('/profile/pets/{pet}/edit', [PetController::class, 'edit'])->name('client.profile.pets.edit');
    Route::put('/profile/pets/{pet}', [PetController::class, 'update'])->name('client.profile.pets.update');
    Route::delete('/profile/pets/{pet}', [PetController::class, 'destroy'])->name('client.profile.pets.destroy');
    Route::get('/api/breeds-by-species', [PetController::class, 'getBreedsBySpecies'])->name('client.api.breeds-by-species');
    
    // Уведомления
    Route::get('/notifications', [NotificationController::class, 'index'])->name('client.profile.notifications');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('client.notifications.unread-count');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('client.notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('client.notifications.mark-all-as-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('client.notifications.destroy');
});

// Публичные страницы
Route::get('/about', [ClientController::class, 'about'])->name('client.about');
Route::get('/contacts', [ClientController::class, 'contacts'])->name('client.contacts');




// Маршруты услуг
Route::get('/services', [ServiceController::class, 'index'])->name('client.services');
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('client.services.show');

// Маршруты записи на прием
Route::prefix('appointment')->name('client.appointment.')->group(function () {
    Route::get('/branches', [AppointmentController::class, 'selectBranch'])->name('branches');
    Route::get('/veterinarians', [AppointmentController::class, 'selectVeterinarian'])->name('veterinarians');
    Route::get('/time', [AppointmentController::class, 'selectTime'])->name('time');
    Route::get('/confirm', [AppointmentController::class, 'confirm'])->name('confirm');
    Route::post('/store', [AppointmentController::class, 'store'])->name('store');
    Route::get('/my-appointments', [AppointmentController::class, 'myAppointments'])->name('appointments');
    Route::post('/{visit}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
    Route::get('/available-time', [AppointmentController::class, 'getAvailableTime'])->name('available-time');
});

// Алиас для старой ссылки
Route::get('/appointment', function () {
    return redirect()->route('client.appointment.branches');
})->name('client.appointment');

// Маршруты Telegram бота подключаются через RouteServiceProvider из routes/bot.php