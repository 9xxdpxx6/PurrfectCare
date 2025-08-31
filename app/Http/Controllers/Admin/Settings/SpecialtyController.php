<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\Specialty;
use App\Services\Settings\SpecialtyService;
use App\Http\Requests\Admin\Settings\Specialty\StoreRequest;
use App\Http\Requests\Admin\Settings\Specialty\UpdateRequest;
use Illuminate\Http\Request;

class SpecialtyController extends SettingsController
{
    protected $service;
    protected $permissionPrefix = 'specialties';

    public function __construct(SpecialtyService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Показать список специальностей
     */
    public function index()
    {
        $specialties = $this->service->getAll(request()->all());
        return view('admin.settings.specialties', compact('specialties'));
    }

    /**
     * Создать новую специальность
     */
    public function store(StoreRequest $request)
    {
        try {
            $this->service->create($request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при создании специальности');
        }
    }

    /**
     * Обновить специальность
     */
    public function update(UpdateRequest $request, Specialty $specialty)
    {
        try {
            $this->service->update($specialty, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при обновлении специальности');
        }
    }

    /**
     * Удалить специальность
     */
    public function destroy(Specialty $specialty)
    {
        try {
            $this->service->delete($specialty);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 