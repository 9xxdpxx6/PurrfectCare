<?php

namespace App\Http\Requests\Admin\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches', 'name')->ignore($this->branch->id),
            ],
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'opens_at' => 'required|date_format:H:i',
            'closes_at' => 'required|date_format:H:i|after:opens_at',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название филиала обязательно для заполнения',
            'name.unique' => 'Филиал с таким названием уже существует',
            'address.required' => 'Адрес обязателен для заполнения',
            'opens_at.required' => 'Время открытия обязательно для заполнения',
            'opens_at.date_format' => 'Время открытия должно быть в формате ЧЧ:ММ',
            'closes_at.required' => 'Время закрытия обязательно для заполнения',
            'closes_at.date_format' => 'Время закрытия должно быть в формате ЧЧ:ММ',
            'closes_at.after' => 'Время закрытия должно быть позже времени открытия',
        ];
    }
} 