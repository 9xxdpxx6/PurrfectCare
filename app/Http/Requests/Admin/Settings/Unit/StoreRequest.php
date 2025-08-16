<?php

namespace App\Http\Requests\Admin\Settings\Unit;

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
            'name' => 'required|string|max:255|unique:units',
            'symbol' => 'required|string|max:10|unique:units',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название единицы измерения обязательно для заполнения',
            'name.unique' => 'Единица измерения с таким названием уже существует',
            'symbol.required' => 'Символ единицы измерения обязателен для заполнения',
            'symbol.unique' => 'Единица измерения с таким символом уже существует',
        ];
    }
}
