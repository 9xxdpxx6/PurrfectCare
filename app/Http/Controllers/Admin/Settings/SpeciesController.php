<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\Species;
use App\Services\Settings\SpeciesService;
use App\Http\Requests\Settings\Species\StoreSpeciesRequest;
use App\Http\Requests\Settings\Species\UpdateSpeciesRequest;
use Illuminate\Http\Request;

class SpeciesController extends SettingsController
{
    protected $service;

    public function __construct(SpeciesService $service)
    {
        $this->service = $service;
    }

    /**
     * Показать список видов животных
     */
    public function index()
    {
        $species = $this->service->getAll(request()->all());
        return view('admin.settings.species', compact('species'));
    }

    /**
     * Создать новый вид животного
     */
    public function store(StoreSpeciesRequest $request)
    {
        try {
            $this->service->create($request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при создании вида животного');
        }
    }

    /**
     * Обновить вид животного
     */
    public function update(UpdateSpeciesRequest $request, Species $species)
    {
        try {
            $this->service->update($species, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при обновлении вида животного');
        }
    }

    /**
     * Удалить вид животного
     */
    public function destroy(Species $species)
    {
        try {
            $this->service->delete($species);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 