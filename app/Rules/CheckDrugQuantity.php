<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Drug;

class CheckDrugQuantity implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Получаем индекс элемента из атрибута (например: items.0.quantity)
        preg_match('/items\.(\d+)\.quantity/', $attribute, $matches);
        if (!isset($matches[1])) {
            return;
        }
        
        $index = $matches[1];
        $itemType = request()->input("items.{$index}.item_type");
        $itemId = request()->input("items.{$index}.item_id");
        
        // Проверяем только для препаратов
        if ($itemType !== 'drug') {
            return;
        }
        
        $drug = Drug::find($itemId);
        if (!$drug) {
            $fail('Препарат не найден.');
            return;
        }
        
        // При обновлении заказа нужно учесть уже заказанные препараты
        $orderId = request()->route('order');
        $availableQuantity = $drug->quantity;
        
        if ($orderId) {
            // Получаем количество этого препарата в текущем заказе
            $currentOrderQuantity = \App\Models\OrderItem::where('order_id', $orderId)
                ->where('item_type', 'App\Models\Drug')
                ->where('item_id', $itemId)
                ->sum('quantity');
            
            $availableQuantity += $currentOrderQuantity;
        }
        
        // Проверяем достаточность количества
        if ($availableQuantity < $value) {
            $fail("Недостаточно препарата '{$drug->name}' на складе. Доступно: {$availableQuantity}, запрошено: {$value}");
        }
    }
}
