<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Employee;
use App\Notifications\BotBookingNotification;
use App\Notifications\BotRegistrationNotification;
use App\Notifications\BotPetAddedNotification;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Отправить уведомление о новой записи через бота
     */
    public function notifyAboutBotBooking($visit)
    {
        $this->notifyAllAdmins(new BotBookingNotification($visit));
    }

    /**
     * Отправить уведомление о новой регистрации через бота
     */
    public function notifyAboutBotRegistration($user)
    {
        $this->notifyAllAdmins(new BotRegistrationNotification($user));
    }

    /**
     * Отправить уведомление о новом питомце через бота
     */
    public function notifyAboutBotPetAdded($pet)
    {
        try {
            \Log::info('Sending notification about bot pet added', [
                'pet_id' => $pet->id,
                'pet_name' => $pet->name,
                'client_id' => $pet->client_id
            ]);
            
            $this->notifyAllAdmins(new BotPetAddedNotification($pet));
            
            \Log::info('Notification about bot pet added sent successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to send notification about bot pet added', [
                'pet_id' => $pet->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Уведомить всех администраторов
     */
    protected function notifyAllAdmins($notification)
    {
        try {
            // Получаем всех сотрудников (Employee) - они и есть админы
            $employees = Employee::all();
            
            \Log::info('Found employees for notifications', [
                'count' => $employees->count(),
                'employee_ids' => $employees->pluck('id')->toArray()
            ]);
            
            if ($employees->isEmpty()) {
                // Если нет сотрудников, логируем ошибку
                \Log::warning('No employees found to send notifications to');
                return;
            }

            foreach ($employees as $employee) {
                try {
                    $employee->notify($notification);
                    \Log::info('Notification sent to employee', [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->name
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send notification to employee', [
                        'employee_id' => $employee->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error in notifyAllAdmins', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Получить количество непрочитанных уведомлений для текущего сотрудника
     */
    public function getUnreadCount()
    {
        if (!Auth::guard('admin')->check()) {
            return 0;
        }

        return Notification::where('notifiable_type', Employee::class)
            ->where('notifiable_id', Auth::guard('admin')->id())
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Получить последние уведомления для текущего сотрудника
     */
    public function getRecentNotifications($limit = 10)
    {
        if (!Auth::guard('admin')->check()) {
            return collect();
        }

        return Notification::where('notifiable_type', Employee::class)
            ->where('notifiable_id', Auth::guard('admin')->id())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Отметить уведомление как прочитанное
     */
    public function markAsRead($notificationId)
    {
        if (!Auth::guard('admin')->check()) {
            return false;
        }

        $notification = Notification::where('id', $notificationId)
            ->where('notifiable_type', Employee::class)
            ->where('notifiable_id', Auth::guard('admin')->id())
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Отметить все уведомления как прочитанные
     */
    public function markAllAsRead()
    {
        if (!Auth::guard('admin')->check()) {
            return false;
        }

        Notification::where('notifiable_type', Employee::class)
            ->where('notifiable_id', Auth::guard('admin')->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return true;
    }
}
