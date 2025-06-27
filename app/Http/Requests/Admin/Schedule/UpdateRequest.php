<?php

namespace App\Http\Requests\Admin\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'veterinarian_id' => 'required|exists:employees,id',
            'branch_id' => 'required|exists:branches,id',
            'shift_starts_at' => 'required|date',
            'shift_ends_at' => 'required|date|after:shift_starts_at',
        ];
    }

    public function messages(): array
    {
        return [
            'veterinarian_id.required' => 'Необходимо выбрать ветеринара',
            'veterinarian_id.exists' => 'Выбранный ветеринар не найден',
            'branch_id.required' => 'Необходимо выбрать филиал',
            'branch_id.exists' => 'Выбранный филиал не найден',
            'shift_starts_at.required' => 'Необходимо указать время начала',
            'shift_starts_at.date' => 'Неверный формат времени начала',
            'shift_ends_at.required' => 'Необходимо указать время окончания',
            'shift_ends_at.date' => 'Неверный формат времени окончания',
            'shift_ends_at.after' => 'Время окончания должно быть позже времени начала',
        ];
    }

    public function attributes(): array
    {
        return [
            'veterinarian_id' => 'ветеринар',
            'branch_id' => 'филиал',
            'shift_starts_at' => 'начало смены',
            'shift_ends_at' => 'окончание смены',
        ];
    }
} 