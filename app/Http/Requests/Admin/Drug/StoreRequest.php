<?php

namespace App\Http\Requests\Admin\Drug;

use Illuminate\Foundation\Http\FormRequest;

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
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'unit_id' => 'nullable|exists:units,id',
            'prescription_required' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Необходимо указать название препарата',
            'name.string' => 'Название должно быть строкой',
            'name.max' => 'Название не может быть длиннее 255 символов',
            'price.required' => 'Необходимо указать цену',
            'price.numeric' => 'Цена должна быть числом',
            'price.min' => 'Цена не может быть отрицательной',
            'quantity.required' => 'Необходимо указать количество',
            'quantity.integer' => 'Количество должно быть целым числом',
            'quantity.min' => 'Количество не может быть отрицательным',
            'unit_id.exists' => 'Выбранная единица измерения не найдена',
            'prescription_required.boolean' => 'Поле "По рецепту" должно быть булевым значением',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'название препарата',
            'price' => 'цена',
            'quantity' => 'количество',
            'unit_id' => 'единица измерения',
            'prescription_required' => 'по рецепту',
        ];
    }
} 