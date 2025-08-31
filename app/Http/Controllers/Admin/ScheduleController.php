<?php

namespace App\Http\Controllers\Admin;

use App\Models\Schedule;
use App\Models\Employee;
use App\Models\Branch;
use App\Http\Filters\ScheduleFilter;
use App\Http\Traits\HasOptionsMethods;
use App\Services\Schedule\ScheduleValidationService;
use App\Services\Schedule\ScheduleCreationService;
use App\Services\Schedule\DateTimeProcessingService;
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
    use HasOptionsMethods;

    protected $validationService;
    protected $creationService;
    protected $dateTimeService;

    public function __construct(
        ScheduleValidationService $validationService,
        ScheduleCreationService $creationService,
        DateTimeProcessingService $dateTimeService
    ) {
        parent::__construct();
        $this->validationService = $validationService;
        $this->creationService = $creationService;
        $this->dateTimeService = $dateTimeService;
        
        $this->model = Schedule::class;
        $this->viewPath = 'schedules';
        $this->routePrefix = 'schedules';
        $this->permissionPrefix = 'schedules';
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
        
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $query = $this->model::select([
                'schedules.id', 'schedules.veterinarian_id', 'schedules.branch_id', 
                'schedules.shift_starts_at', 'schedules.shift_ends_at',
                'schedules.created_at', 'schedules.updated_at'
            ])
            ->with([
                'veterinarian:id,name,email',
                'branch:id,name,address'
            ])
            ->filter($filter);
            
        $items = $query->paginate(25)->withQueryString();
        
        // Оптимизация: используем select для выбора только нужных полей
        $veterinarians = Employee::select(['id', 'name', 'email'])
            ->whereHas('specialties', function($query) {
                $query->where('is_veterinarian', true);
            })
            ->orderBy('name')
            ->get();
            
        $branches = Branch::select(['id', 'name', 'address'])->orderBy('name')->get();
        
        // Оптимизация: используем select для выбора только нужных полей
        $specialties = \App\Models\Specialty::select(['id', 'name', 'is_veterinarian'])->orderBy('name')->get();
        
        return view("admin.{$this->viewPath}.index", compact('items', 'veterinarians', 'branches', 'specialties'));
    }

    public function create() : View
    {
        // Оптимизация: используем select для выбора только нужных полей
        $veterinarians = Employee::select(['id', 'name', 'email'])
            ->whereHas('specialties', function($query) {
                $query->where('is_veterinarian', true);
            })
            ->orderBy('name')
            ->get();
            
        $branches = Branch::select(['id', 'name', 'address'])->orderBy('name')->get();
        
        // Получаем предвыбранного ветеринара из параметра запроса
        $selectedVeterinarian = request()->get('veterinarian_id');
        $selectedBranch = null;
        
        // Если выбран ветеринар, получаем его первый филиал
        if ($selectedVeterinarian) {
            // Оптимизация: используем select для выбора только нужных полей
            $employee = Employee::select(['id'])
                ->with(['branches:id,name'])
                ->find($selectedVeterinarian);
            if ($employee && $employee->branches->count() > 0) {
                $selectedBranch = $employee->branches->first()->id;
            }
        }
        
        return view("admin.{$this->viewPath}.create", compact('veterinarians', 'branches', 'selectedVeterinarian', 'selectedBranch'));
    }

    public function store(StoreRequest $request) : RedirectResponse
    {
        // Обработка полей даты и времени
        $this->dateTimeService->processScheduleDateTimeFields($request);
        $validated = $request->validated();

        // Проверяем логические противоречия
        $errors = $this->validationService->validateScheduleConflicts(
            $validated['veterinarian_id'],
            $validated['shift_starts_at'],
            $validated['shift_ends_at']
        );

        if (!empty($errors)) {
            return back()
                ->withInput()
                ->withErrors(['schedule_conflicts' => $errors]);
        }

        $this->creationService->createSingleSchedule($validated);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Расписание успешно создано');
    }

    public function show($id) : View
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $item = $this->model::select([
                'schedules.id', 'schedules.veterinarian_id', 'schedules.branch_id', 
                'schedules.shift_starts_at', 'schedules.shift_ends_at',
                'schedules.created_at', 'schedules.updated_at'
            ])
            ->with([
                'veterinarian:id,name,email,phone',
                'branch:id,name,address'
            ])
            ->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function edit($id) : View
    {
        // Оптимизация: используем select для выбора нужных полей
        $item = $this->model::select([
                'schedules.id', 'schedules.veterinarian_id', 'schedules.branch_id', 
                'schedules.shift_starts_at', 'schedules.shift_ends_at',
                'schedules.created_at', 'schedules.updated_at'
            ])
            ->findOrFail($id);
            
        // Оптимизация: используем select для выбора только нужных полей
        $veterinarians = Employee::select(['id', 'name', 'email'])
            ->whereHas('specialties', function($query) {
                $query->where('is_veterinarian', true);
            })
            ->orderBy('name')
            ->get();
            
        $branches = Branch::select(['id', 'name', 'address'])->orderBy('name')->get();
        return view("admin.{$this->viewPath}.edit", compact('item', 'veterinarians', 'branches'));
    }

    public function update(Request $request, $id) : RedirectResponse
    {
        // Обработка полей даты и времени
        $this->dateTimeService->processScheduleDateTimeFields($request);
        
        $validated = $request->validate($this->validationRules);

        // Проверяем логические противоречия
        $errors = $this->validationService->validateScheduleConflicts(
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

        $this->creationService->updateSchedule($id, $validated);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Расписание успешно обновлено');
    }

    public function destroy($id) : RedirectResponse
    {
        $this->creationService->deleteSchedule($id);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Расписание успешно удалено');
    }

    /**
     * Показать форму для создания расписания на неделю
     */
    public function createWeek() : View
    {
        // Оптимизация: используем select для выбора только нужных полей
        $veterinarians = Employee::select(['id', 'name', 'email'])
            ->whereHas('specialties', function($query) {
                $query->where('is_veterinarian', true);
            })
            ->orderBy('name')
            ->get();
            
        $branches = Branch::select(['id', 'name', 'address'])->orderBy('name')->get();
        
        // Получаем предвыбранного ветеринара из параметра запроса
        $selectedVeterinarian = request()->get('veterinarian_id');
        $selectedBranch = null;
        
        // Если выбран ветеринар, получаем его первый филиал
        if ($selectedVeterinarian) {
            // Оптимизация: используем select для выбора только нужных полей
            $employee = Employee::select(['id'])
                ->with(['branches:id,name'])
                ->find($selectedVeterinarian);
            if ($employee && $employee->branches->count() > 0) {
                $selectedBranch = $employee->branches->first()->id;
            }
        }
        
        return view("admin.{$this->viewPath}.create-week", compact('veterinarians', 'branches', 'selectedVeterinarian', 'selectedBranch'));
    }

    /**
     * Сохранить расписание на неделю
     */
    public function storeWeek(StoreWeekRequest $request) : RedirectResponse
    {
        $validated = $request->validated();
        
        // Обработка поля начала недели
        $this->dateTimeService->processWeekStartField($request);

        // Создаем расписание на неделю через сервис
        $result = $this->creationService->createWeekSchedule($validated, $request->all());

        if (!$result['success']) {
            switch ($result['type']) {
                case 'conflicts':
                    $conflicts = $result['data'];
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

                case 'existing_schedules':
                    $existingSchedules = $result['data'];
                    $existingMessage = "Для всех выбранных дней уже существует расписание: ";
                    $existingList = [];
                    foreach ($existingSchedules as $day => $data) {
                        $existingList[] = "{$data['day_name']} ({$data['date']})";
                    }
                    $existingMessage .= implode(', ', $existingList);

                    return back()
                        ->withInput()
                        ->with('warning', $existingMessage);

                case 'error':
                    return back()
                        ->withInput()
                        ->withErrors(['general' => $result['message']]);
            }
        }

        // Формируем сообщение об успехе
        $createdCount = count($result['created_schedules']);
        $totalDays = $result['total_days'];
        
        $successMessage = "Успешно создано расписаний: {$createdCount}";
        
        if ($createdCount < $totalDays) {
            $skippedCount = $totalDays - $createdCount;
            $successMessage .= ". Пропущено дней: {$skippedCount} (уже существует расписание)";
        }

        if (!empty($result['existing_schedules'])) {
            $existingList = [];
            foreach ($result['existing_schedules'] as $day => $data) {
                $existingList[] = "{$data['day_name']} ({$data['date']})";
            }
            $successMessage .= ". Существующие расписания: " . implode(', ', $existingList);
        }

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', $successMessage);
    }
} 