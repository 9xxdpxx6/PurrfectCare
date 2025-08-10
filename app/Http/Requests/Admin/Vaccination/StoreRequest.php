<?php

namespace App\Http\Requests\Admin\Vaccination;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $administered_at = null;
        $next_due = null;

        // Безопасное преобразование дат
        if ($this->administered_at && !empty(trim($this->administered_at))) {
            try {
                $administered_at = \Carbon\Carbon::createFromFormat('d.m.Y', trim($this->administered_at))->format('Y-m-d');
            } catch (\Exception $e) {
                // Ошибка будет обработана в withValidator
            }
        }

        if ($this->next_due && !empty(trim($this->next_due))) {
            try {
                $next_due = \Carbon\Carbon::createFromFormat('d.m.Y', trim($this->next_due))->format('Y-m-d');
            } catch (\Exception $e) {
                // Ошибка будет обработана в withValidator
            }
        }

        $this->merge([
            'administered_at' => $administered_at,
            'next_due' => $next_due,
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Проверяем формат и корректность дат
            if ($this->administered_at && !empty(trim($this->administered_at))) {
                $date = trim($this->administered_at);
                
                // Проверяем формат
                if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
                    $validator->errors()->add('administered_at', 'Неверный формат даты. Используйте формат дд.мм.гггг');
                } else {
                    // Проверяем корректность даты
                    try {
                        $carbonDate = \Carbon\Carbon::createFromFormat('d.m.Y', $date);
                        if (!$carbonDate->isValid()) {
                            $validator->errors()->add('administered_at', 'Некорректная дата проведения');
                        }
                    } catch (\Exception $e) {
                        $validator->errors()->add('administered_at', 'Некорректная дата проведения');
                    }
                }
            }
            
            if ($this->next_due && !empty(trim($this->next_due))) {
                $date = trim($this->next_due);
                
                // Проверяем формат
                if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
                    $validator->errors()->add('next_due', 'Неверный формат даты. Используйте формат дд.мм.гггг');
                } else {
                    // Проверяем корректность даты
                    try {
                        $carbonDate = \Carbon\Carbon::createFromFormat('d.m.Y', $date);
                        if (!$carbonDate->isValid()) {
                            $validator->errors()->add('next_due', 'Некорректная дата следующей вакцинации');
                        }
                    } catch (\Exception $e) {
                        $validator->errors()->add('next_due', 'Некорректная дата следующей вакцинации');
                    }
                }
            }
        });
    }

    public function rules()
    {
        return [
            'vaccination_type_id' => 'required|exists:vaccination_types,id',
            'pet_id' => 'required|exists:pets,id',
            'veterinarian_id' => 'required|exists:employees,id',
            'administered_at' => 'required|date',
            'next_due' => 'nullable|date|after:administered_at',
        ];
    }

    public function messages()
    {
        return [
            'vaccination_type_id.required' => 'Необходимо выбрать тип вакцинации',
            'vaccination_type_id.exists' => 'Тип вакцинации не найден',
            'pet_id.required' => 'Необходимо выбрать питомца',
            'pet_id.exists' => 'Питомец не найден',
            'veterinarian_id.required' => 'Необходимо выбрать ветеринара',
            'veterinarian_id.exists' => 'Ветеринар не найден',
            'administered_at.required' => 'Необходимо указать дату проведения',
            'administered_at.date' => 'Неверный формат даты проведения',
            'next_due.date' => 'Неверный формат даты следующей вакцинации',
            'next_due.after' => 'Дата следующей вакцинации должна быть позже даты проведения',
        ];
    }

    public function attributes()
    {
        return [
            'vaccination_type_id' => 'тип вакцинации',
            'pet_id' => 'питомец',
            'veterinarian_id' => 'ветеринар',
            'administered_at' => 'дата проведения',
            'next_due' => 'дата следующей вакцинации',
        ];
    }
} 