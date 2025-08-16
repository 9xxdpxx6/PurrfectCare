<?php

namespace App\Http\Requests\Admin\Settings\Status;

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
            'name' => 'required|string|max:255|unique:statuses',
            'color' => 'required|string|max:7',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название статуса обязательно для заполнения',
            'name.unique' => 'Статус с таким названием уже существует',
            'color.required' => 'Цвет обязателен для заполнения',
            'color.max' => 'Цвет должен быть в формате #XXXXXX',
        ];
    }
}
