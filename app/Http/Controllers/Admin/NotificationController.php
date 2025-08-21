<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use App\Http\Filters\NotificationFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Показать страницу уведомлений
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['status', 'type', 'dateFrom', 'dateTo', 'sort']);
            $perPage = $request->get('per_page', 25);
            
            // Отладочная информация
            \Log::info('NotificationController::index', [
                'request_all' => $request->all(),
                'filters' => $filters,
                'per_page' => $perPage,
                'user_id' => Auth::guard('admin')->id() // Добавляем ID пользователя для отслеживания
            ]);
            
            // Оптимизация: используем сервис с уже оптимизированными запросами для работы с индексами
            $result = $this->notificationService->getAllNotifications($perPage, $filters);

            return response()->json([
                'notifications' => $result->items(),
                'pagination' => [
                    'current_page' => $result->currentPage(),
                    'last_page' => $result->lastPage(),
                    'per_page' => $result->perPage(),
                    'total' => $result->total(),
                    'from' => $result->firstItem(),
                    'to' => $result->lastItem(),
                ]
            ]);
        }

        return view('admin.notifications.index');
    }

    /**
     * Получить последние уведомления и количество непрочитанных
     */
    public function getRecentNotifications(): JsonResponse
    {
        // Оптимизация: используем сервис с уже оптимизированными запросами для работы с индексами
        $unreadCount = $this->notificationService->getUnreadCount();
        $notifications = $this->notificationService->getRecentNotifications(10);

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    /**
     * Отметить уведомление как прочитанное
     */
    public function markAsRead(string $notificationId): JsonResponse
    {
        // Оптимизация: используем сервис с уже оптимизированными запросами для работы с индексами
        $success = $this->notificationService->markAsRead($notificationId);

        // Логируем результат для отслеживания производительности
        \Log::info('Notification marked as read', [
            'notification_id' => $notificationId,
            'user_id' => Auth::guard('admin')->id(),
            'success' => $success
        ]);

        return response()->json([
            'success' => $success
        ]);
    }

    /**
     * Отметить все уведомления как прочитанные
     */
    public function markAllAsRead(): JsonResponse
    {
        // Оптимизация: используем сервис с уже оптимизированными запросами для работы с индексами
        $success = $this->notificationService->markAllAsRead();

        // Логируем результат для отслеживания производительности
        \Log::info('All notifications marked as read', [
            'user_id' => Auth::guard('admin')->id(),
            'success' => $success
        ]);

        return response()->json([
            'success' => $success
        ]);
    }
}
