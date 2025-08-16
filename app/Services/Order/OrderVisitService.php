<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderVisitService
{
    /**
     * Синхронизировать связи заказа с приемами
     * 
     * @param Order $order Заказ
     * @param Request $request Запрос
     * @return void
     */
    public function syncOrderVisits(Order $order, Request $request): void
    {
        try {
            if ($request->has('visits') && is_array($request->visits)) {
                // Синхронизируем с выбранными приемами
                $order->visits()->sync($request->visits);
                
                Log::info('Связи заказа с приемами синхронизированы', [
                    'order_id' => $order->id,
                    'visit_ids' => $request->visits
                ]);
            } elseif ($request->has('visit_id') && $request->visit_id) {
                // Автоматическая привязка к конкретному приему
                $order->visits()->sync([$request->visit_id]);
                
                Log::info('Заказ привязан к приему', [
                    'order_id' => $order->id,
                    'visit_id' => $request->visit_id
                ]);
            } else {
                // Удаляем все связи, если приемы не выбраны
                $order->visits()->detach();
                
                Log::info('Все связи заказа с приемами удалены', [
                    'order_id' => $order->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Ошибка при синхронизации связей заказа с приемами', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Получить связанные приемы для заказа
     * 
     * @param Order $order Заказ
     * @return \Illuminate\Database\Eloquent\Collection Коллекция приемов
     */
    public function getOrderVisits(Order $order)
    {
        return $order->visits()->with(['status', 'client', 'pet'])->get();
    }

    /**
     * Проверить, связан ли заказ с конкретным приемом
     * 
     * @param Order $order Заказ
     * @param int $visitId ID приема
     * @return bool
     */
    public function isOrderLinkedToVisit(Order $order, int $visitId): bool
    {
        return $order->visits()->where('visit_id', $visitId)->exists();
    }

    /**
     * Получить заказы, связанные с конкретным приемом
     * 
     * @param int $visitId ID приема
     * @return \Illuminate\Database\Eloquent\Collection Коллекция заказов
     */
    public function getVisitsOrders(int $visitId)
    {
        return Order::whereHas('visits', function($query) use ($visitId) {
            $query->where('visit_id', $visitId);
        })->with(['client', 'pet', 'status', 'items'])->get();
    }

    /**
     * Создать заказ на основе приема
     * 
     * @param Visit $visit Прием
     * @param array $orderData Данные заказа
     * @return Order Созданный заказ
     */
    public function createOrderFromVisit(Visit $visit, array $orderData): Order
    {
        try {
            // Создаем заказ
            $order = Order::create([
                'client_id' => $visit->client_id,
                'pet_id' => $visit->pet_id,
                'status_id' => $orderData['status_id'] ?? 1, // По умолчанию "Новый"
                'branch_id' => $orderData['branch_id'] ?? $visit->branch_id,
                'manager_id' => $orderData['manager_id'] ?? auth()->id(),
                'notes' => $orderData['notes'] ?? null,
                'total' => $orderData['total'] ?? 0,
                'is_paid' => $orderData['is_paid'] ?? false,
                'closed_at' => null
            ]);

            // Привязываем к приему
            $order->visits()->attach($visit->id);

            Log::info('Заказ создан на основе приема', [
                'order_id' => $order->id,
                'visit_id' => $visit->id,
                'client_id' => $visit->client_id,
                'pet_id' => $visit->pet_id
            ]);

            return $order;

        } catch (\Exception $e) {
            Log::error('Ошибка при создании заказа на основе приема', [
                'visit_id' => $visit->id,
                'order_data' => $orderData,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Получить статистику по связям заказов с приемами
     * 
     * @param Order $order Заказ
     * @return array Статистика
     */
    public function getOrderVisitStatistics(Order $order): array
    {
        $visits = $this->getOrderVisits($order);
        
        $statistics = [
            'total_visits' => $visits->count(),
            'completed_visits' => $visits->where('status.name', 'Завершен')->count(),
            'pending_visits' => $visits->where('status.name', 'Ожидает')->count(),
            'cancelled_visits' => $visits->where('status.name', 'Отменен')->count(),
            'visit_dates' => $visits->pluck('visit_date')->toArray()
        ];

        return $statistics;
    }

    /**
     * Проверить доступность приема для привязки к заказу
     * 
     * @param int $visitId ID приема
     * @param int $orderId ID заказа (для исключения при обновлении)
     * @return bool
     */
    public function isVisitAvailableForOrder(int $visitId, ?int $orderId = null): bool
    {
        $query = Visit::where('id', $visitId);
        
        if ($orderId) {
            // Исключаем текущий заказ при обновлении
            $query->whereDoesntHave('orders', function($q) use ($orderId) {
                $q->where('order_id', $orderId);
            });
        }
        
        $visit = $query->first();
        
        if (!$visit) {
            return false;
        }

        // Проверяем, что прием не отменен
        if ($visit->status && $visit->status->name === 'Отменен') {
            return false;
        }

        return true;
    }

    /**
     * Получить рекомендации по приемам для заказа
     * 
     * @param int $clientId ID клиента
     * @param int $petId ID питомца
     * @return \Illuminate\Database\Eloquent\Collection Коллекция рекомендуемых приемов
     */
    public function getRecommendedVisits(int $clientId, int $petId)
    {
        return Visit::where('client_id', $clientId)
            ->where('pet_id', $petId)
            ->whereDoesntHave('orders')
            ->whereHas('status', function($query) {
                $query->whereNotIn('name', ['Отменен', 'Завершен']);
            })
            ->with(['status', 'branch'])
            ->orderBy('visit_date', 'desc')
            ->get();
    }
}
