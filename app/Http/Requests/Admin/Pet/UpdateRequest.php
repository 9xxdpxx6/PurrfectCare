<?php

namespace App\Http\Requests\Admin\Pet;

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
            'client_id' => 'required|exists:users,id',
            'breed_id' => 'required|exists:breeds,id',
            'birthdate' => 'nullable|date',
            'gender' => 'required|in:male,female,unknown',
            'weight' => 'nullable|numeric|min:0',
            'temperature' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Необходимо указать имя питомца',
            'name.string' => 'Имя должно быть строкой',
            'name.max' => 'Имя не может быть длиннее 255 символов',
            'client_id.required' => 'Необходимо выбрать владельца',
            'client_id.exists' => 'Выбранный владелец не найден',
            'breed_id.required' => 'Необходимо выбрать породу',
            'breed_id.exists' => 'Выбранная порода не найдена',
            'birthdate.date' => 'Неверный формат даты рождения',
            'gender.required' => 'Необходимо указать пол',
            'gender.in' => 'Пол должен быть: male, female или unknown',
            'weight.numeric' => 'Вес должен быть числом',
            'weight.min' => 'Вес не может быть отрицательным',
            'temperature.numeric' => 'Температура должна быть числом',
            'temperature.min' => 'Температура не может быть отрицательной',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'имя питомца',
            'client_id' => 'владелец',
            'breed_id' => 'порода',
            'birthdate' => 'дата рождения',
            'gender' => 'пол',
            'weight' => 'вес',
            'temperature' => 'температура',
        ];
    }
} 