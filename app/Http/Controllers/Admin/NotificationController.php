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

class NotificationController extends AdminController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
        $this->permissionPrefix = 'notifications';
    }

    /**
     * Показать страницу уведомлений
     */
    public function index(Request $request): View
    {
        return view('admin.notifications.index');
    }

    /**
     * Получить уведомления для AJAX запросов
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'type', 'dateFrom', 'dateTo', 'sort']);
        $perPage = $request->get('per_page', 25);
        
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

    /**
     * Получить последние уведомления и количество непрочитанных
     */
    public function getRecentNotifications(): JsonResponse
    {
        // Дополнительная проверка авторизации
        if (!Auth::guard('admin')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

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
        $this->authorize('notifications.update');
        
        // Оптимизация: используем сервис с уже оптимизированными запросами для работы с индексами
        $success = $this->notificationService->markAsRead($notificationId);

        return response()->json([
            'success' => $success
        ]);
    }

    /**
     * Отметить все уведомления как прочитанные
     */
    public function markAllAsRead(): JsonResponse
    {
        $this->authorize('notifications.update');
        
        // Оптимизация: используем сервис с уже оптимизированными запросами для работы с индексами
        $success = $this->notificationService->markAllAsRead();

        return response()->json([
            'success' => $success
        ]);
    }
}
