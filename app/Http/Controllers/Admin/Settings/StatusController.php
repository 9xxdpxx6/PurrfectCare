<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\Status;
use App\Services\Settings\StatusService;
use App\Http\Requests\Admin\Settings\Status\StoreRequest;
use App\Http\Requests\Admin\Settings\Status\UpdateRequest;
use Illuminate\Http\Request;

class StatusController extends SettingsController
{
    protected $service;
    protected $permissionPrefix = 'statuses';

    public function __construct(StatusService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Показать список статусов
     */
    public function index()
    {
        $statuses = $this->service->getAll(request()->all());
        return view('admin.settings.statuses', compact('statuses'));
    }

    /**
     * Создать новый статус
     */
    public function store(StoreRequest $request)
    {
        try {
            $this->service->create($request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при создании статуса');
        }
    }

    /**
     * Обновить статус
     */
    public function update(UpdateRequest $request, Status $status)
    {
        try {
            $this->service->update($status, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при обновлении статуса');
        }
    }

    /**
     * Удалить статус
     */
    public function destroy(Status $status)
    {
        try {
            $this->service->delete($status);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 