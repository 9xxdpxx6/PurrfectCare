<?php

namespace App\Http\Requests\Settings\DictionarySymptom;

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
        $dictionarySymptomId = $this->route('dictionarySymptom')->id;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dictionary_symptoms')->ignore($dictionarySymptomId)
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
            'name.required' => 'Название симптома обязательно для заполнения',
            'name.unique' => 'Симптом с таким названием уже существует',
        ];
    }
}
