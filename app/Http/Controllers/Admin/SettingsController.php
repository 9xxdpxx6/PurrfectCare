<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LabTestType;
use App\Models\LabTestParam;
use App\Models\Status;
use App\Models\Unit;
use App\Models\Branch;
use App\Models\Specialty;
use App\Models\Species;
use App\Models\Breed;
use App\Models\Supplier;
use App\Models\DictionaryDiagnosis;
use App\Models\DictionarySymptom;
use App\Http\Filters\Settings\LabTestTypeFilter;
use App\Http\Filters\Settings\LabTestParamFilter;
use App\Http\Filters\Settings\StatusFilter;
use App\Http\Filters\Settings\UnitFilter;
use App\Http\Filters\Settings\BranchFilter;
use App\Http\Filters\Settings\SpecialtyFilter;
use App\Http\Filters\Settings\SpeciesFilter;
use App\Http\Filters\Settings\BreedFilter;
use App\Http\Filters\Settings\SupplierFilter;
use App\Http\Filters\Settings\DictionaryDiagnosisFilter;
use App\Http\Filters\Settings\DictionarySymptomFilter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    // Lab Test Types
    public function labTestTypes()
    {
        $labTestTypes = LabTestType::filter(new LabTestTypeFilter(request()->all()))->orderByDesc('id')->paginate(20);
        return view('admin.settings.lab-test-types', compact('labTestTypes'));
    }

    public function updateLabTestType(Request $request, LabTestType $labTestType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        $labTestType->update($request->only(['name', 'description', 'price']));

        return response()->json(['success' => true]);
    }

    public function storeLabTestType(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:lab_test_types',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
            ]);

            LabTestType::create($validated);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при создании типа анализа'
            ], 500);
        }
    }

    public function destroyLabTestType(LabTestType $labTestType)
    {
        // Проверяем наличие зависимых записей
        if ($errorMessage = $labTestType->hasDependencies()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        }
        
        $labTestType->delete();
        return response()->json(['success' => true]);
    }

    // Lab Test Params
    public function labTestParams()
    {
        $labTestParams = LabTestParam::with(['labTestType', 'unit'])->filter(new LabTestParamFilter(request()->all()))->orderByDesc('id')->paginate(20);
        $labTestTypes = LabTestType::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        return view('admin.settings.lab-test-params', compact('labTestParams', 'labTestTypes', 'units'));
    }

    public function updateLabTestParam(Request $request, LabTestParam $labTestParam)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'lab_test_type_id' => 'required|exists:lab_test_types,id',
                'unit_id' => 'nullable|exists:units,id',
            ]);

            $labTestParam->update($validated);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при обновлении параметра анализа'
            ], 500);
        }
    }

    public function storeLabTestParam(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:lab_test_params',
                'description' => 'nullable|string',
                'lab_test_type_id' => 'required|exists:lab_test_types,id',
                'unit_id' => 'nullable|exists:units,id',
            ]);

            LabTestParam::create($validated);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при создании параметра анализа'
            ], 500);
        }
    }

    public function destroyLabTestParam(LabTestParam $labTestParam)
    {
        // Проверяем наличие зависимых записей
        if ($errorMessage = $labTestParam->hasDependencies()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        }
        
        $labTestParam->delete();
        return response()->json(['success' => true]);
    }

    // Statuses
    public function statuses()
    {
        $statuses = Status::filter(new StatusFilter(request()->all()))->orderByDesc('id')->paginate(20);
        return view('admin.settings.statuses', compact('statuses'));
    }

    public function updateStatus(Request $request, Status $status)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        $status->update($request->only(['name', 'color']));

        return response()->json(['success' => true]);
    }

    public function storeStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:statuses',
                'color' => 'required|string|max:7',
            ]);

            Status::create($validated);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при создании статуса'
            ], 500);
        }
    }

    public function destroyStatus(Status $status)
    {
        // Проверяем наличие зависимых записей
        if ($errorMessage = $status->hasDependencies()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        }
        
        $status->delete();
        return response()->json(['success' => true]);
    }

    // Units
    public function units()
    {
        $units = Unit::filter(new UnitFilter(request()->all()))->orderByDesc('id')->paginate(20);
        return view('admin.settings.units', compact('units'));
    }

    public function updateUnit(Request $request, Unit $unit)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'symbol' => 'required|string|max:10',
            ]);

            $unit->update($validated);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при обновлении единицы измерения'
            ], 500);
        }
    }

    public function storeUnit(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:units',
                'symbol' => 'required|string|max:10|unique:units',
            ]);

            Unit::create($validated);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при создании единицы измерения'
            ], 500);
        }
    }

    public function destroyUnit(Unit $unit)
    {
        // Проверяем наличие зависимых записей
        if ($errorMessage = $unit->hasDependencies()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        }
        
        $unit->delete();
        return response()->json(['success' => true]);
    }

    // Branches
    public function branches()
    {
        $branches = Branch::filter(new BranchFilter(request()->all()))->orderByDesc('id')->paginate(20);
        return view('admin.settings.branches', compact('branches'));
    }

    public function updateBranch(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'opens_at' => 'nullable|date_format:H:i',
            'closes_at' => 'nullable|date_format:H:i',
        ]);

        $branch->update($request->only(['name', 'address', 'phone', 'opens_at', 'closes_at']));

        return response()->json(['success' => true]);
    }

    public function storeBranch(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:branches',
                'address' => 'required|string|max:500',
                'phone' => 'required|string|max:20',
                'opens_at' => 'nullable|date_format:H:i',
                'closes_at' => 'nullable|date_format:H:i',
            ]);

            Branch::create($validated);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при создании филиала'
            ], 500);
        }
    }

    public function destroyBranch(Branch $branch)
    {
        // Проверяем наличие зависимых записей
        if ($errorMessage = $branch->hasDependencies()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        }
        
        $branch->delete();
        return response()->json(['success' => true]);
    }

    // Specialties
    public function specialties()
    {
        $specialties = Specialty::filter(new SpecialtyFilter(request()->all()))->orderByDesc('id')->paginate(20);
        return view('admin.settings.specialties', compact('specialties'));
    }

    public function updateSpecialty(Request $request, Specialty $specialty)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_veterinarian' => 'boolean',
        ]);

        $specialty->update($request->only(['name', 'is_veterinarian']));

        return response()->json(['success' => true]);
    }

    public function storeSpecialty(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:specialties',
                'is_veterinarian' => 'boolean',
            ]);

            Specialty::create($validated);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при создании специальности'
            ], 500);
        }
    }

    public function destroySpecialty(Specialty $specialty)
    {
        // Проверяем наличие зависимых записей
        if ($errorMessage = $specialty->hasDependencies()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        }
        
        $specialty->delete();
        return response()->json(['success' => true]);
    }

    // Species
    public function species()
    {
        $species = Species::filter(new SpeciesFilter(request()->all()))->orderByDesc('id')->paginate(20);
        return view('admin.settings.species', compact('species'));
    }

    public function updateSpecies(Request $request, Species $species)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $species->update($request->only(['name']));

        return response()->json(['success' => true]);
    }

    public function storeSpecies(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:species',
            ]);

            Species::create($validated);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при создании вида животного'
            ], 500);
        }
    }

    public function destroySpecies(Species $species)
    {
        // Проверяем наличие зависимых записей
        if ($errorMessage = $species->hasDependencies()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        }
        
        $species->delete();
        return response()->json(['success' => true]);
    }

    // Breeds
    public function breeds()
    {
        $breeds = Breed::with('species')->filter(new BreedFilter(request()->all()))->orderByDesc('id')->paginate(20);
        $species = Species::orderBy('name')->get();
        return view('admin.settings.breeds', compact('breeds', 'species'));
    }

    public function updateBreed(Request $request, Breed $breed)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'species_id' => 'required|exists:species,id',
        ]);

        $breed->update($request->only(['name', 'species_id']));

        return response()->json(['success' => true]);
    }

    public function storeBreed(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'species_id' => 'required|exists:species,id',
            ]);

            Breed::create($validated);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при создании породы'
            ], 500);
        }
    }

    public function destroyBreed(Breed $breed)
    {
        // Проверяем наличие зависимых записей
        if ($errorMessage = $breed->hasDependencies()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        }
        
        $breed->delete();
        return response()->json(['success' => true]);
    }

    // Suppliers
    public function suppliers()
    {
        $suppliers = Supplier::filter(new SupplierFilter(request()->all()))->orderByDesc('id')->paginate(20);
        return view('admin.settings.suppliers', compact('suppliers'));
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $supplier->update($request->only(['name']));

        return response()->json(['success' => true]);
    }

    public function storeSupplier(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:suppliers',
            ]);

            Supplier::create($validated);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при создании поставщика'
            ], 500);
        }
    }

    public function destroySupplier(Supplier $supplier)
    {
        // Проверяем наличие зависимых записей
        if ($errorMessage = $supplier->hasDependencies()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        }
        
        $supplier->delete();
        return response()->json(['success' => true]);
    }

    // Dictionary Diagnoses
    public function dictionaryDiagnoses()
    {
        $dictionaryDiagnoses = DictionaryDiagnosis::filter(new DictionaryDiagnosisFilter(request()->all()))->orderByDesc('id')->paginate(20);
        return view('admin.settings.dictionary-diagnoses', compact('dictionaryDiagnoses'));
    }

    public function updateDictionaryDiagnosis(Request $request, DictionaryDiagnosis $dictionaryDiagnosis)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $dictionaryDiagnosis->update($request->only(['name', 'description']));

        return response()->json(['success' => true]);
    }

    public function storeDictionaryDiagnosis(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:dictionary_diagnoses',
                'description' => 'nullable|string',
            ]);

            DictionaryDiagnosis::create($validated);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при создании диагноза'
            ], 500);
        }
    }

    public function destroyDictionaryDiagnosis(DictionaryDiagnosis $dictionaryDiagnosis)
    {
        // Проверяем наличие зависимых записей
        if ($errorMessage = $dictionaryDiagnosis->hasDependencies()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        }
        
        $dictionaryDiagnosis->delete();
        return response()->json(['success' => true]);
    }

    // Dictionary Symptoms
    public function dictionarySymptoms()
    {
        $dictionarySymptoms = DictionarySymptom::filter(new DictionarySymptomFilter(request()->all()))->orderByDesc('id')->paginate(20);
        return view('admin.settings.dictionary-symptoms', compact('dictionarySymptoms'));
    }

    public function updateDictionarySymptom(Request $request, DictionarySymptom $dictionarySymptom)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $dictionarySymptom->update($request->only(['name', 'description']));

        return response()->json(['success' => true]);
    }

    public function storeDictionarySymptom(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:dictionary_symptoms',
                'description' => 'nullable|string',
            ]);

            DictionarySymptom::create($validated);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при создании симптома'
            ], 500);
        }
    }

    public function destroyDictionarySymptom(DictionarySymptom $dictionarySymptom)
    {
        // Проверяем наличие зависимых записей
        if ($errorMessage = $dictionarySymptom->hasDependencies()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        }
        
        $dictionarySymptom->delete();
        return response()->json(['success' => true]);
    }
} 