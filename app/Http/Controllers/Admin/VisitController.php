<?php

namespace App\Http\Controllers\Admin;

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
use App\Http\Traits\HasSelectOptions;
use Illuminate\Support\Facades\DB;

class VisitController extends AdminController
{
    use HasSelectOptions;

    public function __construct()
    {
        $this->model = Visit::class;
        $this->viewPath = 'visits';
        $this->routePrefix = 'visits';
        $this->validationRules = [
            'client_id' => 'required|exists:users,id',
            'pet_id' => 'required|exists:pets,id',
            'schedule_id' => 'required|exists:schedules,id',
            'starts_at' => 'required|date',
            'status_id' => 'required|exists:statuses,id',
            'complaints' => 'nullable|string',
            'notes' => 'nullable|string',
            'symptoms' => 'nullable|array',
            'symptoms.*' => 'exists:symptoms,id',
            'diagnoses' => 'nullable|array',
            'diagnoses.*' => 'exists:diagnoses,id'
        ];
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
        return view("admin.{$this->viewPath}.edit", compact(
            'item', 'clients', 'pets', 'schedules', 'statuses',
            'symptoms', 'diagnoses', 'edit_starts_at'
        ));
    }

    public function index(Request $request) : View
    {
        $filter = app(VisitFilter::class, ['queryParams' => $request->query()]);
        $query = $this->model::with([
            'client', 'pet', 'schedule', 'status',
            'symptoms.dictionarySymptom', 'diagnoses.dictionaryDiagnosis'
        ])->filter($filter);
        $items = $query->paginate(10)->withQueryString();
        
        $clients = User::orderBy('name')->get();
        $pets = Pet::with('client')->orderBy('name')->get();
        $statuses = Status::orderBy('name')->get();
        
        return view("admin.{$this->viewPath}.index", compact('items', 'clients', 'pets', 'statuses'));
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