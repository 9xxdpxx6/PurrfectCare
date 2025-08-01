<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\BelongsToClient;
use App\Rules\CheckDrugQuantity;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:users,id',
            'pet_id' => ['required', 'exists:pets,id', new BelongsToClient],
            'status_id' => 'required|exists:statuses,id',
            'branch_id' => 'required|exists:branches,id',
            'manager_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string|max:1000',
            'total' => 'required|numeric|min:0|max:999999.99',
            'is_paid' => 'boolean',
            'is_closed' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:service,drug,lab_test,vaccination',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999', new CheckDrugQuantity],
            'items.*.unit_price' => 'required|numeric|min:0|max:999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Клиент обязателен для выбора',
            'client_id.exists' => 'Выбранный клиент не найден',
            'pet_id.required' => 'Питомец обязателен для выбора',
            'pet_id.exists' => 'Выбранный питомец не найден',
            'pet_id.belongs_to_client' => 'Выбранный питомец не принадлежит указанному клиенту',
            'status_id.required' => 'Статус обязателен для выбора',
            'status_id.exists' => 'Выбранный статус не найден',
            'branch_id.required' => 'Филиал обязателен для выбора',
            'branch_id.exists' => 'Выбранный филиал не найден',
            'manager_id.required' => 'Менеджер обязателен для выбора',
            'manager_id.exists' => 'Выбранный менеджер не найден',
            'notes.max' => 'Заметки не должны превышать 1000 символов',
            'total.required' => 'Общая сумма обязательна',
            'total.numeric' => 'Общая сумма должна быть числом',
            'total.min' => 'Общая сумма не может быть отрицательной',
            'total.max' => 'Общая сумма не может превышать 999999.99',
            'items.required' => 'Должен быть указан хотя бы один элемент заказа',
            'items.min' => 'Должен быть указан хотя бы один элемент заказа',
            'items.*.item_type.required' => 'Тип элемента обязателен',
            'items.*.item_type.in' => 'Неизвестный тип элемента',
            'items.*.item_id.required' => 'Элемент обязателен для выбора',
            'items.*.item_id.integer' => 'ID элемента должен быть числом',
            'items.*.quantity.required' => 'Количество обязательно',
            'items.*.quantity.integer' => 'Количество должно быть целым числом',
            'items.*.quantity.min' => 'Количество должно быть не менее 1',
            'items.*.quantity.max' => 'Количество не может превышать 9999',
            'items.*.quantity.check_drug_quantity' => 'Недостаточно препарата на складе',
            'items.*.unit_price.required' => 'Цена за единицу обязательна',
            'items.*.unit_price.numeric' => 'Цена за единицу должна быть числом',
            'items.*.unit_price.min' => 'Цена за единицу не может быть отрицательной',
            'items.*.unit_price.max' => 'Цена за единицу не может превышать 999999.99',
        ];
    }
} 