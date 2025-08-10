<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Filters\VaccinationFilter;
use App\Http\Requests\Admin\Vaccination\StoreRequest;
use App\Http\Requests\Admin\Vaccination\UpdateRequest;
use App\Models\Vaccination;
use App\Models\Pet;
use App\Models\Employee;
use App\Models\Drug;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Traits\HasOptionsMethods;

class VaccinationController extends AdminController
{
    use HasOptionsMethods;

    public function __construct()
    {
        $this->model = Vaccination::class;
        $this->viewPath = 'vaccinations';
        $this->routePrefix = 'vaccinations';
    }

    public function index(Request $request): View
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
        if (isset($queryParams['next_due_from']) && $queryParams['next_due_from']) {
            try {
                $queryParams['next_due_from'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['next_due_from'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        if (isset($queryParams['next_due_to']) && $queryParams['next_due_to']) {
            try {
                $queryParams['next_due_to'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['next_due_to'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        
        $filter = app(VaccinationFilter::class, ['queryParams' => $queryParams]);
        $query = $this->model::with([
            'pet.client', 'veterinarian', 'vaccinationType.drugs'
        ])->filter($filter);
        $items = $query->paginate(25)->withQueryString();
        
        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function create(): View
    {
        $default_administered_at = now()->format('d.m.Y');
        
        return view("admin.{$this->viewPath}.create", compact('default_administered_at'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        $vaccination = $this->model::create([
            'vaccination_type_id' => $validated['vaccination_type_id'],
            'pet_id' => $validated['pet_id'],
            'veterinarian_id' => $validated['veterinarian_id'],
            'administered_at' => $validated['administered_at'],
            'next_due' => $validated['next_due'] ?? null,
        ]);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Вакцинация успешно создана');
    }

    public function show($id): View
    {
        $item = $this->model::with([
            'pet.client', 'pet.breed.species', 'veterinarian', 'vaccinationType.drugs'
        ])->findOrFail($id);
        
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function edit($id): View
    {
        $item = $this->model::with(['vaccinationType'])->findOrFail($id);
        
        return view("admin.{$this->viewPath}.edit", compact('item'));
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        $validated = $request->validated();
        $vaccination = $this->model::findOrFail($id);
        
        $vaccination->update([
            'vaccination_type_id' => $validated['vaccination_type_id'],
            'pet_id' => $validated['pet_id'],
            'veterinarian_id' => $validated['veterinarian_id'],
            'administered_at' => $validated['administered_at'],
            'next_due' => $validated['next_due'] ?? null,
        ]);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Вакцинация успешно обновлена');
    }

    public function destroy($id): RedirectResponse
    {
        $vaccination = $this->model::findOrFail($id);
        
        // Проверяем наличие зависимых записей
        if ($errorMessage = $vaccination->hasDependencies()) {
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('error', $errorMessage);
        }
        
        $vaccination->delete();
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Вакцинация успешно удалена');
    }

    public function vaccinationOptions(Request $request)
    {
        return app(\App\Services\Options\VaccinationOptionsService::class)->getOptions($request);
    }
    
    public function vaccinationTypeOptions(Request $request)
    {
        return app(\App\Services\Options\VaccinationTypeOptionsService::class)->getOptions($request);
    }
    
    public function getDrugs($id)
    {
        $vaccination = Vaccination::with('vaccinationType.drugs')->findOrFail($id);
        
        $drugs = $vaccination->drugs->map(function($drug) {
            return [
                'id' => $drug->id,
                'name' => $drug->name,
                'dosage' => $drug->pivot->dosage,
                'price' => $drug->price ?? 0
            ];
        });
        
        return response()->json($drugs);
    }
} 