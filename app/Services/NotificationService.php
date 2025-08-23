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
            // Оптимизация: используем select для выбора только нужных полей
            $employees = Employee::select(['id', 'name', 'email'])->get();
            
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

        // Оптимизация: используем индексы на notifiable_type, notifiable_id и read_at
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

        // Оптимизация: используем индексы и select для выбора нужных полей
        return Notification::select(['id', 'type', 'data', 'read_at', 'created_at'])
            ->where('notifiable_type', Employee::class)
            ->where('notifiable_id', Auth::guard('admin')->id())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить все уведомления для текущего сотрудника с пагинацией
     */
    public function getAllNotifications($perPage = 25, $filters = [])
    {
        if (!Auth::guard('admin')->check()) {
            return collect();
        }

        // Оптимизация: используем индексы на notifiable_type, notifiable_id и select для выбора нужных полей
        $query = Notification::select(['id', 'type', 'data', 'read_at', 'created_at'])
            ->where('notifiable_type', Employee::class)
            ->where('notifiable_id', Auth::guard('admin')->id());

        // Применяем фильтры если есть
        if (!empty($filters)) {
            if (isset($filters['status'])) {
                if ($filters['status'] === 'unread') {
                    // Оптимизация: используем индекс на read_at
                    $query->whereNull('read_at');
                } elseif ($filters['status'] === 'read') {
                    // Оптимизация: используем индекс на read_at
                    $query->whereNotNull('read_at');
                }
            }

            if (isset($filters['type']) && $filters['type']) {
                // Оптимизация: используем индекс на type
                $query->where('type', $filters['type']);
            }

            if (isset($filters['dateFrom']) && $filters['dateFrom']) {
                // Добавляем время 00:00:00 к дате "от"
                $dateFrom = $filters['dateFrom'] . ' 00:00:00';
                $query->where('created_at', '>=', $dateFrom);
            }

            if (isset($filters['dateTo']) && $filters['dateTo']) {
                // Добавляем время 23:59:59 к дате "до"
                $dateTo = $filters['dateTo'] . ' 23:59:59';
                $query->where('created_at', '<=', $dateTo);
            }

            if (isset($filters['sort'])) {
                switch ($filters['sort']) {
                    case 'created_asc':
                        $query->orderBy('created_at', 'asc');
                        break;
                    case 'read_asc':
                        // Оптимизация: используем индекс на read_at
                        $query->orderBy('read_at', 'asc');
                        break;
                    case 'read_desc':
                        // Оптимизация: используем индекс на read_at
                        $query->orderBy('read_at', 'desc');
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                        break;
                }
            }
        } else {
            // По умолчанию сортируем по дате создания (используем индекс на created_at)
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Отметить уведомление как прочитанное
     */
    public function markAsRead($notificationId)
    {
        if (!Auth::guard('admin')->check()) {
            return false;
        }

        // Оптимизация: используем индексы на id, notifiable_type, notifiable_id и select для выбора нужных полей
        $notification = Notification::select(['id', 'read_at'])
            ->where('id', $notificationId)
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

        // Оптимизация: используем индексы на notifiable_type, notifiable_id и read_at
        Notification::where('notifiable_type', Employee::class)
            ->where('notifiable_id', Auth::guard('admin')->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return true;
    }
}
