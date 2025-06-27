<?php

namespace App\Http\Requests\Admin\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeekRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'veterinarian_id' => 'required|exists:employees,id',
            'branch_id' => 'required|exists:branches,id',
            'week_start' => 'required|date',
            'days' => 'required|array|min:1',
            'days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ];

        // Добавляем правила валидации времени для каждого дня
        if ($this->has('days') && is_array($this->days)) {
            foreach ($this->days as $day) {
                $rules["start_time_{$day}"] = 'required|date_format:H:i';
                $rules["end_time_{$day}"] = "required|date_format:H:i|after:start_time_{$day}";
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'veterinarian_id.required' => 'Необходимо выбрать ветеринара',
            'veterinarian_id.exists' => 'Выбранный ветеринар не найден',
            'branch_id.required' => 'Необходимо выбрать филиал',
            'branch_id.exists' => 'Выбранный филиал не найден',
            'week_start.required' => 'Необходимо указать начало недели',
            'week_start.date' => 'Неверный формат даты',
            'days.required' => 'Необходимо выбрать хотя бы один день',
            'days.array' => 'Дни должны быть выбраны в виде списка',
            'days.min' => 'Необходимо выбрать хотя бы один день',
            'days.*.in' => 'Неверно выбран день недели',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        $attributes = [
            'veterinarian_id' => 'ветеринар',
            'branch_id' => 'филиал',
            'week_start' => 'начало недели',
            'days' => 'дни недели',
        ];

        // Добавляем атрибуты для полей времени
        $dayNames = [
            'monday' => 'понедельник',
            'tuesday' => 'вторник',
            'wednesday' => 'среда',
            'thursday' => 'четверг',
            'friday' => 'пятница',
            'saturday' => 'суббота',
            'sunday' => 'воскресенье'
        ];

        if ($this->has('days') && is_array($this->days)) {
            foreach ($this->days as $day) {
                if (isset($dayNames[$day])) {
                    $attributes["start_time_{$day}"] = "время начала в {$dayNames[$day]}";
                    $attributes["end_time_{$day}"] = "время окончания в {$dayNames[$day]}";
                }
            }
        }

        return $attributes;
    }
} 