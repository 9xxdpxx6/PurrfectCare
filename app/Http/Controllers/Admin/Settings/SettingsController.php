<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Traits\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse as JsonResponseInterface;

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
            $validated = $request->validate($validationRules);
            $model::create($validated);
            return $this->successResponse();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse($errorMessage);
        }
    }

    /**
     * Общий метод для обновления записи
     */
    protected function updateRecord(Request $request, $model, $validationRules, $errorMessage = 'Произошла ошибка при обновлении')
    {
        try {
            $validated = $request->validate($validationRules);
            $model->update($validated);
            return $this->successResponse();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse($errorMessage);
        }
    }

    /**
     * Общий метод для удаления записи
     */
    protected function deleteRecord($model, $errorMessage = 'Невозможно удалить запись, так как она используется в системе')
    {
        if ($errorMessage = $model->hasDependencies()) {
            return $this->dependencyErrorResponse($errorMessage);
        }
        
        $model->delete();
        return $this->successResponse();
    }
} 