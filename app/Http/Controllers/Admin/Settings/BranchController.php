<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\Branch;
use App\Services\Settings\BranchService;
use App\Http\Requests\Settings\Branch\StoreRequest;
use App\Http\Requests\Settings\Branch\UpdateRequest;
use Illuminate\Http\Request;

class BranchController extends SettingsController
{
    protected $service;

    public function __construct(BranchService $service)
    {
        $this->service = $service;
    }

    /**
     * Показать список филиалов
     */
    public function index()
    {
        $branches = $this->service->getAll(request()->all());
        return view('admin.settings.branches', compact('branches'));
    }

    /**
     * Создать новый филиал
     */
    public function store(StoreRequest $request)
    {
        try {
            $this->service->create($request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при создании филиала');
        }
    }

    /**
     * Обновить филиал
     */
    public function update(UpdateRequest $request, Branch $branch)
    {
        try {
            $this->service->update($branch, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при обновлении филиала');
        }
    }

    /**
     * Удалить филиал
     */
    public function destroy(Branch $branch)
    {
        try {
            $this->service->delete($branch);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 