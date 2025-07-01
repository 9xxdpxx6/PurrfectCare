<?php

namespace App\Http\Requests\Admin\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1|max:1440',
            'branches' => 'required|array|min:1',
            'branches.*' => 'exists:branches,id'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название услуги обязательно для заполнения',
            'name.unique' => 'Услуга с таким названием уже существует',
            'name.max' => 'Название не должно превышать 255 символов',
            'description.max' => 'Описание не должно превышать 1000 символов',
            'price.required' => 'Цена обязательна для заполнения',
            'price.numeric' => 'Цена должна быть числом',
            'price.min' => 'Цена не может быть отрицательной',
            'duration.required' => 'Продолжительность обязательна для заполнения',
            'duration.integer' => 'Продолжительность должна быть целым числом',
            'duration.min' => 'Продолжительность должна быть не менее 1 минуты',
            'duration.max' => 'Продолжительность не может превышать 24 часа (1440 минут)',
            'branches.required' => 'Выберите хотя бы один филиал',
            'branches.min' => 'Выберите хотя бы один филиал',
            'branches.*.exists' => 'Выбранный филиал не существует'
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