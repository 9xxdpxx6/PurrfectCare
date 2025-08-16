<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Drug;
use Illuminate\Support\Facades\Log;

class InventoryManagementService
{
    /**
     * Обработка списания препаратов со склада
     * 
     * @param Order $order Заказ
     * @return void
     */
    public function processInventoryReduction(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->item_type === 'App\Models\Drug') {
                $this->reduceDrugQuantity($item);
            }
        }
    }

    /**
     * Обработка возврата препаратов на склад
     * 
     * @param Order $order Заказ
     * @return void
     */
    public function processInventoryReturn(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->item_type === 'App\Models\Drug') {
                $this->returnDrugQuantity($item);
            }
        }
    }

    /**
     * Списание количества препарата со склада
     * 
     * @param mixed $orderItem Элемент заказа
     * @return void
     */
    protected function reduceDrugQuantity($orderItem): void
    {
        try {
            $drug = Drug::find($orderItem->item_id);
            if ($drug) {
                // Проверяем, достаточно ли препарата на складе
                if ($drug->quantity >= $orderItem->quantity) {
                    $drug->decrement('quantity', $orderItem->quantity);
                    
                    Log::info('Препарат списан со склада', [
                        'drug_id' => $drug->id,
                        'drug_name' => $drug->name,
                        'quantity_reduced' => $orderItem->quantity,
                        'remaining_quantity' => $drug->quantity
                    ]);
                } else {
                    Log::warning('Недостаточно препарата на складе для списания', [
                        'drug_id' => $drug->id,
                        'drug_name' => $drug->name,
                        'requested_quantity' => $orderItem->quantity,
                        'available_quantity' => $drug->quantity
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Ошибка при списании препарата со склада', [
                'drug_id' => $orderItem->item_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Возврат количества препарата на склад
     * 
     * @param mixed $orderItem Элемент заказа
     * @return void
     */
    protected function returnDrugQuantity($orderItem): void
    {
        try {
            $drug = Drug::find($orderItem->item_id);
            if ($drug) {
                $drug->increment('quantity', $orderItem->quantity);
                
                Log::info('Препарат возвращен на склад', [
                    'drug_id' => $drug->id,
                    'drug_name' => $drug->name,
                    'quantity_returned' => $orderItem->quantity,
                    'new_quantity' => $drug->quantity
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Ошибка при возврате препарата на склад', [
                'drug_id' => $orderItem->item_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Проверить доступность препаратов на складе
     * 
     * @param array $items Массив элементов заказа
     * @return array Массив с информацией о доступности
     */
    public function checkDrugAvailability(array $items): array
    {
        $availability = [];
        
        foreach ($items as $item) {
            if ($item['item_type'] === 'App\Models\Drug') {
                $drug = Drug::find($item['item_id']);
                if ($drug) {
                    $available = $drug->quantity >= $item['quantity'];
                    $availability[] = [
                        'drug_id' => $drug->id,
                        'drug_name' => $drug->name,
                        'requested_quantity' => $item['quantity'],
                        'available_quantity' => $drug->quantity,
                        'is_available' => $available
                    ];
                }
            }
        }
        
        return $availability;
    }

    /**
     * Получить общую стоимость списываемых препаратов
     * 
     * @param Order $order Заказ
     * @return float Общая стоимость
     */
    public function getInventoryReductionValue(Order $order): float
    {
        $totalValue = 0;
        
        foreach ($order->items as $item) {
            if ($item->item_type === 'App\Models\Drug') {
                $totalValue += $item->unit_price * $item->quantity;
            }
        }
        
        return $totalValue;
    }
}
