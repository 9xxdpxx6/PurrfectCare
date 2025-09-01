<?php

namespace App\Http\Requests\Admin\Role;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guard('admin')->user()->can('roles.update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roleId = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                "unique:roles,name,{$roleId},id,guard_name,admin",
                'regex:/^[a-z0-9\-]+$/',
            ],
            'permissions' => 'nullable|array',
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
            'permissions.array' => 'Права должны быть переданы в виде массива',
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
