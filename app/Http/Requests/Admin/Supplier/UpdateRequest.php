<?php

namespace App\Http\Requests\Admin\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Необходимо указать название поставщика',
            'name.string' => 'Название должно быть строкой',
            'name.max' => 'Название не может быть длиннее 255 символов',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'название поставщика',
        ];
    }
} 