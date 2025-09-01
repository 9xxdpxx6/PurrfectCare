<?php

namespace App\Http\Requests\Admin\Role;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Permission;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guard('admin')->user()->can('roles.create');
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
                'unique:roles,name,NULL,id,guard_name,admin',
                'regex:/^[a-z0-9\-]+$/',
            ],
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название роли обязательно для заполнения',
            'name.unique' => 'Роль с таким названием уже существует',
            'name.regex' => 'Название роли может содержать только латинские буквы, цифры и дефисы',
            'permissions.required' => 'Необходимо выбрать хотя бы одно право',
            'permissions.array' => 'Права должны быть переданы в виде массива',
            'permissions.min' => 'Необходимо выбрать хотя бы одно право',
            'permissions.*.exists' => 'Выбрано несуществующее право',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'название роли',
            'permissions' => 'права',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => strtolower(trim($this->name)),
            ]);
        }
    }
}
