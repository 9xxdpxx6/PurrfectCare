<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\NormalizesPhone;

class UpdateRequest extends FormRequest
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
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->route('user'),
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'telegram' => 'nullable|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->phone && !$this->validatePhone($this->phone)) {
                $validator->errors()->add('phone', 'Неверный формат номера телефона.');
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
            'email.required' => 'Необходимо указать email',
            'email.email' => 'Неверный формат email',
            'email.max' => 'Email не может быть длиннее 255 символов',
            'email.unique' => 'Пользователь с таким email уже существует',
            'phone.required' => 'Необходимо указать телефон',
            'phone.string' => 'Телефон должен быть строкой',
            'phone.max' => 'Телефон не может быть длиннее 20 символов',
            'address.string' => 'Адрес должен быть строкой',
            'address.max' => 'Адрес не может быть длиннее 255 символов',
            'telegram.string' => 'Telegram должен быть строкой',
            'telegram.max' => 'Telegram не может быть длиннее 255 символов',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'имя',
            'email' => 'email',
            'phone' => 'телефон',
            'address' => 'адрес',
            'telegram' => 'telegram',
        ];
    }
} 