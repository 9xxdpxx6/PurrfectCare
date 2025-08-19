<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NotificationService;
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
            $query = Notification::where('notifiable_type', \App\Models\Employee::class)
                ->where('notifiable_id', Auth::guard('admin')->id());

            // Фильтр по статусу
            if ($request->filled('status')) {
                if ($request->status === 'unread') {
                    $query->whereNull('read_at');
                } elseif ($request->status === 'read') {
                    $query->whereNotNull('read_at');
                }
            }

            // Фильтр по типу
            if ($request->filled('type')) {
                $query->whereJsonContains('data->type', $request->type);
            }

            // Фильтр по дате
            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }

            $notifications = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            return response()->json([
                'notifications' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
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
        $success = $this->notificationService->markAllAsRead();

        return response()->json([
            'success' => $success
        ]);
    }
}
