<?php

namespace App\Http\Requests\Admin\Settings\Supplier;

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
     */
    public function rules(): array
    {
        $supplierId = $this->route('supplier')->id;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('suppliers')->ignore($supplierId)
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название поставщика обязательно для заполнения',
            'name.unique' => 'Поставщик с таким названием уже существует',
        ];
    }
}
