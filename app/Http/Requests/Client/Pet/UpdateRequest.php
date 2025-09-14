<?php

namespace App\Http\Requests\Client\Pet;

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
            'breed_id' => 'required|exists:breeds,id',
            'birthdate' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'color' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0.1|max:999.99',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string|max:1000',
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
            'species_id.required' => 'Поле вид животного обязательно для заполнения.',
            'species_id.exists' => 'Выбранный вид животного не существует.',
            'breed_id.required' => 'Поле порода обязательно для заполнения.',
            'breed_id.exists' => 'Выбранная порода не существует.',
            'birthdate.required' => 'Поле дата рождения обязательно для заполнения.',
            'birthdate.date' => 'Поле дата рождения должно содержать корректную дату.',
            'birthdate.before' => 'Дата рождения должна быть раньше сегодняшней даты.',
            'gender.required' => 'Поле пол обязательно для заполнения.',
            'gender.in' => 'Пол должен быть мужской или женский.',
            'color.max' => 'Цвет не должен превышать 100 символов.',
            'weight.numeric' => 'Вес должен быть числом.',
            'weight.min' => 'Вес должен быть больше 0.',
            'weight.max' => 'Вес не должен превышать 999.99 кг.',
            'photo.image' => 'Файл должен быть изображением.',
            'photo.mimes' => 'Изображение должно быть в формате jpeg, png, jpg или gif.',
            'photo.max' => 'Размер изображения не должен превышать 2 МБ.',
            'description.max' => 'Описание не должно превышать 1000 символов.',
        ];
    }
}
