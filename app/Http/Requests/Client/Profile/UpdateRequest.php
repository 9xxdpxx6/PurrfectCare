<?php

namespace App\Http\Requests\Client\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Traits\NormalizesPhone;

class UpdateRequest extends FormRequest
{
    use NormalizesPhone;
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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore(auth()->id())
            ],
            'phone' => 'required|string|max:20',
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

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Поле имя обязательно для заполнения.',
            'name.max' => 'Имя не должно превышать 255 символов.',
            'email.required' => 'Поле email обязательно для заполнения.',
            'email.email' => 'Поле email должно содержать корректный email адрес.',
            'email.unique' => 'Пользователь с таким email уже зарегистрирован.',
            'phone.required' => 'Поле телефон обязательно для заполнения.',
            'phone.max' => 'Номер телефона не должен превышать 20 символов.',
        ];
    }
}
