<?php

namespace App\Http\Requests\Settings\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
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
        $branchId = $this->route('branch')->id;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches')->ignore($branchId)
            ],
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'opens_at' => 'nullable|date_format:H:i',
            'closes_at' => 'nullable|date_format:H:i',
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
            'phone.required' => 'Телефон обязателен для заполнения',
            'opens_at.date_format' => 'Время открытия должно быть в формате ЧЧ:ММ',
            'closes_at.date_format' => 'Время закрытия должно быть в формате ЧЧ:ММ',
        ];
    }
} 