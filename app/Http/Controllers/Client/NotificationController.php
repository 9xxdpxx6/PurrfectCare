<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Получить количество непрочитанных уведомлений
     */
    public function getUnreadCount(): JsonResponse
    {
        $count = Auth::user()->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Отметить уведомление как прочитанное
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    /**
     * Отметить все уведомления как прочитанные
     */
    public function markAllAsRead(): JsonResponse
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Получить все уведомления пользователя
     */
    public function index(Request $request)
    {
        $notifications = Auth::user()->notifications()
            ->latest()
            ->paginate(20);
        
        return view('client.profile.notifications.index', compact('notifications'));
    }

    /**
     * Удалить уведомление
     */
    public function destroy($id): JsonResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();
        
        return response()->json(['success' => true]);
    }
}
