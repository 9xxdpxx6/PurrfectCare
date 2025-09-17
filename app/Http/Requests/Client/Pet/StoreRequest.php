<?php

namespace App\Http\Requests\Client\Pet;

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
            'name' => 'required|string|max:255',
            'breed_id' => 'required|exists:breeds,id',
            'birthdate' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Поле имя обязательно для заполнения.',
            'name.max' => 'Имя не должно превышать 255 символов.',
            'breed_id.required' => 'Поле порода обязательно для заполнения.',
            'breed_id.exists' => 'Выбранная порода не существует.',
            'birthdate.required' => 'Поле дата рождения обязательно для заполнения.',
            'birthdate.date' => 'Поле дата рождения должно содержать корректную дату.',
            'birthdate.before' => 'Дата рождения должна быть раньше сегодняшней даты.',
            'gender.required' => 'Поле пол обязательно для заполнения.',
            'gender.in' => 'Пол должен быть мужской или женский.',
        ];
    }
}
