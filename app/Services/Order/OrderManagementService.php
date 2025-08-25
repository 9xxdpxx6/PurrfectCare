<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\User;
use App\Models\Pet;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderManagementService
{
    protected $inventoryService;
    protected $itemService;
    protected $visitService;

    public function __construct(
        InventoryManagementService $inventoryService,
        OrderItemService $itemService,
        OrderVisitService $visitService
    ) {
        $this->inventoryService = $inventoryService;
        $this->itemService = $itemService;
        $this->visitService = $visitService;
    }

    /**
     * Создать заказ
     * 
     * @param array $validated Валидированные данные
     * @param Request $request Запрос
     * @return Order Созданный заказ
     */
    public function createOrder(array $validated, Request $request): Order
    {
        try {
            DB::beginTransaction();

            // Определяем дату закрытия если заказ выполнен
            $closedAt = null;
            if ($request->has('is_closed') && $request->input('is_closed')) {
                // Дополнительная проверка: заказ не может быть выполнен, если не оплачен
                if (!$request->has('is_paid') || !$request->input('is_paid')) {
                    throw new \InvalidArgumentException('Заказ не может быть выполнен, если он не оплачен.');
                }
                $closedAt = now();
            }

            // Создаем заказ
            $order = Order::create([
                'client_id' => $validated['client_id'],
                'pet_id' => $validated['pet_id'],
                'status_id' => $validated['status_id'],
                'branch_id' => $validated['branch_id'],
                'manager_id' => $validated['manager_id'],
                'notes' => $validated['notes'] ?? null,
                'total' => $validated['total'],
                'is_paid' => $request->has('is_paid') && $request->input('is_paid'),
                'closed_at' => $closedAt
            ]);

            // Создаем элементы заказа
            foreach ($validated['items'] as $item) {
                $this->itemService->createOrderItem($order, $item, $validated);
            }

            // Списание со склада только если заказ закрыт
            if ($closedAt) {
                $this->inventoryService->processInventoryReduction($order);
            }

            // Сохраняем связи с приемами
            $this->visitService->syncOrderVisits($order, $request);

            DB::commit();

            Log::info('Заказ успешно создан', [
                'order_id' => $order->id,
                'client_id' => $order->client_id,
                'total' => $order->total,
                'is_closed' => (bool)$closedAt
            ]);

            return $order;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании заказа', [
                'validated_data' => $validated,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Обновить заказ
     * 
     * @param int $id ID заказа
     * @param array $validated Валидированные данные
     * @param Request $request Запрос
     * @return Order Обновленный заказ
     */
    public function updateOrder(int $id, array $validated, Request $request): Order
    {
        try {
            DB::beginTransaction();

            // Оптимизация: используем индексы на внешние ключи и загружаем только нужные поля
            $order = Order::select([
                    'id', 'client_id', 'pet_id', 'status_id', 'branch_id', 'manager_id',
                    'notes', 'total', 'is_paid', 'closed_at', 'created_at', 'updated_at'
                ])
                ->with(['items:id,order_id,item_type,item_id,quantity,unit_price'])
                ->findOrFail($id);

            // Определяем дату закрытия если заказ выполнен
            $closedAt = $order->closed_at;
            if ($request->has('is_closed') && $request->input('is_closed') && !$closedAt) {
                // Дополнительная проверка: заказ не может быть выполнен, если не оплачен
                if (!$request->has('is_paid') || !$request->input('is_paid')) {
                    throw new \InvalidArgumentException('Заказ не может быть выполнен, если он не оплачен.');
                }
                $closedAt = now();
            } elseif (!$request->has('is_closed') || !$request->input('is_closed')) {
                $closedAt = null;
            }

            // Возвращаем препараты на склад из старого заказа если он был закрыт
            if ($order->closed_at) {
                $this->inventoryService->processInventoryReturn($order);
            }

            // Обновляем заказ
            $order->update([
                'client_id' => $validated['client_id'],
                'pet_id' => $validated['pet_id'],
                'status_id' => $validated['status_id'],
                'branch_id' => $validated['branch_id'],
                // manager_id не обновляется - остается зафиксированным при создании
                'notes' => $validated['notes'] ?? null,
                'total' => $validated['total'],
                'is_paid' => $request->has('is_paid') && $request->input('is_paid'),
                'closed_at' => $closedAt
            ]);

            // Обновляем элементы заказа
            $this->itemService->updateOrderItems($order, $validated['items'], $validated);

            // Списание со склада только если заказ закрыт
            if ($closedAt) {
                $this->inventoryService->processInventoryReduction($order);
            }

            // Обновляем связи с приемами
            $this->visitService->syncOrderVisits($order, $request);

            DB::commit();

            Log::info('Заказ успешно обновлен', [
                'order_id' => $order->id,
                'client_id' => $order->client_id,
                'total' => $order->total,
                'is_closed' => (bool)$closedAt
            ]);

            return $order;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении заказа', [
                'order_id' => $id,
                'validated_data' => $validated,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Удалить заказ
     * 
     * @param int $id ID заказа
     * @return bool Результат удаления
     */
    public function deleteOrder(int $id): bool
    {
        try {
            DB::beginTransaction();

            // Оптимизация: используем индексы на внешние ключи и загружаем только нужные поля
            $order = Order::select([
                    'id', 'closed_at'
                ])
                ->with(['items:id,order_id,item_type,item_id,quantity'])
                ->findOrFail($id);

            // Возвращаем препараты на склад если заказ был закрыт
            if ($order->closed_at) {
                $this->inventoryService->processInventoryReturn($order);
            }

            // Удаляем сам заказ (элементы удалятся каскадно)
            $order->delete();

            DB::commit();

            Log::info('Заказ успешно удален', [
                'order_id' => $id,
                'was_closed' => (bool)$order->closed_at
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении заказа', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Получить полную информацию о заказе
     * 
     * @param int $id ID заказа
     * @return Order Заказ с загруженными связями
     */
    public function getOrderWithDetails(int $id): Order
    {
        // Оптимизация: используем индексы на внешние ключи и добавляем select для выбора только нужных полей
        return Order::select([
                'id', 'client_id', 'pet_id', 'status_id', 'branch_id', 'manager_id',
                'notes', 'total', 'is_paid', 'closed_at', 'created_at', 'updated_at'
            ])
            ->with([
                'client:id,name,email,phone',
                'pet:id,name,breed_id,client_id',
                'status:id,name',
                'branch:id,name,address',
                'manager:id,name,email',
                'items:id,order_id,item_type,item_id,quantity,unit_price',
                'visits:id,client_id,pet_id,starts_at,status_id'
            ])
            ->findOrFail($id);
    }

    /**
     * Получить статистику по заказу
     * 
     * @param Order $order Заказ
     * @return array Статистика
     */
    public function getOrderStatistics(Order $order): array
    {
        // Оптимизация: используем уже загруженные связи вместо дополнительных запросов
        $drugItems = $order->items->where('item_type', 'App\Models\Drug');
        
        return [
            'items' => $this->itemService->getOrderItemsStatistics($order),
            'visits' => $this->visitService->getOrderVisitStatistics($order),
            'inventory' => [
                'drugs_value' => $this->inventoryService->getInventoryReductionValue($order),
                'has_drugs' => $drugItems->count() > 0
            ]
        ];
    }

    /**
     * Проверить доступность препаратов для заказа
     * 
     * @param array $items Массив элементов заказа
     * @return array Результат проверки
     */
    public function checkOrderAvailability(array $items): array
    {
        // Оптимизация: фильтруем только лекарства для проверки доступности
        $drugItems = collect($items)->filter(function($item) {
            return $item['item_type'] === 'App\Models\Drug';
        })->toArray();
        
        $availability = $this->inventoryService->checkDrugAvailability($drugItems);
        
        $allAvailable = collect($availability)->every('is_available');
        
        return [
            'all_available' => $allAvailable,
            'details' => $availability,
            'can_proceed' => $allAvailable
        ];
    }

    /**
     * Создать заказ на основе приема
     * 
     * @param int $visitId ID приема
     * @param array $orderData Данные заказа
     * @return Order Созданный заказ
     */
    public function createOrderFromVisit(int $visitId, array $orderData): Order
    {
        // Оптимизация: используем индекс на visit_id и загружаем только нужные поля
        $visit = Visit::select(['id', 'client_id', 'pet_id', 'schedule_id', 'starts_at'])
            ->findOrFail($visitId);
            
        return $this->visitService->createOrderFromVisit($visit, $orderData);
    }
}
