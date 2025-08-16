<?php

namespace App\Http\Requests\Admin\Settings\Breed;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'name' => 'required|string|max:255',
            'species_id' => 'required|exists:species,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название породы обязательно для заполнения',
            'species_id.required' => 'Вид животного обязателен для выбора',
            'species_id.exists' => 'Выбранный вид животного не существует',
        ];
    }
}
