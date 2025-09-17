<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * История заказов
     */
    public function index(Request $request): View
    {
        $query = Order::where('client_id', Auth::id())
            ->with(['items.item', 'status', 'branch']);

        // Фильтрация по статусу
        if ($request->filled('status')) {
            $query->whereHas('status', function($q) use ($request) {
                $q->where('name', $request->status);
            });
        }

        // Фильтрация по дате
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Поиск по номеру заказа или услуге
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('id', 'like', '%' . $request->search . '%')
                  ->orWhereHas('items', function($subQ) use ($request) {
                      $subQ->whereHasMorph('item', ['App\Models\Service', 'App\Models\Drug'], function($morphQ, $type) use ($request) {
                          $morphQ->where('name', 'like', '%' . $request->search . '%');
                      });
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Получаем все статусы для фильтра
        $statuses = Status::orderBy('name')->get();
        
        return view('client.profile.orders.index', compact('orders', 'statuses'));
    }

    /**
     * Детальная информация о заказе
     */
    public function show(Order $order): View
    {
        // Проверяем, что заказ принадлежит текущему пользователю
        if ($order->client_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        $order->load(['items.item', 'status', 'branch']);
        
        return view('client.profile.orders.show', compact('order'));
    }

    /**
     * Повторный заказ
     */
    public function reorder(Order $order): RedirectResponse
    {
        // Проверяем, что заказ принадлежит текущему пользователю
        if ($order->client_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        // Проверяем, что заказ можно повторить
        if ($order->status->name === 'Отменен') {
            return back()->withErrors(['error' => 'Отмененный заказ нельзя повторить.']);
        }

        // Создаем новый заказ на основе старого
        try {
            DB::beginTransaction();

            $newOrder = Order::create([
                'client_id' => Auth::id(),
                'branch_id' => $order->branch_id,
                'status_id' => Status::where('name', 'Новый')->first()->id,
                'total_amount' => $order->total_amount,
                'notes' => 'Повторный заказ от ' . $order->created_at->format('d.m.Y'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Копируем товары из старого заказа
            foreach ($order->items as $item) {
                OrderItem::create([
                    'order_id' => $newOrder->id,
                    'service_id' => $item->service_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total
                ]);
            }

            DB::commit();

            return redirect()->route('client.profile.orders.show', $newOrder)
                ->with('success', 'Заказ успешно повторен!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Ошибка при создании повторного заказа.']);
        }
    }

    /**
     * Отмена заказа
     */
    public function cancel(Order $order): RedirectResponse
    {
        // Проверяем, что заказ принадлежит текущему пользователю
        if ($order->client_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        // Проверяем, что заказ можно отменить
        if (!in_array($order->status->name, ['Новый', 'Подтвержден'])) {
            return back()->withErrors(['error' => 'Этот заказ нельзя отменить.']);
        }

        // Находим статус "Отменен"
        $cancelledStatus = Status::where('name', 'Отменен')->first();
        
        if (!$cancelledStatus) {
            return back()->withErrors(['error' => 'Статус "Отменен" не найден.']);
        }

        $order->update(['status_id' => $cancelledStatus->id]);

        return back()->with('success', 'Заказ успешно отменен.');
    }

    /**
     * Получение статусов заказа для отслеживания
     */
    public function getOrderStatuses(): array
    {
        return [
            'Новый' => [
                'description' => 'Заказ создан и ожидает подтверждения',
                'icon' => 'bi-plus-circle',
                'color' => 'primary'
            ],
            'Подтвержден' => [
                'description' => 'Заказ подтвержден и принят в работу',
                'icon' => 'bi-check-circle',
                'color' => 'info'
            ],
            'В обработке' => [
                'description' => 'Заказ обрабатывается',
                'icon' => 'bi-gear',
                'color' => 'warning'
            ],
            'Отправлен' => [
                'description' => 'Заказ отправлен',
                'icon' => 'bi-truck',
                'color' => 'info'
            ],
            'Доставлен' => [
                'description' => 'Заказ доставлен',
                'icon' => 'bi-check2-all',
                'color' => 'success'
            ],
            'Отменен' => [
                'description' => 'Заказ отменен',
                'icon' => 'bi-x-circle',
                'color' => 'danger'
            ]
        ];
    }
}
