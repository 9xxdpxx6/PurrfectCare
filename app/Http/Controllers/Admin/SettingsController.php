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
                'short_name' => 'required|string|max:10',
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
                'short_name' => 'required|string|max:10|unique:units',
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
        ]);

        $branch->update($request->only(['name', 'address', 'phone']));

        return response()->json(['success' => true]);
    }

    public function storeBranch(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:branches',
                'address' => 'required|string|max:500',
                'phone' => 'required|string|max:20',
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
            'description' => 'nullable|string',
        ]);

        $specialty->update($request->only(['name', 'description']));

        return response()->json(['success' => true]);
    }

    public function storeSpecialty(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:specialties',
                'description' => 'nullable|string',
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
            'description' => 'nullable|string',
        ]);

        $species->update($request->only(['name', 'description']));

        return response()->json(['success' => true]);
    }

    public function storeSpecies(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:species',
                'description' => 'nullable|string',
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
            'description' => 'nullable|string',
            'species_id' => 'required|exists:species,id',
        ]);

        $breed->update($request->only(['name', 'description', 'species_id']));

        return response()->json(['success' => true]);
    }

    public function storeBreed(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
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
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $supplier->update($request->only(['name', 'contact_person', 'phone', 'email', 'address']));

        return response()->json(['success' => true]);
    }

    public function storeSupplier(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:suppliers',
                'contact_person' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string|max:500',
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
        $dictionarySymptom->delete();
        return response()->json(['success' => true]);
    }
} 