<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Traits\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse as JsonResponseInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class SettingsController extends Controller
{
    use JsonResponse;

    /**
     * Показать главную страницу настроек
     */
    public function index()
    {
        return view('admin.settings.index');
    }

    /**
     * Общий метод для создания записи
     */
    protected function createRecord(Request $request, $model, $validationRules, $errorMessage = 'Произошла ошибка при создании')
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validate($validationRules);
            $record = $model::create($validated);
            
            DB::commit();
            
            Log::info('Запись успешно создана', [
                'model' => get_class($model),
                'record_id' => $record->id,
                'data' => $validated
            ]);
            
            return $this->successResponse();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании записи', [
                'model' => get_class($model),
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse($errorMessage);
        }
    }

    /**
     * Общий метод для обновления записи
     */
    protected function updateRecord(Request $request, $model, $validationRules, $errorMessage = 'Произошла ошибка при обновлении')
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validate($validationRules);
            $oldData = $model->toArray();
            $result = $model->update($validated);
            
            DB::commit();
            
            Log::info('Запись успешно обновлена', [
                'model' => get_class($model),
                'record_id' => $model->id,
                'old_data' => $oldData,
                'new_data' => $validated
            ]);
            
            return $this->successResponse();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении записи', [
                'model' => get_class($model),
                'record_id' => $model->id,
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse($errorMessage);
        }
    }

    /**
     * Общий метод для удаления записи
     */
    protected function deleteRecord($model, $errorMessage = 'Невозможно удалить запись, так как она используется в системе')
    {
        try {
            DB::beginTransaction();
            
            if ($errorMessage = $model->hasDependencies()) {
                throw new \Exception($errorMessage);
            }
            
            $recordId = $model->id;
            $recordName = $model->name ?? 'Запись';
            $result = $model->delete();
            
            DB::commit();
            
            Log::info('Запись успешно удалена', [
                'model' => get_class($model),
                'record_id' => $recordId,
                'record_name' => $recordName
            ]);
            
            return $this->successResponse();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении записи', [
                'model' => get_class($model),
                'record_id' => $model->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 