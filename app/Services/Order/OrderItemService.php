<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Service;
use App\Models\Drug;
use App\Models\LabTestType;
use App\Models\VaccinationType;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;

class OrderItemService
{
    /**
     * Создать элемент заказа
     * 
     * @param Order $order Заказ
     * @param array $itemData Данные элемента
     * @param array $validated Валидированные данные заказа
     * @return OrderItem Созданный элемент заказа
     */
    public function createOrderItem(Order $order, array $itemData, array $validated): OrderItem
    {
        try {
            $itemType = $this->getItemType($itemData['item_type']);
            
            // Проверяем существование элемента
            $this->validateItemExists($itemType, $itemData['item_id']);
            
            // Создаем элемент заказа
            $orderItem = $order->items()->create([
                'item_type' => $itemType,
                'item_id' => $itemData['item_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price']
            ]);

            Log::info('Элемент заказа создан', [
                'order_id' => $order->id,
                'item_type' => $itemType,
                'item_id' => $itemData['item_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price']
            ]);

            return $orderItem;

        } catch (\Exception $e) {
            Log::error('Ошибка при создании элемента заказа', [
                'order_id' => $order->id,
                'item_data' => $itemData,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Обновить элементы заказа
     * 
     * @param Order $order Заказ
     * @param array $items Массив элементов
     * @param array $validated Валидированные данные заказа
     * @return void
     */
    public function updateOrderItems(Order $order, array $items, array $validated): void
    {
        try {
            // Удаляем старые элементы
            $order->items()->delete();
            
            // Создаем новые элементы
            foreach ($items as $item) {
                $this->createOrderItem($order, $item, $validated);
            }

            Log::info('Элементы заказа обновлены', [
                'order_id' => $order->id,
                'items_count' => count($items)
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении элементов заказа', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Получить тип элемента по строковому идентификатору
     * 
     * @param string $type Строковый тип элемента
     * @return string Полное имя класса
     * @throws \InvalidArgumentException
     */
    public function getItemType(string $type): string
    {
        return match($type) {
            'service' => Service::class,
            'drug' => Drug::class,
            'lab_test' => LabTestType::class,
            'vaccination' => VaccinationType::class,
            default => throw new \InvalidArgumentException('Неизвестный тип элемента: ' . $type)
        };
    }

    /**
     * Проверить существование элемента
     * 
     * @param string $itemType Тип элемента
     * @param int $itemId ID элемента
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateItemExists(string $itemType, int $itemId): void
    {
        $item = $itemType::find($itemId);
        if (!$item) {
            throw new \InvalidArgumentException(
                'Элемент типа ' . $itemType . ' с ID ' . $itemId . ' не найден'
            );
        }
    }

    /**
     * Получить информацию об элементе заказа
     * 
     * @param OrderItem $orderItem Элемент заказа
     * @return array Информация об элементе
     */
    public function getItemInfo(OrderItem $orderItem): array
    {
        $item = $orderItem->item;
        
        if (!$item) {
            return [
                'id' => $orderItem->item_id,
                'type' => $orderItem->item_type,
                'name' => 'Элемент не найден',
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->unit_price,
                'total_price' => $orderItem->quantity * $orderItem->unit_price
            ];
        }

        return [
            'id' => $item->id,
            'type' => $orderItem->item_type,
            'name' => $item->name ?? 'Без названия',
            'quantity' => $orderItem->quantity,
            'unit_price' => $orderItem->unit_price,
            'total_price' => $orderItem->quantity * $orderItem->unit_price
        ];
    }

    /**
     * Рассчитать общую стоимость заказа
     * 
     * @param array $items Массив элементов заказа
     * @return float Общая стоимость
     */
    public function calculateOrderTotal(array $items): float
    {
        $total = 0;
        
        foreach ($items as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }
        
        return $total;
    }

    /**
     * Получить статистику по типам элементов в заказе
     * 
     * @param Order $order Заказ
     * @return array Статистика по типам
     */
    public function getOrderItemsStatistics(Order $order): array
    {
        $statistics = [
            'total_items' => 0,
            'total_value' => 0,
            'by_type' => [
                'service' => ['count' => 0, 'value' => 0],
                'drug' => ['count' => 0, 'value' => 0],
                'lab_test' => ['count' => 0, 'value' => 0],
                'vaccination' => ['count' => 0, 'value' => 0]
            ]
        ];

        foreach ($order->items as $item) {
            $statistics['total_items']++;
            $itemValue = $item->quantity * $item->unit_price;
            $statistics['total_value'] += $itemValue;

            $type = $this->getItemTypeKey($item->item_type);
            if (isset($statistics['by_type'][$type])) {
                $statistics['by_type'][$type]['count']++;
                $statistics['by_type'][$type]['value'] += $itemValue;
            }
        }

        return $statistics;
    }

    /**
     * Получить ключ типа элемента
     * 
     * @param string $itemType Полное имя класса
     * @return string Ключ типа
     */
    protected function getItemTypeKey(string $itemType): string
    {
        return match($itemType) {
            Service::class => 'service',
            Drug::class => 'drug',
            LabTestType::class => 'lab_test',
            VaccinationType::class => 'vaccination',
            default => 'unknown'
        };
    }
}
