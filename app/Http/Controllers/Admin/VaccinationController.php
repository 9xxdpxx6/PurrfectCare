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
use App\Http\Traits\HasSelectOptions;

class VaccinationController extends AdminController
{
    use HasSelectOptions;

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
            'pet.client', 'veterinarian', 'drugs'
        ])->filter($filter);
        $items = $query->paginate(10)->withQueryString();
        
        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function create(): View
    {
        $default_administered_at = now()->format('Y-m-d');
        
        return view("admin.{$this->viewPath}.create", compact('default_administered_at'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        $vaccination = $this->model::create([
            'pet_id' => $validated['pet_id'],
            'veterinarian_id' => $validated['veterinarian_id'],
            'administered_at' => $validated['administered_at'],
            'next_due' => $validated['next_due'] ?? null,
        ]);

        // Прикрепляем препараты к вакцинации
        foreach ($validated['drugs'] as $drugData) {
            $vaccination->drugs()->attach($drugData['drug_id'], [
                'batch_number' => 'BATCH' . $drugData['drug_id'],
                'dosage' => $drugData['dosage']
            ]);
        }

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Вакцинация успешно создана');
    }

    public function show($id): View
    {
        $item = $this->model::with([
            'pet.client', 'pet.breed.species', 'veterinarian', 'drugs'
        ])->findOrFail($id);
        
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function edit($id): View
    {
        $item = $this->model::with(['drugs'])->findOrFail($id);
        
        return view("admin.{$this->viewPath}.edit", compact('item'));
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        $validated = $request->validated();
        $vaccination = $this->model::findOrFail($id);
        
        $vaccination->update([
            'pet_id' => $validated['pet_id'],
            'veterinarian_id' => $validated['veterinarian_id'],
            'administered_at' => $validated['administered_at'],
            'next_due' => $validated['next_due'] ?? null,
        ]);

        // Удаляем старые связи с препаратами
        $vaccination->drugs()->detach();
        
        // Прикрепляем новые препараты
        foreach ($validated['drugs'] as $drugData) {
            $vaccination->drugs()->attach($drugData['drug_id'], [
                'batch_number' => 'BATCH' . $drugData['drug_id'],
                'dosage' => $drugData['dosage']
            ]);
        }

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Вакцинация успешно обновлена');
    }

    public function destroy($id): RedirectResponse
    {
        $vaccination = $this->model::findOrFail($id);
        $vaccination->delete();
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Вакцинация успешно удалена');
    }
} 