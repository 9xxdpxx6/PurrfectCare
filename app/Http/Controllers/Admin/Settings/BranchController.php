<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\Branch;
use App\Services\Settings\BranchService;
use App\Http\Requests\Admin\Settings\Branch\StoreRequest;
use App\Http\Requests\Admin\Settings\Branch\UpdateRequest;
use Illuminate\Http\Request;
use App\Services\Export\ExportService;
use Illuminate\Support\Facades\Log;

class BranchController extends SettingsController
{
    protected $service;
    protected $permissionPrefix = 'branches';

    public function __construct(BranchService $service)
    {
        parent::__construct();
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

    /**
     * Экспорт филиалов
     */
    public function export(Request $request)
    {
        try {
            // Ограничиваем количество записей для экспорта (максимум 100)
            $branches = $this->service->getAll($request->all())->take(100);
            
            // Форматируем данные для экспорта
            $formattedData = $branches->map(function ($branch) {
                return [
                    'ID' => $branch->id,
                    'Название' => $branch->name,
                    'Адрес' => $branch->address,
                    'Телефон' => $branch->phone,
                    'Время открытия' => $branch->opens_at ? $branch->opens_at->format('H:i') : 'Не указано',
                    'Время закрытия' => $branch->closes_at ? $branch->closes_at->format('H:i') : 'Не указано',
                    'Количество сотрудников' => $branch->veterinarians ? $branch->veterinarians->count() : 0,
                    'Количество услуг' => $branch->services ? $branch->services->count() : 0,
                    'Дата создания' => $branch->created_at ? $branch->created_at->format('d.m.Y H:i') : '',
                    'Последнее обновление' => $branch->updated_at ? $branch->updated_at->format('d.m.Y H:i') : '',
                ];
            });
            
            $filename = app(ExportService::class)->generateFilename('branches', 'xlsx');
            
            return app(ExportService::class)->toExcel($formattedData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте филиалов', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse('Ошибка при экспорте: ' . $e->getMessage());
        }
    }
} 