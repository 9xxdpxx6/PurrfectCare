<?php

namespace App\Http\Requests\Admin\Employee;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\NormalizesPhone;

class ProfileUpdateRequest extends FormRequest
{
    use NormalizesPhone;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:6',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->phone && !$this->validatePhone($this->phone)) {
                $validator->errors()->add('phone', 'Неверный формат номера телефона. Введите корректный российский номер.');
            }
        });
    }

    protected function prepareForValidation()
    {
        if ($this->phone) {
            $this->merge([
                'phone' => $this->normalizePhone($this->phone)
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Необходимо указать имя',
            'name.string' => 'Имя должно быть строкой',
            'name.max' => 'Имя не может быть длиннее 255 символов',
            'phone.required' => 'Необходимо указать телефон',
            'phone.string' => 'Телефон должен быть строкой',
            'phone.max' => 'Телефон не может быть длиннее 20 символов',

            'current_password.string' => 'Пароль должен быть строкой',
            'new_password.string' => 'Новый пароль должен быть строкой',
            'new_password.min' => 'Новый пароль должен содержать минимум 6 символов',
        ];
    }
}
