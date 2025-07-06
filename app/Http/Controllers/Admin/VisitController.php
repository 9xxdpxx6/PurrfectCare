<?php

namespace App\Http\Controllers\Admin;

use App\Http\Traits\HasSelectOptions;
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
    use HasSelectOptions;

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
        $default_starts_at = now()->format('d.m.Y H:i');
        return view("admin.{$this->viewPath}.create", compact(
            'clients', 'pets', 'schedules', 'statuses',
            'symptoms', 'diagnoses', 'default_status_id', 'default_starts_at'
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
        $edit_starts_at = $item->starts_at ? $item->starts_at->format('d.m.Y H:i') : null;
        
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
            'symptoms', 'diagnoses', 'edit_starts_at', 'selectedSymptoms', 'selectedDiagnoses'
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
            'symptoms.dictionarySymptom', 'diagnoses.dictionaryDiagnosis'
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
        ])->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function store(StoreRequest $request) : RedirectResponse
    {
        $validated = $request->validated();
        $visit = $this->model::create($validated);
        
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
            ->with('success', 'Запись на прием успешно создана');
    }

    public function update(UpdateRequest $request, $id) : RedirectResponse
    {
        $validated = $request->validated();
        $visit = $this->model::findOrFail($id);
        $visit->update($validated);
        
        // Удаляем все старые симптомы этого визита
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
        
        // Удаляем все старые диагнозы этого визита
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
        $visit->delete();
        return redirect()->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись на приём успешно удалена');
    }

} 