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
            ->where('shift_starts_at', '<=', now()->addDays(30))
            ->orderBy('shift_starts_at')
            ->get();

        // Группируем по датам и генерируем доступные слоты (как в боте)
        $schedulesByDate = $schedules->groupBy(function($schedule) {
            return Carbon::parse($schedule->shift_starts_at)->format('Y-m-d');
        });

        // Генерируем доступные временные слоты для каждой даты
        $availableSlotsByDate = [];
        $userBookingsByDate = [];
        
        foreach ($schedulesByDate as $date => $daySchedules) {
            $availableSlots = $this->generateAvailableTimeSlots($daySchedules, $date);
            if (!empty($availableSlots)) {
                $availableSlotsByDate[$date] = $availableSlots;
            }
            
            // Подсчитываем записи пользователя на эту дату
            $userBookingsByDate[$date] = Visit::where('client_id', Auth::id())
                ->whereDate('starts_at', $date)
                ->count();
        }

        return view('client.appointment.time', compact('availableSlotsByDate', 'branch', 'veterinarian', 'userBookingsByDate'));
    }

    /**
     * Генерировать доступные временные слоты для даты (логика из бота)
     */
    private function generateAvailableTimeSlots($schedules, string $date): array
    {
        $allAvailableSlots = [];
        
        // Загружаем все визиты за этот день одним запросом
        $scheduleIds = $schedules->pluck('id')->all();
        $dayStart = $date . ' 00:00:00';
        $dayEnd = $date . ' 23:59:59';
        $visits = Visit::select('schedule_id', 'starts_at')
            ->whereIn('schedule_id', $scheduleIds)
            ->whereBetween('starts_at', [$dayStart, $dayEnd])
            ->get();
        
        $busy = [];
        foreach ($visits as $visit) {
            $busy[$visit->schedule_id . '|' . Carbon::parse($visit->starts_at)->format('Y-m-d H:i:s')] = true;
        }
        
        foreach ($schedules as $schedule) {
            // Генерируем временные слоты с 9:00 до 18:00 с интервалом 30 минут
            $startTime = Carbon::parse($schedule->shift_starts_at)->setTime(9, 0);
            $endTime = Carbon::parse($schedule->shift_starts_at)->setTime(18, 0);
            
            $currentTime = $startTime->copy();
            
            while ($currentTime < $endTime) {
                $key = $schedule->id . '|' . $currentTime->format('Y-m-d H:i:s');
                $isBusy = isset($busy[$key]);
                
                if (!$isBusy) {
                    $allAvailableSlots[] = [
                        'time' => $currentTime->format('H:i'),
                        'datetime' => $currentTime->format('Y-m-d H:i:s'),
                        'schedule_id' => $schedule->id
                    ];
                }
                
                $currentTime->addMinutes(30);
            }
        }
        
        // Убираем дублирующиеся временные слоты
        $uniqueSlots = [];
        foreach ($allAvailableSlots as $slot) {
            $timeKey = $slot['time'];
            if (!isset($uniqueSlots[$timeKey])) {
                $uniqueSlots[$timeKey] = $slot;
            }
        }
        
        // Сортируем по времени
        ksort($uniqueSlots);
        return array_values($uniqueSlots);
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
            ], [
                'branch_id.required' => 'Поле филиал обязательно для заполнения.',
                'branch_id.exists' => 'Выбранный филиал не существует.',
                'veterinarian_id.required' => 'Поле ветеринар обязательно для заполнения.',
                'veterinarian_id.exists' => 'Выбранный ветеринар не существует.',
                'schedule_id.required' => 'Поле расписание обязательно для заполнения.',
                'schedule_id.exists' => 'Выбранное расписание не существует.',
                'time.required' => 'Поле время обязательно для заполнения.',
                'time.date_format' => 'Время должно быть в формате ЧЧ:ММ (например, 09:30).'
            ]);

            $branch = Branch::find($request->branch_id);
            $veterinarian = Employee::find($request->veterinarian_id);
            $schedule = Schedule::find($request->schedule_id);
            
            // Проверяем, что все модели найдены
            if (!$branch) {
                return back()->withErrors(['branch_id' => 'Выбранный филиал не существует.']);
            }
            if (!$veterinarian) {
                return back()->withErrors(['veterinarian_id' => 'Выбранный ветеринар не существует.']);
            }
            if (!$schedule) {
                return back()->withErrors(['schedule_id' => 'Выбранное расписание не существует.']);
            }

            // Получаем питомцев пользователя
            $pets = Pet::where('client_id', Auth::id())->get();

            // Формируем дату и время
            $date = Carbon::parse($schedule->shift_starts_at)->format('Y-m-d');
            $datetime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->time);


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

            // Проверяем конфликты времени перед созданием записи
            $schedule = Schedule::findOrFail($request->schedule_id);
            $conflicts = $this->visitManagementService->checkTimeConflicts(
                $request->schedule_id,
                $cleanTime,
                30 // Стандартная длительность 30 минут
            );

            if (!empty($conflicts)) {
                return back()->withErrors(['time' => 'Выбранное время занято. Пожалуйста, выберите другое время.']);
            }

            // Ограничение: не более 4 записей в день (как в боте)
            $date = Carbon::parse($schedule->shift_starts_at)->toDateString();
            $existingCount = Visit::where('client_id', Auth::id())
                ->whereDate('starts_at', $date)
                ->count();
            
            if ($existingCount >= 4) {
                return back()->withErrors(['error' => 'Вы уже забронировали максимум 4 интервала на этот день.']);
            }

            // Создаем запись (starts_at будет обработан в VisitDateTimeProcessingService)
            $visitData = [
                'client_id' => Auth::id(),
                'pet_id' => ($request->pet_id && $request->pet_id !== '') ? $request->pet_id : null,
                'schedule_id' => $request->schedule_id,
                'complaints' => $request->complaints,
                'status_id' => 1 // Статус "Запланирован"
            ];


            try {
                $visit = $this->visitManagementService->createVisit($visitData, $request);
            } catch (\Exception $e) {
                // Если ошибка связана с дублированием времени, показываем понятное сообщение
                if (strpos($e->getMessage(), 'Запись на это время уже существует') !== false) {
                    return back()->withErrors(['time' => $e->getMessage()]);
                }
                throw $e;
            }


            // Отправляем уведомление администраторам о новой записи через сайт
            $this->notificationService->notifyAboutWebsiteBooking($visit);

            return redirect()->route('client.appointment.appointments')
                ->with('success', 'Запись на прием успешно создана!');

        } catch (\Exception $e) {
            \Log::error('AppointmentController::store - Ошибка при создании записи', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            // Определяем, показывать ли техническую ошибку или общее сообщение
            $userMessage = $this->getUserFriendlyErrorMessage($e);
            
            return back()->withErrors(['error' => $userMessage]);
        }
    }

    /**
     * Получить понятное пользователю сообщение об ошибке
     */
    private function getUserFriendlyErrorMessage(\Exception $e): string
    {
        $message = $e->getMessage();
        
        // Список известных понятных ошибок (из валидации и бизнес-логики)
        $knownUserErrors = [
            'Запись на это время уже существует',
            'Вы уже забронировали максимум 4 интервала',
            'Выбранное время занято',
            'Поле филиал обязательно для заполнения',
            'Поле ветеринар обязательно для заполнения',
            'Поле расписание обязательно для заполнения',
            'Поле время обязательно для заполнения',
            'Время должно быть в формате ЧЧ:ММ',
            'Время должно быть в формате ЧЧ:ММ (например, 09:30)',
            'Выбранный филиал не существует',
            'Выбранный ветеринар не существует',
            'Выбранное расписание не существует',
            'Выбранный питомец не существует',
            'Жалобы не должны превышать 1000 символов',
            'Необходимо войти в систему для записи на прием',
            'Запись можно отменить не менее чем за 2 часа до приема',
            'Запись успешно отменена',
            'Произошла ошибка при отмене записи'
        ];
        
        // Проверяем, является ли ошибка понятной пользователю
        foreach ($knownUserErrors as $knownError) {
            if (strpos($message, $knownError) !== false) {
                return $message; // Возвращаем оригинальное сообщение
            }
        }
        
        // Если это техническая ошибка, возвращаем общее сообщение
        return 'Произошла системная ошибка. Пожалуйста, попробуйте позже или обратитесь в поддержку.';
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
