<?php

namespace App\Http\Requests\Admin\Vaccination;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'pet_id' => 'required|exists:pets,id',
            'veterinarian_id' => 'required|exists:employees,id',
            'administered_at' => 'required|date',
            'next_due' => 'nullable|date|after:administered_at',
            'drugs' => 'required|array|min:1',
            'drugs.*.drug_id' => 'required|exists:drugs,id',
            'drugs.*.batch_number' => 'nullable|string|max:255',
            'drugs.*.dosage' => 'required|numeric|min:0.01|max:999.99'
        ];
    }

    public function messages()
    {
        return [
            'pet_id.required' => 'Необходимо выбрать питомца',
            'pet_id.exists' => 'Питомец не найден',
            'veterinarian_id.required' => 'Необходимо выбрать ветеринара',
            'veterinarian_id.exists' => 'Ветеринар не найден',
            'administered_at.required' => 'Необходимо указать дату проведения',
            'administered_at.date' => 'Неверный формат даты проведения',
            'next_due.date' => 'Неверный формат даты следующей вакцинации',
            'next_due.after' => 'Дата следующей вакцинации должна быть позже даты проведения',
            'drugs.required' => 'Необходимо выбрать хотя бы один препарат',
            'drugs.array' => 'Препараты должны быть массивом',
            'drugs.min' => 'Необходимо выбрать хотя бы один препарат',
            'drugs.*.drug_id.required' => 'Необходимо выбрать препарат',
            'drugs.*.drug_id.exists' => 'Выбранный препарат не найден',
            'drugs.*.batch_number.string' => 'Номер партии должен быть строкой',
            'drugs.*.batch_number.max' => 'Номер партии не должен превышать 255 символов',
            'drugs.*.dosage.required' => 'Необходимо указать дозировку',
            'drugs.*.dosage.numeric' => 'Дозировка должна быть числом',
            'drugs.*.dosage.min' => 'Дозировка должна быть больше 0',
            'drugs.*.dosage.max' => 'Дозировка не должна превышать 999.99'
        ];
    }

    public function attributes()
    {
        return [
            'pet_id' => 'питомец',
            'veterinarian_id' => 'ветеринар',
            'administered_at' => 'дата проведения',
            'next_due' => 'дата следующей вакцинации',
            'drugs' => 'препараты',
            'drugs.*.drug_id' => 'препарат',
            'drugs.*.batch_number' => 'номер партии',
            'drugs.*.dosage' => 'дозировка'
        ];
    }
} 