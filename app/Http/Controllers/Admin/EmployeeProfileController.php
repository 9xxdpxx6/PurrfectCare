<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Vaccination;
use App\Models\LabTest;
use App\Models\Schedule;
use App\Models\Visit;
use App\Http\Requests\Admin\Employee\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class EmployeeProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    /**
     * Показать личный кабинет сотрудника
     */
    public function profile(): View
    {
        $employee = auth('admin')->user();
        
        // Получаем статистику сотрудника
        $stats = $this->getEmployeeStats($employee->id);
        
        // Получаем последние активности
        $recentActivities = $this->getRecentActivities($employee->id);
        
        return view('admin.employees.profile.profile', compact('employee', 'stats', 'recentActivities'));
    }

    /**
     * Показать форму редактирования профиля
     */
    public function editProfile(): View
    {
        $employee = auth('admin')->user();
        return view('admin.employees.profile.edit', compact('employee'));
    }

    /**
     * Обновить профиль сотрудника
     */
    public function updateProfile(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $employee = auth('admin')->user();
            $validated = $request->validated();
            
            // Проверяем текущий пароль, если он указан
            if ($request->filled('current_password')) {
                if (!Hash::check($request->current_password, $employee->password)) {
                    return back()
                        ->withInput()
                        ->withErrors(['current_password' => 'Неверный текущий пароль']);
                }
            } else {
                // Если текущий пароль не указан, требуем его
                return back()
                    ->withInput()
                    ->withErrors(['current_password' => 'Необходимо указать текущий пароль для подтверждения изменений']);
            }
            
            // Убираем поля пароля и email из валидированных данных для обновления профиля
            $profileData = collect($validated)->except(['current_password', 'new_password', 'email'])->toArray();
            
            $oldName = $employee->name;
            $oldPhone = $employee->phone;
            
            // Обновляем профиль
            $employee->update($profileData);
            
            $successMessage = 'Профиль успешно обновлен';
            
            // Если указан новый пароль - меняем его
            if ($request->filled('new_password')) {
                $employee->update(['password' => Hash::make($request->new_password)]);
                
                $successMessage = 'Профиль и пароль успешно обновлены';
                
                Log::info('Профиль и пароль сотрудника обновлены', [
                    'employee_id' => $employee->id,
                    'old_name' => $oldName,
                    'new_name' => $employee->name,
                    'old_phone' => $oldPhone,
                    'new_phone' => $employee->phone,
                    'password_changed' => true
                ]);
            } else {
                Log::info('Профиль сотрудника обновлен', [
                    'employee_id' => $employee->id,
                    'old_name' => $oldName,
                    'new_name' => $employee->name,
                    'old_phone' => $oldPhone,
                    'new_phone' => $employee->phone,
                    'password_changed' => false
                ]);
            }
            
            return redirect()->route('admin.employees.profile')
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении профиля сотрудника', [
                'employee_id' => auth('admin')->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при обновлении профиля: ' . $e->getMessage()]);
        }
    }



    /**
     * Получить статистику сотрудника
     */
    private function getEmployeeStats(int $employeeId): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        
        // Базовая статистика
        $totalOrders = Order::where('manager_id', $employeeId);
        $totalVisits = Visit::whereHas('schedule', function($query) use ($employeeId) {
            $query->where('veterinarian_id', $employeeId);
        });
        $totalVaccinations = Vaccination::where('veterinarian_id', $employeeId);
        $totalLabTests = LabTest::where('veterinarian_id', $employeeId);
        $totalSchedules = Schedule::where('veterinarian_id', $employeeId);
        
        // Статистика по дням недели
        $mondayVisits = clone $totalVisits;
        $tuesdayVisits = clone $totalVisits;
        $wednesdayVisits = clone $totalVisits;
        $thursdayVisits = clone $totalVisits;
        $fridayVisits = clone $totalVisits;
        $saturdayVisits = clone $totalVisits;
        $sundayVisits = clone $totalVisits;
        
        $mondayVisits->whereRaw('DAYOFWEEK(starts_at) = 2');
        $tuesdayVisits->whereRaw('DAYOFWEEK(starts_at) = 3');
        $wednesdayVisits->whereRaw('DAYOFWEEK(starts_at) = 4');
        $thursdayVisits->whereRaw('DAYOFWEEK(starts_at) = 5');
        $fridayVisits->whereRaw('DAYOFWEEK(starts_at) = 6');
        $saturdayVisits->whereRaw('DAYOFWEEK(starts_at) = 7');
        $sundayVisits->whereRaw('DAYOFWEEK(starts_at) = 1');
        
        // Статистика по времени суток
        $morningVisits = clone $totalVisits;
        $afternoonVisits = clone $totalVisits;
        $eveningVisits = clone $totalVisits;
        
        $morningVisits->whereRaw('HOUR(starts_at) BETWEEN 8 AND 11');
        $afternoonVisits->whereRaw('HOUR(starts_at) BETWEEN 12 AND 16');
        $eveningVisits->whereRaw('HOUR(starts_at) BETWEEN 17 AND 20');
        
        // Финансовая статистика
        $ordersAmount = Order::where('manager_id', $employeeId)->sum('total');
        $averageOrderAmount = Order::where('manager_id', $employeeId)->avg('total');
        
        // Статистика по типам приёмов (примерная логика)
        $primaryVisits = clone $totalVisits;
        $repeatVisits = clone $totalVisits;
        $emergencyVisits = clone $totalVisits;
        
        // Простая логика: если это первый визит клиента - первичный, иначе повторный
        $primaryVisits->whereHas('client', function($query) {
            $query->whereRaw('visits.created_at = (SELECT MIN(v2.created_at) FROM visits v2 WHERE v2.client_id = visits.client_id)');
        });
        
        // Продуктивность 
        $totalWorkingHours = $totalSchedules->sum(DB::raw('TIMESTAMPDIFF(HOUR, shift_starts_at, shift_ends_at)'));
        
        // Приёмов в час - среднее за рабочие часы
        $visitsPerHour = $totalWorkingHours > 0 ? round($totalVisits->count() / $totalWorkingHours, 2) : 0;
        
        // Эффективность расписания - сколько времени реально занято приёмами
        $totalVisitDuration = $totalVisits->count() * 30; // 30 минут на приём
        $scheduleEfficiency = $totalWorkingHours > 0 ? round(($totalVisitDuration / 60) / $totalWorkingHours * 100, 1) : 0;
        
        // Заполненность графика - сколько потенциальных слотов занято
        // Рассчитываем реальные слоты на основе расписания (каждый слот = 30 минут)
        $totalAvailableSlots = 0;
        foreach ($totalSchedules->get() as $schedule) {
            $startHour = (int) $schedule->shift_starts_at->format('H');
            $endHour = (int) $schedule->shift_ends_at->format('H');
            $hoursPerDay = $endHour - $startHour;
            $slotsPerDay = $hoursPerDay * 2; // 2 слота в час (30 минут каждый)
            $totalAvailableSlots += $slotsPerDay;
        }
        
        $scheduleUtilization = $totalAvailableSlots > 0 ? round($totalVisits->count() / $totalAvailableSlots * 100, 1) : 0;
        
        return [
            // Базовая статистика
            'total_orders' => $totalOrders->count(),
            'total_visits' => $totalVisits->count(),
            'total_vaccinations' => $totalVaccinations->count(),
            'total_lab_tests' => $totalLabTests->count(),
            'total_schedules' => $totalSchedules->count(),
            
            // Текущий период
            'today_visits' => Visit::whereHas('schedule', function($query) use ($employeeId) {
                $query->where('veterinarian_id', $employeeId);
            })->whereDate('starts_at', $today)->count(),
            'this_month_visits' => Visit::whereHas('schedule', function($query) use ($employeeId) {
                $query->where('veterinarian_id', $employeeId);
            })->whereMonth('starts_at', $thisMonth->month)
                ->whereYear('starts_at', $thisMonth->year)->count(),
            'this_month_orders' => Order::where('manager_id', $employeeId)
                ->whereMonth('created_at', $thisMonth->month)
                ->whereYear('created_at', $thisMonth->year)
                ->count(),
            
            // Статистика по дням недели
            'monday_visits' => $mondayVisits->count(),
            'tuesday_visits' => $tuesdayVisits->count(),
            'wednesday_visits' => $wednesdayVisits->count(),
            'thursday_visits' => $thursdayVisits->count(),
            'friday_visits' => $fridayVisits->count(),
            'saturday_visits' => $saturdayVisits->count(),
            'sunday_visits' => $sundayVisits->count(),
            
            // Статистика по времени
            'morning_visits' => $morningVisits->count(),
            'afternoon_visits' => $afternoonVisits->count(),
            'evening_visits' => $eveningVisits->count(),
            
            // Статистика по типам
            'primary_visits' => $primaryVisits->count(),
            'repeat_visits' => $totalVisits->count() - $primaryVisits->count(),
            'emergency_visits' => 0, // Пока оставляем 0, можно добавить логику позже
            
            // Финансовая статистика
            'total_orders_amount' => $ordersAmount,
            'average_order_amount' => $averageOrderAmount,
            
            // Продуктивность
            'visits_per_hour' => $visitsPerHour,
            'schedule_efficiency' => $scheduleEfficiency,
            'schedule_utilization' => $scheduleUtilization,
        ];
    }

    /**
     * Получить последние активности сотрудника
     */
    private function getRecentActivities(int $employeeId): array
    {
        return [
            'recent_visits' => Visit::whereHas('schedule', function($query) use ($employeeId) {
                $query->where('veterinarian_id', $employeeId);
            })->with(['pet.client', 'schedule.branch'])
                ->latest('starts_at')
                ->limit(5)
                ->get(),
            'recent_orders' => Order::where('manager_id', $employeeId)
                ->with(['client', 'pet'])
                ->latest()
                ->limit(5)
                ->get(),
            'recent_vaccinations' => Vaccination::where('veterinarian_id', $employeeId)
                ->with(['pet.client', 'vaccinationType'])
                ->latest('administered_at')
                ->limit(5)
                ->get(),
            'recent_lab_tests' => LabTest::where('veterinarian_id', $employeeId)
                ->with(['pet.client', 'labTestType'])
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }
}
