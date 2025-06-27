<?php

namespace App\Http\Controllers\Admin;

use App\Models\Vaccination;
use App\Models\Pet;
use App\Models\Drug;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VaccinationController extends AdminController
{
    public function __construct()
    {
        $this->model = Vaccination::class;
        $this->viewPath = 'vaccinations';
        $this->routePrefix = 'vaccinations';
        $this->validationRules = [
            'pet_id' => 'required|exists:pets,id',
            'veterinarian_id' => 'required|exists:employees,id',
            'administered_at' => 'required|date',
            'next_due' => 'required|date|after:administered_at',
            'drugs' => 'required|array',
            'drugs.*.drug_id' => 'required|exists:drugs,id',
            'drugs.*.batch_number' => 'required|string|max:255',
            'drugs.*.dosage' => 'required|numeric|min:0'
        ];
    }

    public function create() : View
    {
        $pets = Pet::all();
        $drugs = Drug::all();
        $veterinarians = Employee::where('position', 'veterinarian')->get();
        return view("admin.{$this->viewPath}.create", compact('pets', 'drugs', 'veterinarians'));
    }

    public function edit($id) : View
    {
        $item = $this->model::with('drugs')->findOrFail($id);
        $pets = Pet::all();
        $drugs = Drug::all();
        $veterinarians = Employee::where('position', 'veterinarian')->get();
        return view("admin.{$this->viewPath}.edit", compact('item', 'pets', 'drugs', 'veterinarians'));
    }

    public function store(Request $request) : RedirectResponse
    {
        $validated = $request->validate($this->validationRules);
        
        $vaccination = $this->model::create([
            'pet_id' => $validated['pet_id'],
            'veterinarian_id' => $validated['veterinarian_id'],
            'administered_at' => $validated['administered_at'],
            'next_due' => $validated['next_due']
        ]);

        foreach ($validated['drugs'] as $drug) {
            $vaccination->drugs()->attach($drug['drug_id'], [
                'batch_number' => $drug['batch_number'],
                'dosage' => $drug['dosage']
            ]);
        }

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Вакцинация успешно создана');
    }

    public function update(Request $request, $id) : RedirectResponse
    {
        $validated = $request->validate($this->validationRules);
        
        $vaccination = $this->model::findOrFail($id);
        $vaccination->update([
            'pet_id' => $validated['pet_id'],
            'veterinarian_id' => $validated['veterinarian_id'],
            'administered_at' => $validated['administered_at'],
            'next_due' => $validated['next_due']
        ]);

        $vaccination->drugs()->detach();
        foreach ($validated['drugs'] as $drug) {
            $vaccination->drugs()->attach($drug['drug_id'], [
                'batch_number' => $drug['batch_number'],
                'dosage' => $drug['dosage']
            ]);
        }

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Вакцинация успешно обновлена');
    }
} 