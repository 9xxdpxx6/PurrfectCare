<?php

namespace App\Http\Requests\Admin\Visit;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\BelongsToClient;
use App\Rules\VisitTimeWithinSchedule;
use App\Rules\NoVisitConflict;

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
            'visit_time' => 'required|date_format:H:i',
            'starts_at' => ['required', 'date', new VisitTimeWithinSchedule, new NoVisitConflict],
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
            'visit_time.required' => 'Необходимо указать время приёма',
            'visit_time.date_format' => 'Неверный формат времени (должно быть чч:мм)',
            'starts_at.required' => 'Необходимо указать дату и время',
            'starts_at.date' => 'Неверный формат даты и времени',
            'starts_at.visit_time_within_schedule' => 'Время приема должно находиться в рамках выбранного расписания.',
            'starts_at.no_visit_conflict' => 'На это время уже записан другой приём к данному врачу',
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
            'visit_time' => 'время приёма',
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
                
                $minutes = $dt->minute;
                if ($minutes >= 30) {
                    $dt->setMinute(30)->setSecond(0);
                } else {
                    $dt->setMinute(0)->setSecond(0);
                }

                $this->merge([
                    'starts_at' => $dt->format('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) {
                // Не меняем, если формат не совпал
            }
        }
    }
} 