<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\Breed;
use App\Services\Settings\BreedService;
use App\Http\Requests\Admin\Settings\Breed\StoreRequest;
use App\Http\Requests\Admin\Settings\Breed\UpdateRequest;
use Illuminate\Http\Request;

class BreedController extends SettingsController
{
    protected $service;
    protected $permissionPrefix = 'breeds';

    public function __construct(BreedService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Показать список пород
     */
    public function index()
    {
        $breeds = $this->service->getAll(request()->all());
        $species = $this->service->getSpeciesForSelect();
        return view('admin.settings.breeds', compact('breeds', 'species'));
    }

    /**
     * Создать новую породу
     */
    public function store(StoreRequest $request)
    {
        try {
            $this->service->create($request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при создании породы');
        }
    }

    /**
     * Обновить породу
     */
    public function update(UpdateRequest $request, Breed $breed)
    {
        try {
            $this->service->update($breed, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при обновлении породы');
        }
    }

    /**
     * Удалить породу
     */
    public function destroy(Breed $breed)
    {
        try {
            $this->service->delete($breed);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 