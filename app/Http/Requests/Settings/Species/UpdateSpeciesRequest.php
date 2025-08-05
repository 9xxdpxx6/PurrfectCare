<?php

namespace App\Http\Requests\Settings\Species;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpeciesRequest extends FormRequest
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
        $speciesId = $this->route('species')->id;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('species')->ignore($speciesId)
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название вида животного обязательно для заполнения',
            'name.unique' => 'Вид животного с таким названием уже существует',
        ];
    }
} 