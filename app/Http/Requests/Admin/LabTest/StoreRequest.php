<?php

namespace App\Http\Requests\Admin\LabTest;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_id' => 'required|exists:pets,id',
            'veterinarian_id' => 'required|exists:employees,id',
            'lab_test_type_id' => 'required|exists:lab_test_types,id',
            'received_at' => 'required|date_format:Y-m-d',
            'completed_at' => 'nullable|date_format:Y-m-d|after_or_equal:received_at',
            'results' => 'nullable|array',
            'results.*.lab_test_param_id' => 'required|exists:lab_test_params,id',
            'results.*.value' => 'required|string|max:255',
            'results.*.notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'pet_id.required' => 'Питомец обязателен для выбора',
            'pet_id.exists' => 'Выбранный питомец не найден',
            'veterinarian_id.required' => 'Ветеринар обязателен для выбора',
            'veterinarian_id.exists' => 'Выбранный ветеринар не найден',
            'lab_test_type_id.required' => 'Тип анализа обязателен для выбора',
            'lab_test_type_id.exists' => 'Выбранный тип анализа не найден',
            'received_at.required' => 'Дата получения обязательна',
            'received_at.date_format' => 'Дата получения должна быть в формате дд.мм.гггг',
            'completed_at.date_format' => 'Дата завершения должна быть в формате дд.мм.гггг',
            'completed_at.after_or_equal' => 'Дата завершения должна быть не раньше даты получения',
            'results.*.lab_test_param_id.required' => 'Параметр анализа обязателен',
            'results.*.lab_test_param_id.exists' => 'Выбранный параметр анализа не найден',
            'results.*.value.required' => 'Значение результата обязательно',
            'results.*.value.max' => 'Значение результата не должно превышать 255 символов',
            'results.*.notes.max' => 'Заметки не должны превышать 500 символов',
        ];
    }

    protected function prepareForValidation()
    {
        // Преобразуем даты из формата d.m.Y в Y-m-d для валидации
        if ($this->has('received_at') && $this->received_at) {
            try {
                $this->merge([
                    'received_at' => \Carbon\Carbon::createFromFormat('d.m.Y', $this->received_at)->format('Y-m-d')
                ]);
            } catch (\Exception $e) {
                // Оставляем как есть, валидация покажет ошибку
            }
        }

        if ($this->has('completed_at') && $this->completed_at) {
            try {
                $this->merge([
                    'completed_at' => \Carbon\Carbon::createFromFormat('d.m.Y', $this->completed_at)->format('Y-m-d')
                ]);
            } catch (\Exception $e) {
                // Оставляем как есть, валидация покажет ошибку
            }
        }
    }
} 