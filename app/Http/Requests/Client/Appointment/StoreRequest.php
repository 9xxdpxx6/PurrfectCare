<?php

namespace App\Http\Requests\Client\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
        return [
            'branch_id' => 'required|exists:branches,id',
            'veterinarian_id' => 'required|exists:employees,id',
            'schedule_id' => 'required|exists:schedules,id',
            'pet_id' => 'nullable|integer|exists:pets,id|min:1',
            'time' => 'required|date_format:H:i',
            'complaints' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {

        // Преобразуем пустую строку pet_id в null
        if ($this->has('pet_id') && $this->input('pet_id') === '') {
            $this->merge(['pet_id' => null]);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'branch_id.required' => 'Поле филиал обязательно для заполнения.',
            'branch_id.exists' => 'Выбранный филиал не существует.',
            'veterinarian_id.required' => 'Поле ветеринар обязательно для заполнения.',
            'veterinarian_id.exists' => 'Выбранный ветеринар не существует.',
            'schedule_id.required' => 'Поле расписание обязательно для заполнения.',
            'schedule_id.exists' => 'Выбранное расписание не существует.',
            'pet_id.exists' => 'Выбранный питомец не существует.',
            'time.required' => 'Поле время обязательно для заполнения.',
            'time.date_format' => 'Время должно быть в формате ЧЧ:ММ.',
            'complaints.max' => 'Жалобы не должны превышать 1000 символов.',
        ];
    }
}
