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
use App\Http\Filters\VisitFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitController extends AdminController
{
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
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
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
        $services = Service::all();
        $symptoms = Symptom::all();
        $diagnoses = Diagnosis::all();
        return view("admin.{$this->viewPath}.create", compact(
            'clients', 'pets', 'schedules', 'statuses', 'services',
            'symptoms', 'diagnoses'
        ));
    }

    public function edit($id) : View
    {
        $item = $this->model::with([
            'services', 'diagnoses', 'labTests', 'vaccinations',
            'symptoms'
        ])->findOrFail($id);
        $clients = User::all();
        $pets = Pet::all();
        $schedules = Schedule::all();
        $statuses = Status::all();
        $services = Service::all();
        $symptoms = Symptom::all();
        $diagnoses = Diagnosis::all();
        return view("admin.{$this->viewPath}.edit", compact(
            'item', 'clients', 'pets', 'schedules', 'statuses', 'services',
            'symptoms', 'diagnoses'
        ));
    }

    public function index(Request $request) : View
    {
        $filter = app(VisitFilter::class, ['queryParams' => $request->query()]);
        $query = $this->model::with([
            'client', 'pet', 'schedule', 'status',
            'symptoms', 'diagnoses'
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
            'services.service', 'symptoms', 'diagnoses', 'labTests', 'vaccinations'
        ])->findOrFail($id);
        
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function store(Request $request) : RedirectResponse
    {
        $validated = $request->validate($this->validationRules);
        
        $visit = $this->model::create($validated);
        
        if ($request->has('services')) {
            foreach ($request->services as $serviceId) {
                $visit->services()->create(['service_id' => $serviceId]);
            }
        }

        if ($request->has('symptoms')) {
            $visit->symptoms()->sync($request->symptoms);
        }

        if ($request->has('diagnoses')) {
            $visit->diagnoses()->sync($request->diagnoses); 
        }
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись на прием успешно создана');
    }

    public function update(Request $request, $id) : RedirectResponse
    {
        $validated = $request->validate($this->validationRules);
        
        $visit = $this->model::findOrFail($id);
        $visit->update($validated);
        
        if ($request->has('services')) {
            $visit->services()->delete();
            foreach ($request->services as $serviceId) {
                $visit->services()->create(['service_id' => $serviceId]);
            }
        }

        if ($request->has('symptoms')) {
            $visit->symptoms()->sync($request->symptoms);
        } else {
            $visit->symptoms()->detach();
        }

        if ($request->has('diagnoses')) {
            $visit->diagnoses()->sync($request->diagnoses);
        } else {
            $visit->diagnoses()->detach();
        }
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись на прием успешно обновлена');
    }
} 