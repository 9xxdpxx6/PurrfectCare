<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            DB::beginTransaction();
            
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
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
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
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        return $order->visits()
            ->select(['id', 'client_id', 'pet_id', 'schedule_id', 'starts_at', 'status_id', 'is_completed'])
            ->with([
                'status:id,name',
                'client:id,name,email',
                'pet:id,name,breed_id'
            ])
            ->get();
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
        // Оптимизация: используем индекс на visit_id в промежуточной таблице
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
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        return Order::select([
                'id', 'client_id', 'pet_id', 'status_id', 'branch_id', 'manager_id',
                'total', 'is_paid', 'closed_at', 'created_at'
            ])
            ->whereHas('visits', function($query) use ($visitId) {
                $query->where('visit_id', $visitId);
            })
            ->with([
                'client:id,name,email',
                'pet:id,name,breed_id',
                'status:id,name',
                'items:id,order_id,item_type,item_id,quantity,unit_price'
            ])
            ->get();
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
            DB::beginTransaction();
            
            // Оптимизация: используем уже загруженные поля visit вместо дополнительных запросов
            $branchId = $visit->schedule ? $visit->schedule->branch_id : ($orderData['branch_id'] ?? null);
            
            // Создаем заказ
            $order = Order::create([
                'client_id' => $visit->client_id,
                'pet_id' => $visit->pet_id,
                'status_id' => $orderData['status_id'] ?? 1, // По умолчанию "Новый"
                'branch_id' => $branchId,
                'manager_id' => $orderData['manager_id'] ?? null,
                'notes' => $orderData['notes'] ?? null,
                'total' => $orderData['total'] ?? 0,
                'is_paid' => $orderData['is_paid'] ?? false,
                'closed_at' => null
            ]);
            
            // Привязываем заказ к приему
            $order->visits()->attach($visit->id);
            
            DB::commit();
            
            Log::info('Заказ создан на основе приема', [
                'order_id' => $order->id,
                'visit_id' => $visit->id,
                'client_id' => $visit->client_id,
                'pet_id' => $visit->pet_id
            ]);
            
            return $order;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании заказа на основе приема', [
                'visit_id' => $visit->id,
                'order_data' => $orderData,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Получить статистику по связям заказ-прием
     * 
     * @param Order $order Заказ
     * @return array Статистика
     */
    public function getOrderVisitStatistics(Order $order): array
    {
        // Оптимизация: используем уже загруженные связи
        $visits = $this->getOrderVisits($order);
        
        return [
            'total_visits' => $visits->count(),
            'completed_visits' => $visits->where('status.name', 'Завершен')->count(),
            'scheduled_visits' => $visits->where('status.name', 'Запланирован')->count(),
            'cancelled_visits' => $visits->where('status.name', 'Отменен')->count()
        ];
    }

    /**
     * Отвязать заказ от приема
     * 
     * @param Order $order Заказ
     * @param int $visitId ID приема
     * @return void
     */
    public function detachOrderFromVisit(Order $order, int $visitId): void
    {
        try {
            DB::beginTransaction();
            
            $order->visits()->detach($visitId);
            
            DB::commit();
            
            Log::info('Заказ отвязан от приема', [
                'order_id' => $order->id,
                'visit_id' => $visitId
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при отвязке заказа от приема', [
                'order_id' => $order->id,
                'visit_id' => $visitId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
