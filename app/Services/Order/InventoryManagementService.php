<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Drug;
use Illuminate\Support\Facades\DB;
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
        try {
            DB::beginTransaction();
            
            foreach ($order->items as $item) {
                if ($item->item_type === 'App\Models\Drug') {
                    $this->reduceDrugQuantity($item);
                }
            }
            
            DB::commit();
            
            Log::info('Списание препаратов со склада успешно завершено', [
                'order_id' => $order->id,
                'drugs_count' => $order->items->where('item_type', 'App\Models\Drug')->count()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при списании препаратов со склада', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
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
        try {
            DB::beginTransaction();
            
            foreach ($order->items as $item) {
                if ($item->item_type === 'App\Models\Drug') {
                    $this->returnDrugQuantity($item);
                }
            }
            
            DB::commit();
            
            Log::info('Возврат препаратов на склад успешно завершен', [
                'order_id' => $order->id,
                'drugs_count' => $order->items->where('item_type', 'App\Models\Drug')->count()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при возврате препаратов на склад', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
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
            // Оптимизация: используем select для выбора только нужных полей и индекс на id
            $drug = Drug::select(['id', 'name', 'quantity'])->find($orderItem->item_id);
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
            // Оптимизация: используем select для выбора только нужных полей и индекс на id
            $drug = Drug::select(['id', 'name', 'quantity'])->find($orderItem->item_id);
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
        
        // Оптимизация: группируем запросы по типам для уменьшения количества обращений к БД
        $drugItems = collect($items)->filter(function($item) {
            return $item['item_type'] === 'App\Models\Drug';
        });
        
        if ($drugItems->isEmpty()) {
            return $availability;
        }
        
        // Оптимизация: получаем все нужные препараты одним запросом
        $drugIds = $drugItems->pluck('item_id')->toArray();
        $drugs = Drug::select(['id', 'name', 'quantity'])
            ->whereIn('id', $drugIds)
            ->get()
            ->keyBy('id');
        
        foreach ($drugItems as $item) {
            $drug = $drugs->get($item['item_id']);
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
        
        return $availability;
    }

    /**
     * Умная корректировка запасов при обновлении заказа
     * 
     * @param Order $oldOrder Заказ до изменений (со старыми items)
     * @param Order $newOrder Заказ после изменений (с новыми items)
     * @param bool $wasExecuted Был ли заказ выполнен до изменений
     * @param bool $isExecuted Выполнен ли заказ после изменений
     * @return void
     */
    public function processInventoryUpdateCorrection(Order $oldOrder, Order $newOrder, bool $wasExecuted, bool $isExecuted): void
    {
        try {
            DB::beginTransaction();

            // Сценарий 1: Заказ не был выполнен и не стал выполненным - ничего не делаем
            if (!$wasExecuted && !$isExecuted) {
                DB::commit();
                Log::info('Заказ не выполнен, корректировка запасов не требуется', [
                    'order_id' => $newOrder->id
                ]);
                return;
            }

            // Сценарий 2: Заказ не был выполнен, но стал выполненным - списываем все новые препараты
            if (!$wasExecuted && $isExecuted) {
                $this->processInventoryReduction($newOrder);
                DB::commit();
                Log::info('Заказ стал выполненным, списаны препараты', [
                    'order_id' => $newOrder->id,
                    'drugs_count' => $newOrder->items->where('item_type', 'App\Models\Drug')->count()
                ]);
                return;
            }

            // Сценарий 3: Заказ был выполнен, но стал невыполненным - возвращаем все старые препараты
            if ($wasExecuted && !$isExecuted) {
                $this->processInventoryReturn($oldOrder);
                DB::commit();
                Log::info('Заказ стал невыполненным, возвращены препараты', [
                    'order_id' => $newOrder->id,
                    'drugs_count' => $oldOrder->items->where('item_type', 'App\Models\Drug')->count()
                ]);
                return;
            }

            // Сценарий 4: Заказ был и остался выполненным - корректируем разницу
            if ($wasExecuted && $isExecuted) {
                $this->processInventoryDifferenceCorrection($oldOrder, $newOrder);
                DB::commit();
                Log::info('Скорректированы остатки препаратов для выполненного заказа', [
                    'order_id' => $newOrder->id
                ]);
                return;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при корректировке запасов заказа', [
                'order_id' => $newOrder->id,
                'was_executed' => $wasExecuted,
                'is_executed' => $isExecuted,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Корректировка запасов на основе разницы между старым и новым заказом
     * 
     * @param Order $oldOrder Заказ до изменений
     * @param Order $newOrder Заказ после изменений
     * @return void
     */
    protected function processInventoryDifferenceCorrection(Order $oldOrder, Order $newOrder): void
    {
        // Получаем старые и новые препараты
        $oldDrugs = $this->getOrderDrugItems($oldOrder);
        $newDrugs = $this->getOrderDrugItems($newOrder);

        // Рассчитываем разности
        $differences = $this->calculateDrugDifferences($oldDrugs, $newDrugs);

        foreach ($differences as $drugId => $difference) {
            if ($difference['quantity_change'] != 0) {
                $this->applyDrugQuantityChange($drugId, $difference);
            }
        }
    }

    /**
     * Получить препараты из заказа в виде массива [drug_id => quantity]
     * 
     * @param Order $order Заказ
     * @return array Массив препаратов
     */
    protected function getOrderDrugItems(Order $order): array
    {
        $drugs = [];
        
        foreach ($order->items as $item) {
            if ($item->item_type === 'App\Models\Drug') {
                $drugs[$item->item_id] = [
                    'quantity' => $item->quantity,
                    'drug_id' => $item->item_id
                ];
            }
        }
        
        return $drugs;
    }

    /**
     * Рассчитать разности между старыми и новыми препаратами
     * 
     * @param array $oldDrugs Старые препараты
     * @param array $newDrugs Новые препараты
     * @return array Разности
     */
    protected function calculateDrugDifferences(array $oldDrugs, array $newDrugs): array
    {
        $differences = [];
        $allDrugIds = array_unique(array_merge(array_keys($oldDrugs), array_keys($newDrugs)));

        foreach ($allDrugIds as $drugId) {
            $oldQuantity = $oldDrugs[$drugId]['quantity'] ?? 0;
            $newQuantity = $newDrugs[$drugId]['quantity'] ?? 0;
            $quantityChange = $newQuantity - $oldQuantity;

            if ($quantityChange != 0) {
                $differences[$drugId] = [
                    'drug_id' => $drugId,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'quantity_change' => $quantityChange
                ];
            }
        }

        return $differences;
    }

    /**
     * Применить изменение количества препарата
     * 
     * @param int $drugId ID препарата
     * @param array $difference Информация об изменении
     * @return void
     */
    protected function applyDrugQuantityChange(int $drugId, array $difference): void
    {
        try {
            $drug = Drug::select(['id', 'name', 'quantity'])->find($drugId);
            if (!$drug) {
                Log::warning('Препарат не найден при корректировке', [
                    'drug_id' => $drugId
                ]);
                return;
            }

            $quantityChange = $difference['quantity_change'];

            if ($quantityChange > 0) {
                // Увеличилось количество в заказе - нужно списать больше
                if ($drug->quantity >= $quantityChange) {
                    $drug->decrement('quantity', $quantityChange);
                    
                    Log::info('Дополнительно списан препарат со склада', [
                        'drug_id' => $drug->id,
                        'drug_name' => $drug->name,
                        'quantity_reduced' => $quantityChange,
                        'old_order_quantity' => $difference['old_quantity'],
                        'new_order_quantity' => $difference['new_quantity'],
                        'remaining_quantity' => $drug->quantity
                    ]);
                } else {
                    Log::warning('Недостаточно препарата на складе для дополнительного списания', [
                        'drug_id' => $drug->id,
                        'drug_name' => $drug->name,
                        'requested_additional' => $quantityChange,
                        'available_quantity' => $drug->quantity
                    ]);
                }
            } else {
                // Уменьшилось количество в заказе - нужно вернуть на склад
                $returnQuantity = abs($quantityChange);
                $drug->increment('quantity', $returnQuantity);
                
                Log::info('Возвращен препарат на склад при корректировке', [
                    'drug_id' => $drug->id,
                    'drug_name' => $drug->name,
                    'quantity_returned' => $returnQuantity,
                    'old_order_quantity' => $difference['old_quantity'],
                    'new_order_quantity' => $difference['new_quantity'],
                    'new_stock_quantity' => $drug->quantity
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Ошибка при корректировке количества препарата', [
                'drug_id' => $drugId,
                'difference' => $difference,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
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
