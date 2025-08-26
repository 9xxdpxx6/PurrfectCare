<?php

namespace App\Http\Requests\Admin\Settings\VaccinationType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
        $vaccinationType = $this->route('vaccinationType');
        $vaccinationTypeId = $vaccinationType ? $vaccinationType->id : null;
        
        // Если ID не найден, используем ID из URL
        if (!$vaccinationTypeId) {
            $pathSegments = explode('/', $this->path());
            $vaccinationTypeId = end($pathSegments);
        }
        
        // Убеждаемся, что ID числовой
        if (!is_numeric($vaccinationTypeId)) {
            $vaccinationTypeId = null;
        }
        
        // Логируем для отладки
        \Log::info('UpdateRequest rules', [
            'route_vaccinationType' => $vaccinationType,
            'vaccinationTypeId' => $vaccinationTypeId,
            'path' => $this->path(),
            'route_parameters' => $this->route()->parameters()
        ]);
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vaccination_types')->ignore($vaccinationTypeId, 'id')
            ],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'drugs' => 'required|array|min:1',
            'drugs.*.drug_id' => 'required|exists:drugs,id',
            'drugs.*.dosage' => 'required|numeric|min:0.01|max:999.99',
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
            'drugs.*.dosage.max' => 'Дозировка не может превышать 999.99',
        ];
    }
}
