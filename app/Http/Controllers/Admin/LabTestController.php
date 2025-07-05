<?php

namespace App\Http\Controllers\Admin;

use App\Models\LabTest;
use App\Models\Pet;
use App\Models\LabTestType;
use App\Models\Employee;
use App\Models\LabTestResult;
use App\Models\LabTestParam;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabTestController extends AdminController
{
    public function __construct()
    {
        $this->model = LabTest::class;
        $this->viewPath = 'lab-tests';
        $this->routePrefix = 'lab-tests';
        $this->validationRules = [
            'pet_id' => 'required|exists:pets,id',
            'lab_test_type_id' => 'required|exists:lab_test_types,id',
            'veterinarian_id' => 'required|exists:employees,id',
            'received_at' => 'required|date',
            'completed_at' => 'nullable|date|after:received_at',
            'results' => 'nullable|array',
            'results.*.lab_test_param_id' => 'required|exists:lab_test_params,id',
            'results.*.value' => 'required|string',
            'results.*.notes' => 'nullable|string'
        ];
    }

    public function create(): View
    {
        $pets = Pet::all();
        $testTypes = LabTestType::all();
        $veterinarians = Employee::where('position', 'veterinarian')->get();
        return view("admin.{$this->viewPath}.create", compact('pets', 'testTypes', 'veterinarians'));
    }

    public function edit($id): View
    {
        $item = $this->model::with(['results', 'labTestType'])->findOrFail($id);
        $pets = Pet::all();
        $testTypes = LabTestType::all();
        $veterinarians = Employee::where('position', 'veterinarian')->get();
        $testParams = LabTestParam::where('lab_test_type_id', $item->lab_test_type_id)->get();
        return view("admin.{$this->viewPath}.edit", compact('item', 'pets', 'testTypes', 'veterinarians', 'testParams'));
    }

    public function index(Request $request): View
    {
        $items = $this->model::with(['pet', 'labTestType', 'veterinarian'])->paginate(10);
        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function show($id)
    {
        $test = $this->model::with(['pet', 'labTestType', 'veterinarian', 'results.labTestParam'])->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('test'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules);
        
        $test = $this->model::create([
            'pet_id' => $validated['pet_id'],
            'lab_test_type_id' => $validated['lab_test_type_id'],
            'veterinarian_id' => $validated['veterinarian_id'],
            'received_at' => $validated['received_at'],
            'completed_at' => $validated['completed_at'] ?? null
        ]);

        if ($request->has('results')) {
            foreach ($request->results as $result) {
                $test->results()->create([
                    'lab_test_param_id' => $result['lab_test_param_id'],
                    'value' => $result['value'],
                    'notes' => $result['notes'] ?? null
                ]);
            }
        }
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Лабораторный тест успешно создан');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->validationRules);
        
        $test = $this->model::findOrFail($id);
        $test->update([
            'pet_id' => $validated['pet_id'],
            'lab_test_type_id' => $validated['lab_test_type_id'],
            'veterinarian_id' => $validated['veterinarian_id'],
            'received_at' => $validated['received_at'],
            'completed_at' => $validated['completed_at'] ?? null
        ]);

        if ($request->has('results')) {
            $test->results()->delete();
            foreach ($request->results as $result) {
                $test->results()->create([
                    'lab_test_param_id' => $result['lab_test_param_id'],
                    'value' => $result['value'],
                    'notes' => $result['notes'] ?? null
                ]);
            }
        }
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Лабораторный тест успешно обновлен');
    }

    public function getTestParams($testTypeId)
    {
        $params = LabTestParam::where('lab_test_type_id', $testTypeId)->get();
        return response()->json($params);
    }
} 