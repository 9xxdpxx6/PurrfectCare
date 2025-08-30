<?php

namespace App\Http\Requests\Admin\DrugProcurement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'drug_id' => 'required|exists:drugs,id',
            'branch_id' => 'required|exists:branches,id',
            'delivery_date' => 'required|date',
            'expiry_date' => 'required|date|after:delivery_date',
            'manufacture_date' => 'required|date|before_or_equal:delivery_date',
            'packaging_date' => 'required|date|after_or_equal:manufacture_date|before_or_equal:delivery_date',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Необходимо выбрать поставщика',
            'supplier_id.exists' => 'Выбранный поставщик не найден',
            'drug_id.required' => 'Необходимо выбрать препарат',
            'drug_id.exists' => 'Выбранный препарат не найден',
            'branch_id.required' => 'Необходимо выбрать филиал',
            'branch_id.exists' => 'Выбранный филиал не найден',
            'delivery_date.required' => 'Необходимо указать дату поставки',
            'delivery_date.date' => 'Неверный формат даты поставки',
            'expiry_date.required' => 'Необходимо указать дату истечения срока годности',
            'expiry_date.date' => 'Неверный формат даты истечения срока годности',
            'expiry_date.after' => 'Дата истечения срока годности должна быть позже даты поставки',
            'manufacture_date.required' => 'Необходимо указать дату производства',
            'manufacture_date.date' => 'Неверный формат даты производства',
            'manufacture_date.before_or_equal' => 'Дата производства должна быть не позже даты поставки',
            'packaging_date.required' => 'Необходимо указать дату упаковки',
            'packaging_date.date' => 'Неверный формат даты упаковки',
            'packaging_date.after_or_equal' => 'Дата упаковки должна быть не раньше даты производства',
            'packaging_date.before_or_equal' => 'Дата упаковки должна быть не позже даты поставки',
            'price.required' => 'Необходимо указать цену',
            'price.numeric' => 'Цена должна быть числом',
            'price.min' => 'Цена не может быть отрицательной',
            'quantity.required' => 'Необходимо указать количество',
            'quantity.integer' => 'Количество должно быть целым числом',
            'quantity.min' => 'Количество должно быть не менее 1',
        ];
    }

    public function attributes(): array
    {
        return [
            'supplier_id' => 'поставщик',
            'drug_id' => 'препарат',
            'delivery_date' => 'дата поставки',
            'expiry_date' => 'дата истечения срока годности',
            'manufacture_date' => 'дата производства',
            'packaging_date' => 'дата упаковки',
            'price' => 'цена',
            'quantity' => 'количество',
        ];
    }
} 