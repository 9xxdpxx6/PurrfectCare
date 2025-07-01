<?php

namespace App\Http\Controllers\Admin;

use App\Models\Schedule;
use App\Models\Employee;
use App\Models\Branch;
use App\Http\Filters\ScheduleFilter;
use App\Http\Traits\HasSelectOptions;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Schedule\StoreWeekRequest;
use App\Http\Requests\Admin\Schedule\StoreRequest;
use App\Http\Requests\Admin\Schedule\UpdateRequest;

class ScheduleController extends AdminController
{
    use HasSelectOptions;

    public function __construct()
    {
        $this->model = Schedule::class;
        $this->viewPath = 'schedules';
        $this->routePrefix = 'schedules';
        $this->validationRules = [
            'veterinarian_id' => 'required|exists:employees,id',
            'branch_id' => 'required|exists:branches,id',
            'shift_starts_at' => 'required|date',
            'shift_ends_at' => 'required|date|after:shift_starts_at',
        ];
    }

    public function index(Request $request) : View
    {
        $filter = app(ScheduleFilter::class, ['queryParams' => $request->query()]);
        $query = $this->model::with(['veterinarian', 'branch'])->filter($filter);
        $items = $query->paginate(30)->withQueryString();
        
        $veterinarians = Employee::whereHas('specialties', function($query) {
            $query->where('is_veterinarian', true);
        })->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $specialties = \App\Models\Specialty::orderBy('name')->get();
        
        return view("admin.{$this->viewPath}.index", compact('items', 'veterinarians', 'branches', 'specialties'));
    }

    public function create() : View
    {
        $veterinarians = Employee::whereHas('specialties', function($query) {
            $query->where('is_veterinarian', true);
        })->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        return view("admin.{$this->viewPath}.create", compact('veterinarians', 'branches'));
    }

    /**
     * Проверка логических противоречий в расписании
     * 
     * @param int $veterinarianId ID ветеринара
     * @param string $shiftStartsAt Время начала смены
     * @param string $shiftEndsAt Время окончания смены
     * @param int|null $excludeScheduleId ID расписания для исключения (при обновлении)
     * @return array Массив с ошибками или пустой массив, если ошибок нет
     */
    private function validateScheduleConflicts(
        int $veterinarianId,
        string $shiftStartsAt,
        string $shiftEndsAt,
        ?int $excludeScheduleId = null
    ): array {
        // Проверяем, нет ли у ветеринара других смен в это же время
        $query = Schedule::where('veterinarian_id', $veterinarianId)
                ->where('shift_ends_at', '>', $shiftStartsAt)
                ->where('shift_starts_at', '<', $shiftEndsAt);

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        $conflictingSchedules = $query->with('branch')->get();

        if ($conflictingSchedules->isEmpty()) {
            return [];
        }

        return $conflictingSchedules->map(function($schedule) {
            return sprintf(
                'У ветеринара уже есть смена %s - %s в филиале "%s"',
                Carbon::parse($schedule->shift_starts_at)->format('d.m.Y H:i'),
                Carbon::parse($schedule->shift_ends_at)->format('H:i'),
                $schedule->branch->name
            );
        })->toArray();
    }

    public function store(StoreRequest $request) : RedirectResponse
    {
        // Обработка полей даты и времени
        $this->processDateTimeFields($request);
        $validated = $request->validated();

        // Проверяем логические противоречия
        $errors = $this->validateScheduleConflicts(
            $validated['veterinarian_id'],
            $validated['shift_starts_at'],
            $validated['shift_ends_at']
        );

        if (!empty($errors)) {
            return back()
                ->withInput()
                ->withErrors(['schedule_conflicts' => $errors]);
        }

        $this->model::create($validated);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Расписание успешно создано');
    }

    public function show($id) : View
    {
        $item = $this->model::with(['veterinarian', 'branch'])->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function edit($id) : View
    {
        $item = $this->model::findOrFail($id);
        $veterinarians = Employee::whereHas('specialties', function($query) {
            $query->where('is_veterinarian', true);
        })->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        return view("admin.{$this->viewPath}.edit", compact('item', 'veterinarians', 'branches'));
    }

    public function update(Request $request, $id) : RedirectResponse
    {
        // Обработка полей даты и времени
        $this->processDateTimeFields($request);
        
        $item = $this->model::findOrFail($id);
        $validated = $request->validate($this->validationRules);

        // Проверяем логические противоречия
        $errors = $this->validateScheduleConflicts(
            $validated['veterinarian_id'],
            $validated['shift_starts_at'],
            $validated['shift_ends_at'],
            $id
        );

        if (!empty($errors)) {
            return back()
                ->withInput()
                ->withErrors(['schedule_conflicts' => $errors]);
        }

        $item->update($validated);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Расписание успешно обновлено');
    }

    public function destroy($id) : RedirectResponse
    {
        $item = $this->model::findOrFail($id);
        $item->delete();

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Расписание успешно удалено');
    }

    /**
     * Показать форму для создания расписания на неделю
     */
    public function createWeek() : View
    {
        $veterinarians = Employee::whereHas('specialties', function($query) {
            $query->where('is_veterinarian', true);
        })->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        return view("admin.{$this->viewPath}.create-week", compact('veterinarians', 'branches'));
    }

    /**
     * Сохранить расписание на неделю
     */
    public function storeWeek(StoreWeekRequest $request) : RedirectResponse
    {
        $validated = $request->validated();

        // Обработка полей даты и времени
        // $this->processDateTimeFields($request);

        $weekStart = Carbon::parse($request->week_start)->startOfWeek();
        $veterinarianId = $validated['veterinarian_id'];
        $branchId = $validated['branch_id'];

        $dayMap = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];

        $dayNames = [
            'monday' => 'Понедельник',
            'tuesday' => 'Вторник',
            'wednesday' => 'Среда',
            'thursday' => 'Четверг',
            'friday' => 'Пятница',
            'saturday' => 'Суббота',
            'sunday' => 'Воскресенье'
        ];

        $schedulesToCreate = [];
        $conflicts = [];
        $existingSchedules = [];

        // Проверяем каждый день на конфликты и существующие расписания
        foreach ($request->days as $day) {
            $dayOffset = $dayMap[$day];
            $shiftDate = $weekStart->copy()->addDays($dayOffset);
            
            $startTime = $request->input("start_time_{$day}");
            $endTime = $request->input("end_time_{$day}");
            
            $shiftStartsAt = $shiftDate->copy()->format('Y-m-d') . ' ' . $startTime;
            $shiftEndsAt = $shiftDate->copy()->format('Y-m-d') . ' ' . $endTime;

            // Проверяем существующие расписания на этот день
            $existingSchedule = Schedule::where('veterinarian_id', $veterinarianId)
                ->whereDate('shift_starts_at', $shiftDate->format('Y-m-d'))
                ->first();

            if ($existingSchedule) {
                $existingSchedules[$day] = [
                    'schedule' => $existingSchedule,
                    'day_name' => $dayNames[$day],
                    'date' => $shiftDate->format('d.m.Y')
                ];
                continue;
            }

            // Проверяем конфликты с другими расписаниями
            $conflictErrors = $this->validateScheduleConflicts(
                $veterinarianId,
                $shiftStartsAt,
                $shiftEndsAt
            );

            if (!empty($conflictErrors)) {
                $conflicts[$day] = [
                    'errors' => $conflictErrors,
                    'day_name' => $dayNames[$day],
                    'date' => $shiftDate->format('d.m.Y'),
                    'time' => "{$startTime} - {$endTime}"
                ];
                continue;
            }

            // Добавляем в список для создания
            $schedulesToCreate[] = [
                'veterinarian_id' => $veterinarianId,
                'branch_id' => $branchId,
                'shift_starts_at' => $shiftStartsAt,
                'shift_ends_at' => $shiftEndsAt,
                'day' => $day,
                'day_name' => $dayNames[$day],
                'date' => $shiftDate->format('d.m.Y')
            ];
        }

        // Если есть конфликты, возвращаем ошибки
        if (!empty($conflicts)) {
            $errorMessages = [];
            foreach ($conflicts as $day => $conflict) {
                foreach ($conflict['errors'] as $error) {
                    $errorMessages[] = "{$conflict['day_name']} ({$conflict['date']}): {$error}";
                }
            }

            return back()
                ->withInput()
                ->withErrors(['schedule_conflicts' => $errorMessages])
                ->with('conflicts', $conflicts);
        }

        // Если нет расписаний для создания, возвращаем предупреждение
        if (empty($schedulesToCreate)) {
            $existingMessage = "Для всех выбранных дней уже существует расписание: ";
            $existingList = [];
            foreach ($existingSchedules as $day => $data) {
                $existingList[] = "{$data['day_name']} ({$data['date']})";
            }
            $existingMessage .= implode(', ', $existingList);

            return back()
                ->withInput()
                ->with('warning', $existingMessage);
        }

        // Создаём расписания в транзакции
        try {
            \DB::beginTransaction();

            $createdSchedules = [];
            foreach ($schedulesToCreate as $scheduleData) {
                $schedule = Schedule::create([
                    'veterinarian_id' => $scheduleData['veterinarian_id'],
                    'branch_id' => $scheduleData['branch_id'],
                    'shift_starts_at' => $scheduleData['shift_starts_at'],
                    'shift_ends_at' => $scheduleData['shift_ends_at'],
                ]);
                
                $createdSchedules[] = $schedule;
            }

            \DB::commit();

            // Формируем сообщение об успехе
            $createdCount = count($createdSchedules);
            $totalDays = count($request->days);
            
            $successMessage = "Успешно создано расписаний: {$createdCount}";
            
            if ($createdCount < $totalDays) {
                $skippedCount = $totalDays - $createdCount;
                $successMessage .= ". Пропущено дней: {$skippedCount} (уже существует расписание)";
            }

            if (!empty($existingSchedules)) {
                $existingList = [];
                foreach ($existingSchedules as $day => $data) {
                    $existingList[] = "{$data['day_name']} ({$data['date']})";
                }
                $successMessage .= ". Существующие расписания: " . implode(', ', $existingList);
            }

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('Ошибка при создании расписания на неделю', [
                'error' => $e->getMessage(),
                'veterinarian_id' => $veterinarianId,
                'branch_id' => $branchId,
                'week_start' => $request->week_start,
                'days' => $request->days
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Произошла ошибка при создании расписания. Попробуйте еще раз.']);
        }
    }

    /**
     * Обработка полей даты и времени для создания datetime полей
     */
    private function processDateTimeFields(Request $request)
    {
        if ($request->has('shift_date') && $request->has('start_time')) {
            try {
                $date = Carbon::createFromFormat('d.m.Y', $request->shift_date);
                $startTime = $request->start_time;
                $request->merge([
                    'shift_starts_at' => $date->format('Y-m-d') . ' ' . $startTime
                ]);
            } catch (\Exception $e) {
                // Игнорируем ошибки парсинга, валидация их поймает
            }
        }

        if ($request->has('shift_date') && $request->has('end_time')) {
            try {
                $date = Carbon::createFromFormat('d.m.Y', $request->shift_date);
                $endTime = $request->end_time;
                $request->merge([
                    'shift_ends_at' => $date->format('Y-m-d') . ' ' . $endTime
                ]);
            } catch (\Exception $e) {
                // Игнорируем ошибки парсинга, валидация их поймает
            }
        }

        if ($request->has('week_start')) {
            try {
                $date = Carbon::createFromFormat('d.m.Y', $request->week_start);
                $request->merge([
                    'week_start' => $date->format('Y-m-d')
                ]);
            } catch (\Exception $e) {
                // Игнорируем ошибки парсинга, валидация их поймает
            }
        }
    }
} 