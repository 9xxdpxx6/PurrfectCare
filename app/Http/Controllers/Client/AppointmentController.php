<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Pet;
use App\Models\Visit;
use App\Services\Visit\VisitManagementService;
use App\Services\VeterinarianService;
use App\Services\NotificationService;
use App\Http\Requests\Client\Appointment\StoreRequest as StoreAppointmentRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    protected $visitManagementService;
    protected $veterinarianService;
    protected $notificationService;

    public function __construct(VisitManagementService $visitManagementService, VeterinarianService $veterinarianService, NotificationService $notificationService)
    {
        $this->visitManagementService = $visitManagementService;
        $this->veterinarianService = $veterinarianService;
        $this->notificationService = $notificationService;
        $this->middleware('auth');
    }

    /**
     * Выбор филиала
     */
    public function selectBranch(): View
    {
        $branches = Branch::distinct()->get();
        
        return view('client.appointment.branches', compact('branches'));
    }

    /**
     * Выбор ветеринара
     */
    public function selectVeterinarian(Request $request): View
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'search' => 'nullable|string|max:255'
        ]);

        $branch = Branch::findOrFail($request->branch_id);
        
        // Получаем ветеринаров через сервис
        $veterinarians = $this->veterinarianService->getVeterinariansForBranch(
            $request->branch_id, 
            $request->search
        );

        return view('client.appointment.veterinarians', compact('veterinarians', 'branch'));
    }

    /**
     * Выбор времени
     */
    public function selectTime(Request $request): View
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'veterinarian_id' => 'required|exists:employees,id'
        ]);

        $branch = Branch::findOrFail($request->branch_id);
        $veterinarian = Employee::findOrFail($request->veterinarian_id);

        // Получаем расписание ветеринара в выбранном филиале
        $schedules = Schedule::where('veterinarian_id', $request->veterinarian_id)
            ->where('branch_id', $request->branch_id)
            ->where('shift_starts_at', '>=', now())
            ->orderBy('shift_starts_at')
            ->get();

        // Группируем по датам
        $schedulesByDate = $schedules->groupBy(function($schedule) {
            return Carbon::parse($schedule->shift_starts_at)->format('Y-m-d');
        });

        return view('client.appointment.time', compact('schedulesByDate', 'branch', 'veterinarian'));
    }

    /**
     * Получить доступное время для расписания
     */
    public function getAvailableTime(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id'
        ]);

        $schedule = Schedule::findOrFail($request->schedule_id);
        $availableTime = $this->visitManagementService->getAvailableTime($schedule->id);

        return response()->json($availableTime);
    }

    /**
     * Подтверждение записи
     */
    public function confirm(Request $request): View
    {
        try {
            $request->validate([
                'branch_id' => 'required|exists:branches,id',
                'veterinarian_id' => 'required|exists:employees,id',
                'schedule_id' => 'required|exists:schedules,id',
                'time' => 'required|date_format:H:i'
            ]);

            $branch = Branch::findOrFail($request->branch_id);
            $veterinarian = Employee::findOrFail($request->veterinarian_id);
            $schedule = Schedule::findOrFail($request->schedule_id);

            // Получаем питомцев пользователя
            $pets = Pet::where('client_id', Auth::id())->get();

            // Формируем дату и время
            $date = Carbon::parse($schedule->shift_starts_at)->format('Y-m-d');
            $datetime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->time);

            \Log::info('AppointmentController::confirm - Успешно обработано', [
                'branch_id' => $branch->id,
                'veterinarian_id' => $veterinarian->id,
                'schedule_id' => $schedule->id,
                'time' => $request->time,
                'datetime' => $datetime->format('Y-m-d H:i:s')
            ]);

            return view('client.appointment.confirm', compact('branch', 'veterinarian', 'schedule', 'pets', 'datetime'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('AppointmentController::confirm - Ошибка валидации', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('AppointmentController::confirm - Общая ошибка', [
                'message' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            return back()->withErrors(['error' => 'Произошла ошибка при обработке запроса.']);
        }
    }

    /**
     * Создание записи
     */
    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        \Log::info('AppointmentController::store - Начало создания записи', [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
            'pet_id_raw' => $request->input('pet_id'),
            'pet_id_type' => gettype($request->input('pet_id'))
        ]);

        try {
            // Проверяем аутентификацию
            if (!Auth::check()) {
                \Log::error('AppointmentController::store - Пользователь не аутентифицирован');
                return back()->withErrors(['error' => 'Необходимо войти в систему для записи на прием.']);
            }

            // Очищаем время от лишних символов
            $cleanTime = trim($request->time);
            
            // Добавляем поле visit_time для VisitDateTimeProcessingService
            $request->merge(['visit_time' => $cleanTime]);
            
            // Проверяем, что питомец принадлежит пользователю (если указан)
            if ($request->pet_id && $request->pet_id !== '') {
                $pet = Pet::where('id', $request->pet_id)
                    ->where('client_id', Auth::id())
                    ->firstOrFail();
            }

            // Формируем дату и время
            $schedule = Schedule::findOrFail($request->schedule_id);
            $date = Carbon::parse($schedule->shift_starts_at)->format('Y-m-d');
            $startsAt = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $cleanTime);
            
            \Log::info('AppointmentController::store - Формирование даты и времени', [
                'schedule_date' => $date,
                'clean_time' => $cleanTime,
                'starts_at' => $startsAt->format('Y-m-d H:i:s')
            ]);

            // Проверяем конфликты времени
            $conflicts = $this->visitManagementService->checkTimeConflicts(
                $request->schedule_id,
                $cleanTime,
                30 // Стандартная длительность 30 минут
            );

            if (!empty($conflicts)) {
                return back()->withErrors(['time' => 'Выбранное время занято. Пожалуйста, выберите другое время.']);
            }

            // Создаем запись
            $visitData = [
                'client_id' => Auth::id(),
                'pet_id' => ($request->pet_id && $request->pet_id !== '') ? $request->pet_id : null,
                'schedule_id' => $request->schedule_id,
                'starts_at' => $startsAt,
                'complaints' => $request->complaints,
                'status_id' => 1 // Статус "Запланирован"
            ];

            \Log::info('AppointmentController::store - Данные для создания визита', $visitData);

            $visit = $this->visitManagementService->createVisit($visitData, $request);

            \Log::info('AppointmentController::store - Визит создан успешно', ['visit_id' => $visit->id]);

            // Отправляем уведомление администраторам о новой записи (как в боте)
            $this->notificationService->notifyAboutBotBooking($visit);

            return redirect()->route('client.appointment.appointments')
                ->with('success', 'Запись на прием успешно создана!');

        } catch (\Exception $e) {
            \Log::error('AppointmentController::store - Ошибка при создании записи', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return back()->withErrors(['error' => 'Произошла ошибка при создании записи: ' . $e->getMessage()]);
        }
    }

    /**
     * Мои записи
     */
    public function myAppointments(): View
    {
        $visits = Visit::where('client_id', Auth::id())
            ->where('starts_at', '>=', Carbon::now())
            ->with(['pet', 'schedule.veterinarian', 'schedule.branch', 'status'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('client.appointment.my-appointments', compact('visits'));
    }

    /**
     * Отмена записи
     */
    public function cancel(Request $request, Visit $visit): RedirectResponse
    {
        // Проверяем, что запись принадлежит пользователю
        if ($visit->client_id !== Auth::id()) {
            abort(403);
        }

        // Проверяем, можно ли отменить (например, не менее чем за 2 часа)
        if ($visit->starts_at->diffInHours(now()) < 2) {
            return back()->withErrors(['error' => 'Запись можно отменить не менее чем за 2 часа до приема.']);
        }

        try {
            $visit->update(['status_id' => 3]); // Статус "Отменен"
            
            return back()->with('success', 'Запись успешно отменена.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Произошла ошибка при отмене записи.']);
        }
    }
}
