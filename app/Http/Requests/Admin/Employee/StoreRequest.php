<?php

namespace App\Http\Requests\Admin\Employee;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\NormalizesPhone;

class StoreRequest extends FormRequest
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
            'email' => 'required|email|max:255|unique:employees,email',
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string',
            'specialties' => 'required|array|min:1',
            'specialties.*' => 'exists:specialties,id',
            'branches' => 'required|array|min:1',
            'branches.*' => 'exists:branches,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id,guard_name,admin',
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
            'name.required' => 'Необходимо указать имя сотрудника',
            'name.string' => 'Имя должно быть строкой',
            'name.max' => 'Имя не может быть длиннее 255 символов',
            'email.required' => 'Необходимо указать email',
            'email.email' => 'Неверный формат email',
            'email.max' => 'Email не может быть длиннее 255 символов',
            'email.unique' => 'Сотрудник с таким email уже существует',
            'phone.required' => 'Необходимо указать телефон',
            'phone.string' => 'Телефон должен быть строкой',
            'phone.max' => 'Телефон не может быть длиннее 20 символов',
            'specialties.required' => 'Необходимо выбрать хотя бы одну специальность',
            'specialties.array' => 'Специальности должны быть массивом',
            'specialties.min' => 'Необходимо выбрать хотя бы одну специальность',
            'specialties.*.exists' => 'Выбранная специальность не найдена',
            'branches.required' => 'Необходимо выбрать хотя бы один филиал',
            'branches.array' => 'Филиалы должны быть массивом',
            'branches.min' => 'Необходимо выбрать хотя бы один филиал',
            'branches.*.exists' => 'Выбранный филиал не найден',
            'roles.array' => 'Роли должны быть массивом',
            'roles.*.exists' => 'Выбранная роль не найдена',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'имя сотрудника',
            'email' => 'email',
            'phone' => 'телефон',
            'specialties' => 'специальности',
            'branches' => 'филиалы',
            'roles' => 'роли',
        ];
    }
} 