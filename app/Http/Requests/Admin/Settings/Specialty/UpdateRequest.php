<?php

namespace App\Http\Requests\Admin\Settings\Specialty;

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
        $specialtyId = $this->route('specialty')->id;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('specialties')->ignore($specialtyId)
            ],
            'is_veterinarian' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название специальности обязательно для заполнения',
            'name.unique' => 'Специальность с таким названием уже существует',
        ];
    }
}
