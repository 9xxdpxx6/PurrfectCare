<?php

namespace App\Http\Requests\Admin\Settings\DictionaryDiagnosis;

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
        $dictionaryDiagnosisId = $this->route('dictionaryDiagnosis')->id;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dictionary_diagnoses')->ignore($dictionaryDiagnosisId)
            ],
            'description' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название диагноза обязательно для заполнения',
            'name.unique' => 'Диагноз с таким названием уже существует',
        ];
    }
}
