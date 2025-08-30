<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Drug;
use App\Models\Branch;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
            
            Log::info('Начало списания препаратов со склада', [
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'items_count' => $order->items->count()
            ]);
            
            foreach ($order->items as $item) {
                if ($item->item_type === 'App\Models\Drug') {
                    Log::info('Списание препарата со склада', [
                        'drug_id' => $item->item_id,
                        'quantity' => $item->quantity,
                        'branch_id' => $order->branch_id
                    ]);
                    
                    $this->reduceDrugQuantity($item, $order->branch_id);
                }
            }
            
            DB::commit();
            
            Log::info('Списание препаратов со склада успешно завершено', [
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'drugs_count' => $order->items->where('item_type', 'App\Models\Drug')->count()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при списании препаратов со склада', [
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
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
            
            Log::info('Начало возврата препаратов на склад', [
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'items_count' => $order->items->count()
            ]);
            
            foreach ($order->items as $item) {
                if ($item->item_type === 'App\Models\Drug') {
                    $this->returnDrugQuantity($item, $order->branch_id);
                }
            }
            
            DB::commit();
            
            Log::info('Возврат препаратов на склад успешно завершен', [
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'drugs_count' => $order->items->where('item_type', 'App\Models\Drug')->count()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при возврате препаратов на склад', [
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Списание количества препарата со склада в конкретном филиале
     * 
     * @param mixed $orderItem Элемент заказа
     * @param int $branchId ID филиала
     * @return void
     */
    protected function reduceDrugQuantity($orderItem, int $branchId): void
    {
        try {
            // Проверяем, что branchId не null
            if (is_null($branchId)) {
                throw new \InvalidArgumentException('Branch ID не может быть null');
            }

            // Получаем количество препарата в конкретном филиале
            $branch = Branch::find($branchId);
            $branchDrug = $branch->drugs()
                ->withPivot('quantity')
                ->where('drug_id', $orderItem->item_id)
                ->first();

            if (!$branchDrug) {
                throw new \Exception("Препарат не найден в филиале {$branchId}");
            }

            // Проверяем, достаточно ли препарата на складе филиала
            if ($branchDrug->pivot->quantity >= $orderItem->quantity) {
                $branchDrug->pivot->decrement('quantity', $orderItem->quantity);

                $newQuantity = $branchDrug->pivot->quantity - $orderItem->quantity;
                
                Log::info('Препарат списан со склада филиала', [
                    'drug_id' => $orderItem->item_id,
                    'branch_id' => $branchId,
                    'quantity_reduced' => $orderItem->quantity,
                    'remaining_quantity' => $newQuantity
                ]);
            } else {
                throw new \Exception("Недостаточно препарата в филиале {$branchId}. Запрошено: {$orderItem->quantity}, доступно: {$branchDrug->pivot->quantity}");
            }
        } catch (\Exception $e) {
            Log::error('Ошибка при списании препарата со склада филиала', [
                'drug_id' => $orderItem->item_id,
                'branch_id' => $branchId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Возврат количества препарата на склад в конкретном филиале
     * 
     * @param mixed $orderItem Элемент заказа
     * @param int $branchId ID филиала
     * @return void
     */
    protected function returnDrugQuantity($orderItem, int $branchId): void
    {
        try {
            // Проверяем, что branchId не null
            if (is_null($branchId)) {
                throw new \InvalidArgumentException('Branch ID не может быть null');
            }

            // Получаем текущее количество препарата в филиале
            $branch = Branch::find($branchId);
            $branchDrug = $branch->drugs()
                ->withPivot('quantity')
                ->where('drug_id', $orderItem->item_id)
                ->first();

            if (!$branchDrug) {
                // Если препарат не найден в филиале, создаем запись
                $branch->drugs()->attach($orderItem->item_id, [
                    'quantity' => $orderItem->quantity,
                ]);
                
                Log::info('Создана новая запись препарата в филиале', [
                    'drug_id' => $orderItem->item_id,
                    'branch_id' => $branchId,
                    'quantity_added' => $orderItem->quantity
                ]);
            } else {
                // Увеличиваем количество в существующей записи
                $branchDrug->pivot->increment('quantity', $orderItem->quantity);

                $newQuantity = $branchDrug->pivot->quantity + $orderItem->quantity;
                
                Log::info('Препарат возвращен на склад филиала', [
                    'drug_id' => $orderItem->item_id,
                    'branch_id' => $branchId,
                    'quantity_returned' => $orderItem->quantity,
                    'new_quantity' => $newQuantity
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Ошибка при возврате препарата на склад филиала', [
                'drug_id' => $orderItem->item_id,
                'branch_id' => $branchId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Проверить доступность препаратов на складе в конкретном филиале
     * 
     * @param array $items Массив элементов заказа
     * @param int $branchId ID филиала
     * @return array Массив с информацией о доступности
     */
    public function checkDrugAvailability(array $items, int $branchId): array
    {
        $availability = [];
        
        // Фильтруем только препараты
        $drugItems = collect($items)->filter(function($item) {
            return $item['item_type'] === 'drug';
        });
        
        if ($drugItems->isEmpty()) {
            return $availability;
        }
        
        // Получаем все нужные препараты с количеством в конкретном филиале
        $drugIds = $drugItems->pluck('item_id')->toArray();
        
        $branch = Branch::find($branchId);
        $branchDrugs = $branch->drugs()
            ->withPivot('quantity')
            ->whereIn('drugs.id', $drugIds)
            ->get()
            ->keyBy('id');
        
        foreach ($drugItems as $item) {
            $branchDrug = $branchDrugs->get($item['item_id']);
            if ($branchDrug) {
                $available = $branchDrug->pivot->quantity >= $item['quantity'];
                $availability[] = [
                    'drug_id' => $item['item_id'],
                    'drug_name' => $branchDrug->name,
                    'available_quantity' => $branchDrug->pivot->quantity,
                    'is_available' => $available
                ];
            } else {
                // Препарат не найден в филиале
                $availability[] = [
                    'drug_id' => $item['item_id'],
                    'drug_name' => 'Неизвестно',
                    'requested_quantity' => $item['quantity'],
                    'available_quantity' => 0,
                    'is_available' => false
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
                    'branch_id' => $newOrder->branch_id,
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
                    'branch_id' => $oldOrder->branch_id,
                    'drugs_count' => $oldOrder->items->where('item_type', 'App\Models\Drug')->count()
                ]);
                return;
            }

            // Сценарий 4: Заказ был и остался выполненным - корректируем разницу
            if ($wasExecuted && $isExecuted) {
                $this->processInventoryDifferenceCorrection($oldOrder, $newOrder);
                DB::commit();
                
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
        // Проверяем, изменился ли филиал - приводим к int для корректного сравнения
        $branchChanged = (int)$oldOrder->branch_id !== (int)$newOrder->branch_id;
        
        if ($branchChanged) {

            // 1. Возвращаем все препараты на склад старого филиала
            $this->processInventoryReturn($oldOrder);
            
            // 2. Списываем все препараты со склада нового филиала
            $this->processInventoryReduction($newOrder);
        } else {
            // Филиал не изменился - обычная корректировка разностей
            $oldDrugs = $this->getOrderDrugItems($oldOrder);
            $newDrugs = $this->getOrderDrugItems($newOrder);

            // Рассчитываем разности
            $differences = $this->calculateDrugDifferences($oldDrugs, $newDrugs);

            // Используем branch_id из нового заказа для корректировки
            $branchId = $newOrder->branch_id;

            foreach ($differences as $drugId => $difference) {
                if ($difference['quantity_change'] != 0) {
                    $this->applyDrugQuantityChange($drugId, $difference, $branchId);
                }
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
     * Применить изменение количества препарата в конкретном филиале
     * 
     * @param int $drugId ID препарата
     * @param array $difference Информация об изменении
     * @param int $branchId ID филиала
     * @return void
     */
    protected function applyDrugQuantityChange(int $drugId, array $difference, int $branchId): void
    {
        try {
            $quantityChange = $difference['quantity_change'];

            if ($quantityChange > 0) {
                // Увеличилось количество в заказе - нужно списать больше
                $this->reduceDrugQuantityForCorrection($drugId, $quantityChange, $branchId, $difference);
            } else {
                // Уменьшилось количество в заказе - нужно вернуть на склад
                $returnQuantity = abs($quantityChange);
                $this->returnDrugQuantityForCorrection($drugId, $returnQuantity, $branchId, $difference);
            }

        } catch (\Exception $e) {
            Log::error('Ошибка при корректировке количества препарата в филиале', [
                'drug_id' => $drugId,
                'branch_id' => $branchId,
                'difference' => $difference,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Списание дополнительного количества препарата при корректировке заказа
     */
    protected function reduceDrugQuantityForCorrection(int $drugId, int $quantityChange, int $branchId, array $difference): void
    {
        // Получаем количество препарата в филиале
        $branch = Branch::find($branchId);
        $branchDrug = $branch->drugs()->where('drug_id', $drugId)->first();

        if (!$branchDrug) {
            throw new \Exception("Препарат не найден в филиале {$branchId}");
        }

        if ($branchDrug->pivot->quantity >= $quantityChange) {
            $branchDrug->pivot->decrement('quantity', $quantityChange);

            $newQuantity = $branchDrug->pivot->quantity - $quantityChange;
            
            Log::info('Дополнительно списан препарат со склада филиала', [
                'drug_id' => $drugId,
                'branch_id' => $branchId,
                'quantity_reduced' => $quantityChange,
                'old_order_quantity' => $difference['old_quantity'],
                'new_order_quantity' => $difference['new_quantity'],
                'remaining_quantity' => $newQuantity
            ]);
        } else {
            throw new \Exception("Недостаточно препарата в филиале {$branchId} для дополнительного списания. Запрошено: {$quantityChange}, доступно: {$branchDrug->pivot->quantity}");
        }
    }

    /**
     * Возврат количества препарата на склад филиала при корректировке заказа
     */
    protected function returnDrugQuantityForCorrection(int $drugId, int $returnQuantity, int $branchId, array $difference): void
    {
        // Получаем текущее количество препарата в филиале
        $branch = Branch::find($branchId);
        $branchDrug = $branch->drugs()->where('drug_id', $drugId)->first();

        if ($branchDrug) {
            // Увеличиваем количество в существующей записи
            $branchDrug->pivot->increment('quantity', $returnQuantity);

            $newQuantity = $branchDrug->pivot->quantity + $returnQuantity;
        } else {
            // Создаем новую запись если препарат не найден в филиале
            $branch->drugs()->attach($drugId, [
                'quantity' => $returnQuantity,
            ]);
            
            $newQuantity = $returnQuantity;
        }
        
        Log::info('Возвращен препарат на склад филиала при корректировке', [
            'drug_id' => $drugId,
            'branch_id' => $branchId,
            'quantity_returned' => $returnQuantity,
            'old_order_quantity' => $difference['old_quantity'],
            'new_order_quantity' => $difference['new_quantity'],
            'new_stock_quantity' => $newQuantity
        ]);
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
