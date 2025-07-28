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
        $labTestTypes = LabTestType::orderBy('name')->get();
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

        return response()->json(['success' => true, 'message' => 'Тип анализа обновлен']);
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

            return response()->json(['success' => true, 'message' => 'Тип анализа создан']);
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
        return response()->json(['success' => true, 'message' => 'Тип анализа удален']);
    }

    // Lab Test Params
    public function labTestParams()
    {
        $labTestParams = LabTestParam::with(['labTestType', 'unit'])->orderBy('name')->get();
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

            return response()->json(['success' => true, 'message' => 'Параметр анализа обновлен']);
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
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'lab_test_type_id' => 'required|exists:lab_test_types,id',
                'unit_id' => 'nullable|exists:units,id',
            ]);

            LabTestParam::create($validated);

            return response()->json(['success' => true, 'message' => 'Параметр анализа создан']);
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
        return response()->json(['success' => true, 'message' => 'Параметр анализа удален']);
    }

    // Statuses
    public function statuses()
    {
        $statuses = Status::orderBy('name')->get();
        return view('admin.settings.statuses', compact('statuses'));
    }

    public function updateStatus(Request $request, Status $status)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        $status->update($request->only(['name', 'color']));

        return response()->json(['success' => true, 'message' => 'Статус обновлен']);
    }

    public function storeStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:statuses',
                'color' => 'required|string|max:7',
            ]);

            Status::create($validated);

            return response()->json(['success' => true, 'message' => 'Статус создан']);
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
        return response()->json(['success' => true, 'message' => 'Статус удален']);
    }

    // Units
    public function units()
    {
        $units = Unit::orderBy('name')->get();
        return view('admin.settings.units', compact('units'));
    }

    public function updateUnit(Request $request, Unit $unit)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
                'short_name' => 'required|string|max:10|unique:units,short_name,' . $unit->id,
            ]);

            $unit->update($validated);

            return response()->json(['success' => true, 'message' => 'Единица измерения обновлена']);
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

            return response()->json(['success' => true, 'message' => 'Единица измерения создана']);
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
        return response()->json(['success' => true, 'message' => 'Единица измерения удалена']);
    }

    // Branches
    public function branches()
    {
        $branches = Branch::orderBy('name')->get();
        return view('admin.settings.branches', compact('branches'));
    }

    public function updateBranch(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
        ]);

        $branch->update($request->only(['name', 'address', 'phone']));

        return response()->json(['success' => true, 'message' => 'Филиал обновлен']);
    }

    public function storeBranch(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:branches',
                'address' => 'required|string|max:500',
                'phone' => 'nullable|string|max:20',
            ]);

            Branch::create($validated);

            return response()->json(['success' => true, 'message' => 'Филиал создан']);
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
        return response()->json(['success' => true, 'message' => 'Филиал удален']);
    }

    // Specialties
    public function specialties()
    {
        $specialties = Specialty::orderBy('name')->get();
        return view('admin.settings.specialties', compact('specialties'));
    }

    public function updateSpecialty(Request $request, Specialty $specialty)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $specialty->update($request->only(['name', 'description']));

        return response()->json(['success' => true, 'message' => 'Специальность обновлена']);
    }

    public function storeSpecialty(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:specialties',
                'description' => 'nullable|string',
            ]);

            Specialty::create($validated);

            return response()->json(['success' => true, 'message' => 'Специальность создана']);
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
        return response()->json(['success' => true, 'message' => 'Специальность удалена']);
    }

    // Species
    public function species()
    {
        $species = Species::orderBy('name')->get();
        return view('admin.settings.species', compact('species'));
    }

    public function updateSpecies(Request $request, Species $species)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $species->update($request->only(['name', 'description']));

        return response()->json(['success' => true, 'message' => 'Вид животного обновлен']);
    }

    public function storeSpecies(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:species',
                'description' => 'nullable|string',
            ]);

            Species::create($validated);

            return response()->json(['success' => true, 'message' => 'Вид животного создан']);
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
        return response()->json(['success' => true, 'message' => 'Вид животного удален']);
    }

    // Breeds
    public function breeds()
    {
        $breeds = Breed::with('species')->orderBy('name')->get();
        $species = Species::orderBy('name')->get();
        return view('admin.settings.breeds', compact('breeds', 'species'));
    }

    public function updateBreed(Request $request, Breed $breed)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'species_id' => 'required|exists:species,id',
            'description' => 'nullable|string',
        ]);

        $breed->update($request->only(['name', 'species_id', 'description']));

        return response()->json(['success' => true, 'message' => 'Порода обновлена']);
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

            return response()->json(['success' => true, 'message' => 'Порода создана']);
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
        return response()->json(['success' => true, 'message' => 'Порода удалена']);
    }

    // Suppliers
    public function suppliers()
    {
        $suppliers = Supplier::orderBy('name')->get();
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

        return response()->json(['success' => true, 'message' => 'Поставщик обновлен']);
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

            return response()->json(['success' => true, 'message' => 'Поставщик создан']);
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
        return response()->json(['success' => true, 'message' => 'Поставщик удален']);
    }

    // Dictionary Diagnoses
    public function dictionaryDiagnoses()
    {
        $dictionaryDiagnoses = DictionaryDiagnosis::orderBy('name')->get();
        return view('admin.settings.dictionary-diagnoses', compact('dictionaryDiagnoses'));
    }

    public function updateDictionaryDiagnosis(Request $request, DictionaryDiagnosis $dictionaryDiagnosis)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $dictionaryDiagnosis->update($request->only(['name', 'description']));

        return response()->json(['success' => true, 'message' => 'Диагноз обновлен']);
    }

    public function storeDictionaryDiagnosis(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:dictionary_diagnoses',
                'description' => 'nullable|string',
            ]);

            DictionaryDiagnosis::create($validated);

            return response()->json(['success' => true, 'message' => 'Диагноз создан']);
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
        return response()->json(['success' => true, 'message' => 'Диагноз удален']);
    }

    // Dictionary Symptoms
    public function dictionarySymptoms()
    {
        $dictionarySymptoms = DictionarySymptom::orderBy('name')->get();
        return view('admin.settings.dictionary-symptoms', compact('dictionarySymptoms'));
    }

    public function updateDictionarySymptom(Request $request, DictionarySymptom $dictionarySymptom)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $dictionarySymptom->update($request->only(['name', 'description']));

        return response()->json(['success' => true, 'message' => 'Симптом обновлен']);
    }

    public function storeDictionarySymptom(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:dictionary_symptoms',
                'description' => 'nullable|string',
            ]);

            DictionarySymptom::create($validated);

            return response()->json(['success' => true, 'message' => 'Симптом создан']);
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
        return response()->json(['success' => true, 'message' => 'Симптом удален']);
    }
} 