<?php

namespace App\Http\Requests\Settings\LabTestParam;

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
            'name' => 'required|string|max:255|unique:lab_test_params',
            'description' => 'nullable|string',
            'lab_test_type_id' => 'required|exists:lab_test_types,id',
            'unit_id' => 'nullable|exists:units,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название параметра обязательно для заполнения',
            'name.unique' => 'Параметр с таким названием уже существует',
            'lab_test_type_id.required' => 'Тип анализа обязателен для выбора',
            'lab_test_type_id.exists' => 'Выбранный тип анализа не существует',
            'unit_id.exists' => 'Выбранная единица измерения не существует',
        ];
    }
}
