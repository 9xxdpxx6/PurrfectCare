<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\Status;
use App\Services\Settings\StatusService;
use App\Http\Requests\Settings\Status\StoreStatusRequest;
use App\Http\Requests\Settings\Status\UpdateStatusRequest;
use Illuminate\Http\Request;

class StatusController extends SettingsController
{
    protected $service;

    public function __construct(StatusService $service)
    {
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
    public function store(StoreStatusRequest $request)
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
    public function update(UpdateStatusRequest $request, Status $status)
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