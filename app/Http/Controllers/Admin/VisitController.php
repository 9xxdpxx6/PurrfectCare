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
use App\Services\Visit\VisitManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\Admin\Visit\StoreRequest;
use App\Http\Requests\Admin\Visit\UpdateRequest;
use Illuminate\Support\Facades\DB;

class VisitController extends AdminController
{
    use HasOptionsMethods;
    
    protected $visitService;
    
    public function __construct(VisitManagementService $visitService)
    {
        $this->visitService = $visitService;
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
                    'name' => $diagnosis->dictionaryDiagnosis->name,
                    'treatment_plan' => $diagnosis->treatment_plan,
                    'pivot_id' => $diagnosis->id
                ];
            } else {
                return [
                    'id' => $diagnosis->custom_diagnosis,
                    'name' => $diagnosis->custom_diagnosis,
                    'treatment_plan' => $diagnosis->treatment_plan,
                    'pivot_id' => $diagnosis->id
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
            $formattedData = $this->visitService->getFormattedDisplayData($visit);
            $visit->symptoms_display = $formattedData['symptoms']['display'];
            $visit->diagnoses_display = $formattedData['diagnoses']['display'];
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
        $validated = $request->validated();
        $this->visitService->createVisit($validated, $request);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись на прием успешно создана');
    }

    public function update(UpdateRequest $request, $id) : RedirectResponse
    {
        $validated = $request->validated();
        $this->visitService->updateVisit($id, $validated, $request);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись на прием успешно обновлена');
    }

    public function destroy($id) : RedirectResponse
    {
        $this->visitService->deleteVisit($id);
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
        
        try {
            $availableTime = $this->visitService->getAvailableTime($scheduleId);
            return response()->json($availableTime);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }


} 