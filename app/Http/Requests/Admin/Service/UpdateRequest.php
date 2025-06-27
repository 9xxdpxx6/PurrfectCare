<?php

namespace App\Http\Requests\Admin\Service;

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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'branches' => 'required|array|min:1',
            'branches.*' => 'exists:branches,id'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Необходимо указать название услуги',
            'name.string' => 'Название должно быть строкой',
            'name.max' => 'Название не может быть длиннее 255 символов',
            'description.string' => 'Описание должно быть строкой',
            'price.required' => 'Необходимо указать цену',
            'price.numeric' => 'Цена должна быть числом',
            'price.min' => 'Цена не может быть отрицательной',
            'duration.required' => 'Необходимо указать продолжительность',
            'duration.integer' => 'Продолжительность должна быть целым числом',
            'duration.min' => 'Продолжительность должна быть не менее 1 минуты',
            'branches.required' => 'Необходимо выбрать хотя бы один филиал',
            'branches.array' => 'Филиалы должны быть массивом',
            'branches.min' => 'Необходимо выбрать хотя бы один филиал',
            'branches.*.exists' => 'Выбранный филиал не найден',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'название услуги',
            'description' => 'описание',
            'price' => 'цена',
            'duration' => 'продолжительность',
            'branches' => 'филиалы',
        ];
    }
} 