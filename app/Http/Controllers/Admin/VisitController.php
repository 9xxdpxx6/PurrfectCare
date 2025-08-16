<?php

namespace App\Http\Controllers\Admin;

use App\Http\Traits\HasOptionsMethods;
use App\Models\Visit;
use App\Models\User;
use App\Models\Pet;
use App\Models\Schedule;
use App\Models\Status;
use App\Models\Service;
use App\Models\Symptom;
use App\Models\Diagnosis;
use App\Models\DictionarySymptom;
use App\Models\DictionaryDiagnosis;
use App\Http\Filters\VisitFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\Admin\Visit\StoreRequest;
use App\Http\Requests\Admin\Visit\UpdateRequest;
use Illuminate\Support\Facades\DB;

class VisitController extends AdminController
{
    use HasOptionsMethods;

    public function __construct()
    {
        $this->model = Visit::class;
        $this->viewPath = 'visits';
        $this->routePrefix = 'visits';
    }

    public function create() : View
    {
        $clients = User::all();
        $pets = Pet::all();
        $schedules = Schedule::all();
        $statuses = Status::all();
        $symptoms = DictionarySymptom::all();
        $diagnoses = DictionaryDiagnosis::all();
        $default_status = Status::where('name', 'Новый')->first();
        $default_status_id = $default_status ? $default_status->id : null;
        
        // Получаем ID клиента, питомца и расписания из параметров запроса
        $selectedClientId = request('client');
        $selectedPetId = request('pet');
        $selectedScheduleId = request('schedule_id');
        
        // Если передан pet_id, но не передан client_id, получаем владельца питомца
        if ($selectedPetId && !$selectedClientId) {
            $pet = Pet::with('client')->find($selectedPetId);
            if ($pet && $pet->client) {
                $selectedClientId = $pet->client->id;
            }
        }
        
        return view("admin.{$this->viewPath}.create", compact(
            'clients', 'pets', 'schedules', 'statuses',
            'symptoms', 'diagnoses', 'default_status_id', 'selectedClientId', 'selectedPetId', 'selectedScheduleId'
        ));
    }

    public function edit($id) : View
    {
        $item = $this->model::with([
            'diagnoses.dictionaryDiagnosis', 'symptoms.dictionarySymptom'
        ])->findOrFail($id);
        $clients = User::all();
        $pets = Pet::all();
        $schedules = Schedule::all();
        $statuses = Status::all();
        $symptoms = DictionarySymptom::all();
        $diagnoses = DictionaryDiagnosis::all();
        
        // Подготавливаем выбранные симптомы
        $selectedSymptoms = $item->symptoms->map(function($symptom) {
            if ($symptom->dictionary_symptom_id) {
                return [
                    'id' => $symptom->dictionary_symptom_id,
                    'name' => $symptom->dictionarySymptom->name
                ];
            } else {
                return [
                    'id' => $symptom->custom_symptom,
                    'name' => $symptom->custom_symptom
                ];
            }
        });
        
        // Подготавливаем выбранные диагнозы
        $selectedDiagnoses = $item->diagnoses->map(function($diagnosis) {
            if ($diagnosis->dictionary_diagnosis_id) {
                return [
                    'id' => $diagnosis->dictionary_diagnosis_id,
                    'name' => $diagnosis->dictionaryDiagnosis->name
                ];
            } else {
                return [
                    'id' => $diagnosis->custom_diagnosis,
                    'name' => $diagnosis->custom_diagnosis
                ];
            }
        });
        
        return view("admin.{$this->viewPath}.edit", compact(
            'item', 'clients', 'pets', 'schedules', 'statuses',
            'symptoms', 'diagnoses', 'selectedSymptoms', 'selectedDiagnoses'
        ));
    }

    public function index(Request $request) : View
    {
        // Преобразуем даты из формата d.m.Y в Y-m-d для фильтров
        $queryParams = $request->query();
        if (isset($queryParams['date_from']) && $queryParams['date_from']) {
            try {
                $queryParams['date_from'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['date_from'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        if (isset($queryParams['date_to']) && $queryParams['date_to']) {
            try {
                $queryParams['date_to'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['date_to'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        
        $filter = app(VisitFilter::class, ['queryParams' => $queryParams]);
        $query = $this->model::with([
            'client', 'pet', 'schedule', 'status',
            'symptoms.dictionarySymptom', 'diagnoses.dictionaryDiagnosis',
            'orders'
        ])->filter($filter);
        $items = $query->paginate(25)->withQueryString();
        
        // Подготавливаем данные для каждого приёма
        foreach ($items as $visit) {
            // Ограничиваем симптомы для отображения
            $limitedSymptoms = $visit->symptoms->take(3);
            $symptomsCount = $visit->symptoms->count();
            
            $symptomNames = $limitedSymptoms->map(function($symptom) {
                return $symptom->getName();
            })->toArray();
            
            if ($symptomsCount > 3) {
                $symptomNames[] = '...';
            }
            
            $visit->symptoms_display = $symptomNames;
            
            // Ограничиваем диагнозы для отображения
            $limitedDiagnoses = $visit->diagnoses->take(3);
            $diagnosesCount = $visit->diagnoses->count();
            
            $diagnosisNames = $limitedDiagnoses->map(function($diagnosis) {
                return $diagnosis->getName();
            })->toArray();
            
            if ($diagnosesCount > 3) {
                $diagnosisNames[] = '...';
            }
            
            $visit->diagnoses_display = $diagnosisNames;
        }
        
        $statuses = Status::orderBy('name')->get();
        
        return view("admin.{$this->viewPath}.index", compact('items', 'statuses'));
    }

    public function show($id) : View
    {
        $item = $this->model::with([
            'client', 'pet', 'schedule.veterinarian', 'status',
            'symptoms.dictionarySymptom', 'diagnoses.dictionaryDiagnosis',
            'orders.status'
        ])->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function store(StoreRequest $request) : RedirectResponse
    {
        // Обработка полей даты и времени
        $this->processDateTimeFields($request);
        $validated = $request->validated();
        $visit = $this->model::create($validated);
        
        // Обрабатываем симптомы
        if ($request->has('symptoms') && is_array($request->symptoms)) {
            foreach ($request->symptoms as $symptomData) {
                if (!empty(trim($symptomData))) {
                    if (is_numeric($symptomData)) {
                        // Проверяем существование симптома в словаре
                        $dictionarySymptom = \App\Models\DictionarySymptom::find($symptomData);
                        if ($dictionarySymptom) {
                            Symptom::create([
                                'visit_id' => $visit->id,
                                'dictionary_symptom_id' => $symptomData,
                                'custom_symptom' => null,
                                'notes' => null
                            ]);
                        }
                    } else {
                        Symptom::create([
                            'visit_id' => $visit->id,
                            'dictionary_symptom_id' => null,
                            'custom_symptom' => $symptomData,
                            'notes' => null
                        ]);
                    }
                }
            }
        }

        // Обрабатываем диагнозы
        if ($request->has('diagnoses') && is_array($request->diagnoses)) {
            foreach ($request->diagnoses as $diagnosisData) {
                if (!empty(trim($diagnosisData))) {
                    if (is_numeric($diagnosisData)) {
                        // Проверяем существование диагноза в словаре
                        $dictionaryDiagnosis = \App\Models\DictionaryDiagnosis::find($diagnosisData);
                        if ($dictionaryDiagnosis) {
                            Diagnosis::create([
                                'visit_id' => $visit->id,
                                'dictionary_diagnosis_id' => $diagnosisData,
                                'custom_diagnosis' => null,
                                'treatment_plan' => null
                            ]);
                        }
                    } else {
                        Diagnosis::create([
                            'visit_id' => $visit->id,
                            'dictionary_diagnosis_id' => null,
                            'custom_diagnosis' => $diagnosisData,
                            'treatment_plan' => null
                        ]);
                    }
                }
            }
        }
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись на прием успешно создана');
    }

    public function update(UpdateRequest $request, $id) : RedirectResponse
    {
        // Обработка полей даты и времени
        $this->processDateTimeFields($request);
        $validated = $request->validated();
        $visit = $this->model::findOrFail($id);
        $visit->update($validated);
        
        // Удаляем все старые симптомы этого приема
        $visit->symptoms()->delete();
        
        // Создаём новые симптомы
        if ($request->has('symptoms')) {
            foreach ($request->symptoms as $symptomData) {
                if (is_numeric($symptomData)) {
                    // Создаем симптом из словаря
                    Symptom::create([
                        'visit_id' => $visit->id,
                        'dictionary_symptom_id' => $symptomData,
                        'custom_symptom' => null,
                        'notes' => null
                    ]);
                } else {
                    // Создаем кастомный симптом
                    Symptom::create([
                        'visit_id' => $visit->id,
                        'dictionary_symptom_id' => null,
                        'custom_symptom' => $symptomData,
                        'notes' => null
                    ]);
                }
            }
        }
        
        // Удаляем все старые диагнозы этого приема
        $visit->diagnoses()->delete();
        
        // Создаём новые диагнозы
        if ($request->has('diagnoses')) {
            foreach ($request->diagnoses as $diagnosisData) {
                if (is_numeric($diagnosisData)) {
                    // Создаем диагноз из словаря
                    Diagnosis::create([
                        'visit_id' => $visit->id,
                        'dictionary_diagnosis_id' => $diagnosisData,
                        'custom_diagnosis' => null,
                        'treatment_plan' => null
                    ]);
                } else {
                    // Создаем кастомный диагноз
                    Diagnosis::create([
                        'visit_id' => $visit->id,
                        'dictionary_diagnosis_id' => null,
                        'custom_diagnosis' => $diagnosisData,
                        'treatment_plan' => null
                    ]);
                }
            }
        }
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись на прием успешно обновлена');
    }

    public function destroy($id) : RedirectResponse
    {
        $visit = $this->model::findOrFail($id);
        
        // Убираем проверку зависимостей - диагнозы и симптомы удаляются каскадно
        
        $visit->delete();
        return redirect()->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись на приём успешно удалена');
    }

    /**
     * Получить доступное время для выбранного расписания
     */
    public function getAvailableTime(Request $request)
    {
        $scheduleId = $request->input('schedule_id');
        
        if (!$scheduleId) {
            return response()->json(['error' => 'Не выбрано расписание']);
        }
        
        $schedule = Schedule::with('veterinarian')->find($scheduleId);
        if (!$schedule) {
            return response()->json(['error' => 'Расписание не найдено']);
        }
        
        // Получаем все занятые времена для этого расписания
        $occupiedTimes = Visit::where('schedule_id', $scheduleId)
            ->pluck('starts_at')
            ->map(function($time) {
                return \Carbon\Carbon::parse($time)->format('H:i');
            })
            ->toArray();
        
        // Генерируем доступные времена (кратно получасу: 00 и 30 минут)
        $availableTimes = [];
        $startTime = \Carbon\Carbon::parse($schedule->shift_starts_at);
        $endTime = \Carbon\Carbon::parse($schedule->shift_ends_at);
        
        // Начинаем с ближайшего получаса после начала смены
        $currentTime = $startTime->copy();
        
        // Если время не кратно получасу, округляем вверх до следующего получаса
        $minutes = $currentTime->minute;
        if ($minutes > 0 && $minutes < 30) {
            $currentTime->setMinute(30);
            $currentTime->setSecond(0);
        } elseif ($minutes > 30) {
            $currentTime->addHour();
            $currentTime->setMinute(0);
            $currentTime->setSecond(0);
        } else {
            // Если уже кратно получасу (0 или 30), оставляем как есть
            $currentTime->setSecond(0);
        }
        
        // Генерируем времена с интервалом 30 минут
        while ($currentTime < $endTime) {
            $timeString = $currentTime->format('H:i');
            if (!in_array($timeString, $occupiedTimes)) {
                $availableTimes[] = [
                    'time' => $timeString,
                    'formatted' => $currentTime->format('d.m.Y H:i')
                ];
            }
            $currentTime->addMinutes(30);
        }
        
        $nextAvailableTime = null;
        if (!empty($availableTimes)) {
            $nextAvailableTime = $availableTimes[0]['formatted'];
        }

        return response()->json([
            'schedule' => [
                'veterinarian' => $schedule->veterinarian ? $schedule->veterinarian->name : 'Не указан',
                'shift_start' => $startTime->format('d.m.Y H:i'),
                'shift_end' => $endTime->format('d.m.Y H:i'),
                'shift_starts_at' => $schedule->shift_starts_at
            ],
            'available_times' => $availableTimes,
            'occupied_times' => $occupiedTimes,
            'next_available_time' => $nextAvailableTime
        ]);
    }

    /**
     * Обработка полей даты и времени для создания datetime полей
     */
    private function processDateTimeFields(Request $request)
    {
        if ($request->has('schedule_id') && $request->has('visit_time')) {
            try {
                $schedule = Schedule::find($request->schedule_id);
                if ($schedule) {
                    $scheduleDate = \Carbon\Carbon::parse($schedule->shift_starts_at);
                    $visitTime = $request->visit_time;
                    
                    // Парсим время и округляем до начала получасового интервала
                    $timeParts = explode(':', $visitTime);
                    if (count($timeParts) === 2) {
                        $hour = (int)$timeParts[0];
                        $minute = (int)$timeParts[1];
                        
                        // Округляем до начала получасового интервала
                        if ($minute >= 30) {
                            $roundedMinute = 30;
                        } else {
                            $roundedMinute = 0;
                        }
                        
                        // Форматируем округленное время
                        $roundedTime = sprintf('%02d:%02d', $hour, $roundedMinute);
                        
                        // Объединяем дату из расписания с округленным временем приёма
                        $fullDateTime = $scheduleDate->format('Y-m-d') . ' ' . $roundedTime;
                        
                        // Отладочная информация
                        \Log::info('Processing datetime fields', [
                            'original_visit_time' => $visitTime,
                            'rounded_time' => $roundedTime,
                            'schedule_date' => $scheduleDate->format('Y-m-d'),
                            'full_datetime' => $fullDateTime,
                            'schedule_shift_start' => $schedule->shift_starts_at,
                            'schedule_shift_end' => $schedule->shift_ends_at
                        ]);
                        
                        $request->merge([
                            'starts_at' => $fullDateTime
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Игнорируем ошибки парсинга, валидация их поймает
                \Log::error('Error processing datetime fields', [
                    'error' => $e->getMessage(),
                    'visit_time' => $request->visit_time ?? 'not set',
                    'schedule_id' => $request->schedule_id ?? 'not set'
                ]);
            }
        }
    }
} 