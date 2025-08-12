<?php

namespace App\Http\Requests\Settings\VaccinationType;

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
            'name' => 'required|string|max:255|unique:vaccination_types',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'drugs' => 'required|array|min:1',
            'drugs.*.drug_id' => 'required|exists:drugs,id',
            'drugs.*.dosage' => 'required|numeric|min:0.01|max:9999.99',
            // Batch template отключен по требованию клиники
            // 'drugs.*.batch_template' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название типа вакцинации обязательно для заполнения',
            'name.unique' => 'Тип вакцинации с таким названием уже существует',
            'price.required' => 'Цена обязательна для заполнения',
            'price.numeric' => 'Цена должна быть числом',
            'price.min' => 'Цена не может быть отрицательной',
            'drugs.required' => 'Необходимо выбрать хотя бы один препарат',
            'drugs.array' => 'Препараты должны быть массивом',
            'drugs.min' => 'Необходимо выбрать хотя бы один препарат',
            'drugs.*.drug_id.required' => 'Необходимо выбрать препарат',
            'drugs.*.drug_id.exists' => 'Выбранный препарат не найден',
            'drugs.*.dosage.required' => 'Необходимо указать дозировку',
            'drugs.*.dosage.numeric' => 'Дозировка должна быть числом',
            'drugs.*.dosage.min' => 'Дозировка должна быть больше 0',
            'drugs.*.dosage.max' => 'Дозировка не может превышать 9999.99',
        ];
    }
}
