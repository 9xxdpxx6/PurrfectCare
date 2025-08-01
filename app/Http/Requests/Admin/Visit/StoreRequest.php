<?php

namespace App\Http\Requests\Admin\Visit;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\BelongsToClient;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'client_id' => 'required|exists:users,id',
            'pet_id' => ['required', 'exists:pets,id', new BelongsToClient],
            'schedule_id' => 'required|exists:schedules,id',
            'starts_at' => 'required|date',
            'status_id' => 'required|exists:statuses,id',
            'complaints' => 'nullable|string',
            'notes' => 'nullable|string',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
            'symptoms' => 'nullable|array',
            'symptoms.*' => 'nullable|string',
            'diagnoses' => 'nullable|array',
            'diagnoses.*' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'client_id.required' => 'Необходимо выбрать клиента',
            'client_id.exists' => 'Клиент не найден',
            'pet_id.required' => 'Необходимо выбрать питомца',
            'pet_id.exists' => 'Питомец не найден',
            'pet_id.belongs_to_client' => 'Выбранный питомец не принадлежит указанному клиенту',
            'schedule_id.required' => 'Необходимо выбрать расписание',
            'schedule_id.exists' => 'Расписание не найдено',
            'starts_at.required' => 'Необходимо указать дату и время',
            'starts_at.date' => 'Неверный формат даты и времени',
            'status_id.required' => 'Необходимо выбрать статус',
            'status_id.exists' => 'Статус не найден',
            'complaints.string' => 'Жалобы должны быть строкой',
            'notes.string' => 'Заметки должны быть строкой',
            'services.array' => 'Услуги должны быть массивом',
            'services.*.exists' => 'Выбранная услуга не найдена',
            'symptoms.array' => 'Симптомы должны быть массивом',
            'diagnoses.array' => 'Диагнозы должны быть массивом',
        ];
    }

    public function attributes()
    {
        return [
            'client_id' => 'клиент',
            'pet_id' => 'питомец',
            'schedule_id' => 'расписание',
            'starts_at' => 'дата и время',
            'status_id' => 'статус',
            'complaints' => 'жалобы',
            'notes' => 'заметки',
            'services' => 'услуги',
            'services.*' => 'услуга',
            'symptoms' => 'симптомы',
            'symptoms.*' => 'симптом',
            'diagnoses' => 'диагнозы',
            'diagnoses.*' => 'диагноз',
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('starts_at') && $this->starts_at) {
            try {
                $dt = \Carbon\Carbon::createFromFormat('d.m.Y H:i', $this->starts_at);
                $this->merge([
                    'starts_at' => $dt->format('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) {
                // Не меняем, если формат не совпал
            }
        }
    }
} 