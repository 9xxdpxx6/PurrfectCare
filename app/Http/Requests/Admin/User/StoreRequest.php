<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UniqueTelegram;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'telegram' => ['nullable', 'string', 'max:255', new UniqueTelegram()],
        ];
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