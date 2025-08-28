<?php

namespace App\Services\Bot;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Visit;
use App\Models\Status;
use App\Models\TelegramProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Pet;
use App\Models\User;
use App\Services\NotificationService;

class AppointmentService
{
    private int $currentScheduleId = 0;

    public function __construct(
        private TelegramApiService $apiService,
        private NotificationService $notificationService
    ) {
    }

    public function sendBranches(string $chatId): array
    {
        Log::info('AppointmentService: sending branches');
        
        // Убираем проверку is_active - такой колонки нет в таблице
        $branches = Branch::select('id', 'name')
            ->orderBy('name')
            ->get();
        
        if ($branches->isEmpty()) {
            $keyboard = [
                [
                    ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                ]
            ];
            
            return [
                'action' => 'send_message',
                'message' => '❌ В системе нет доступных филиалов.',
                'keyboard' => $keyboard
            ];
        }

        $keyboard = [];
        foreach ($branches as $branch) {
            $keyboard[] = [[
                'text' => $this->cleanUtf8($branch->name),
                'callback_data' => "branch:{$branch->id}"
            ]];
        }

        // Добавляем кнопку главного меню
        $keyboard[] = [
            ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
        ];

        return [
            'action' => 'send_message',
            'message' => 'Выберите филиал:',
            'keyboard' => $keyboard
        ];
    }

    public function sendVeterinarians(string $chatId, int $branchId, int $page = 1): array
    {
        Log::info('AppointmentService: sending veterinarians', ['branch_id' => $branchId, 'page' => $page]);

        // Упрощаем запрос - сначала получаем ID сотрудников филиала
        $employeeIds = \DB::table('branch_employee')
            ->where('branch_id', $branchId)
            ->pluck('employee_id');

        if ($employeeIds->isEmpty()) {
            return [
                'action' => 'send_message',
                'message' => '❌ В этом филиале нет сотрудников.',
                'keyboard' => []
            ];
        }

        // Получаем название филиала для отображения
        $branch = Branch::select('name')->find($branchId);
        $branchName = $branch ? $this->cleanUtf8($branch->name) : 'Филиал';

        // Теперь получаем ветеринаров с их специальностями
        $veterinarians = Employee::select('employees.id', 'employees.name')
            ->whereIn('employees.id', $employeeIds)
            ->whereHas('specialties', function ($query) {
                $query->where('is_veterinarian', true);
            })
            ->with(['specialties' => function ($query) {
                $query->select('specialties.id', 'specialties.name', 'specialties.is_veterinarian')
                    ->where('specialties.is_veterinarian', true);
            }])
            ->orderBy('employees.name')
            ->get();

        if ($veterinarians->isEmpty()) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'back_to_branches'],
                    ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                ]
            ];
            
            return [
                'action' => 'send_message',
                'message' => "❌ В филиале '{$branchName}' нет ветеринаров.",
                'keyboard' => $keyboard
            ];
        }

        // Пагинация: максимум 20 ветеринаров на страницу
        $perPage = 20;
        $total = $veterinarians->count();
        $totalPages = ceil($total / $perPage);
        $currentPage = max(1, min($page, $totalPages));
        $offset = ($currentPage - 1) * $perPage;
        
        $pageVeterinarians = $veterinarians->slice($offset, $perPage);

        $keyboard = [];
        foreach ($pageVeterinarians as $veterinarian) {
            $specialties = $veterinarian->specialties->pluck('name')->toArray();
            $specialtyText = implode(', ', $specialties);
            
            // Очищаем и нормализуем UTF-8 символы
            $veterinarianName = $this->cleanUtf8($veterinarian->name);
            $specialtyText = $this->cleanUtf8($specialtyText);
            
            // Обрезаем длинные названия специальностей
            if (mb_strlen($specialtyText) > 80) {
                $specialtyText = mb_substr($specialtyText, 0, 77) . '...';
            }
            
            $keyboard[] = [[
                'text' => "{$veterinarianName} ({$specialtyText})",
                'callback_data' => "vet:{$veterinarian->id}:{$branchId}"
            ]];
        }

        // Добавляем кнопки пагинации если есть несколько страниц
        if ($totalPages > 1) {
            $paginationRow = [];
            
            if ($currentPage > 1) {
                $paginationRow[] = ['text' => '⬅️ Пред.', 'callback_data' => "vets_page:{$branchId}:" . ($currentPage - 1)];
            }
            
            $paginationRow[] = ['text' => "{$currentPage}/{$totalPages}", 'callback_data' => 'page_info'];
            
            if ($currentPage < $totalPages) {
                $paginationRow[] = ['text' => 'След. ➡️', 'callback_data' => "vets_page:{$branchId}:" . ($currentPage + 1)];
            }
            
            $keyboard[] = $paginationRow;
        }

        // Добавляем кнопки навигации
        $keyboard[] = [
            ['text' => '⬅️ Назад', 'callback_data' => 'back_to_branches'],
            ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
        ];

        $message = $this->cleanUtf8("{$branchName}. Выберите ветеринара:");
        if ($totalPages > 1) {
            $message .= "\n\n📄 Страница {$currentPage} из {$totalPages} (всего: {$total})";
        }

        return [
            'action' => 'send_message',
            'message' => $message,
            'keyboard' => $keyboard
        ];
    }

    public function sendDates(string $chatId, int $employeeId, int $branchId): array
    {
        Log::info('AppointmentService: sending dates', ['employee_id' => $employeeId, 'branch_id' => $branchId]);
        
        // Используем правильное поле veterinarian_id из таблицы schedules
        $schedules = Schedule::select('id', 'shift_starts_at')
            ->where('veterinarian_id', $employeeId)
            ->where('branch_id', $branchId) // Добавляем фильтр по филиалу
            ->where('shift_starts_at', '>=', now())
            ->where('shift_starts_at', '<=', now()->addDays(30))
            ->orderBy('shift_starts_at')
            ->get()
            ->groupBy(function ($schedule) {
                return $schedule->shift_starts_at->format('Y-m-d');
            });
        
        if ($schedules->isEmpty()) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'back_to_veterinarians'],
                    ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                ]
            ];
            
            return [
                'action' => 'send_message',
                'message' => '❌ У ветеринара нет доступных дней в ближайшие 30 дней.',
                'keyboard' => $keyboard
            ];
        }

        $employee = Employee::select('name')->find($employeeId);
        $employeeName = $employee ? $employee->name : 'Ветеринар';

        $keyboard = [];
        foreach ($schedules->keys() as $date) {
            $carbonDate = Carbon::parse($date)->locale('ru');
            $formattedDate = $carbonDate->translatedFormat('d F Y');
            $dayOfWeek = $carbonDate->translatedFormat('l');
            
            $keyboard[] = [[
                'text' => "📅 {$formattedDate} ({$dayOfWeek})",
                'callback_data' => "date:{$employeeId}:{$branchId}:{$date}"
            ]];
        }

        // Добавляем кнопки навигации
        $keyboard[] = [
            ['text' => '⬅️ Назад', 'callback_data' => 'back_to_veterinarians'],
            ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
        ];

        return [
            'action' => 'send_message',
            'message' => "Выберите дату для записи к {$employeeName}:",
            'keyboard' => $keyboard
        ];
    }

    public function sendTimeSlots(string $chatId, int $employeeId, int $branchId, string $dateYmd): array
    {
        Log::info('AppointmentService: sending time slots', [
            'chat_id' => $chatId,
            'employee_id' => $employeeId,
            'branch_id' => $branchId,
            'date' => $dateYmd
        ]);

        // Получаем расписание для выбранного ветеринара на указанную дату
        $schedules = Schedule::where('veterinarian_id', $employeeId)
            ->where('shift_starts_at', '>=', $dateYmd . ' 00:00:00')
            ->where('shift_starts_at', '<', $dateYmd . ' 23:59:59')
            ->get();

        Log::info('AppointmentService: found schedules', [
            'count' => $schedules->count(),
            'schedules' => $schedules->toArray(),
            'query' => 'SELECT * FROM schedules WHERE veterinarian_id = ' . $employeeId . ' AND shift_starts_at >= "' . $dateYmd . ' 00:00:00" AND shift_starts_at < "' . $dateYmd . ' 23:59:59"'
        ]);

        $veterinarian = Employee::find($employeeId);
        $veterinarianName = $veterinarian ? $veterinarian->name : 'Ветеринар';
        
        if ($schedules->isEmpty()) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'back_to_veterinarians'],
                    ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                ]
            ];
            
            return [
                'action' => 'send_message',
                'message' => "На эту дату у ветеринарного врача {$veterinarianName} расписание отсутствует.",
                'keyboard' => $keyboard
            ];
        }

        $messages = [];
        $allAvailableSlots = [];

        // Оптимизация: грузим все приёмы по всем расписаниям за этот день одним запросом
        $scheduleIds = $schedules->pluck('id')->all();
        $dayStart = $dateYmd . ' 00:00:00';
        $dayEnd = $dateYmd . ' 23:59:59';
        $visits = Visit::select('schedule_id', 'starts_at')
            ->whereIn('schedule_id', $scheduleIds)
            ->whereBetween('starts_at', [$dayStart, $dayEnd])
            ->get();
        $busy = [];
        foreach ($visits as $v) {
            $busy[$v->schedule_id . '|' . Carbon::parse($v->starts_at)->format('Y-m-d H:i:s')] = true;
        }
        
        foreach ($schedules as $schedule) {
            // Генерируем временные слоты с 9:00 до 18:00 с интервалом 30 минут
            $startTime = Carbon::parse($schedule->shift_starts_at)->setTime(9, 0);
            $endTime = Carbon::parse($schedule->shift_starts_at)->setTime(18, 0);
            
            $currentTime = $startTime->copy();
            
            while ($currentTime < $endTime) {
                $key = $schedule->id . '|' . $currentTime->format('Y-m-d H:i:s');
                if (!isset($busy[$key])) {
                    $allAvailableSlots[] = [
                        'time' => $currentTime->format('H:i'),
                        'datetime' => $currentTime->format('Y-m-d H:i:s'),
                        'schedule_id' => $schedule->id
                    ];
                }
                $currentTime->addMinutes(30);
            }
        }
        
        if (empty($allAvailableSlots)) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'back_to_veterinarians'],
                    ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                ]
            ];
            
            return [
                'action' => 'send_message',
                'message' => "На эту дату у ветеринарного врача {$veterinarianName} нет свободных слотов.",
                'keyboard' => $keyboard
            ];
        }
        
        // Убираем дублирующиеся временные слоты
        $uniqueSlots = [];
        foreach ($allAvailableSlots as $slot) {
            $timeKey = $slot['time'];
            if (!isset($uniqueSlots[$timeKey])) {
                $uniqueSlots[$timeKey] = $slot;
            }
        }
        
        // Сортируем по времени
        ksort($uniqueSlots);
        $uniqueSlots = array_values($uniqueSlots);

        $keyboard = [];
        $row = [];
        foreach ($uniqueSlots as $slot) {
            $row[] = [
                'text' => $slot['time'],
                'callback_data' => 'time:'.$slot['schedule_id'].':'.$slot['datetime'],
            ];
            if (count($row) === 3) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        if (!empty($row)) {
            $keyboard[] = $row;
        }

        // Форматируем дату на русском языке
        $dateFormatted = Carbon::parse($dateYmd)->locale('ru')->translatedFormat('d F');
        $messages[] = [
            'message' => "Свободные слоты на {$dateFormatted} у ветеринарного врача {$veterinarianName}:",
            'keyboard' => $keyboard
        ];

        // Добавляем кнопки навигации к последнему сообщению
        if (!empty($messages)) {
            $lastIndex = count($messages) - 1;
            $messages[$lastIndex]['keyboard'][] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'back_to_veterinarians'],
                ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
            ];
        }

        Log::info('AppointmentService: time slots result', [
            'messages_count' => count($messages),
            'action' => 'send_multiple_messages'
        ]);

        return [
            'action' => 'send_multiple_messages',
            'messages' => $messages
        ];
    }

    public function tryBookSlot(string $chatId, int $scheduleId, string $startsAt, TelegramProfile $profile): array
    {
        if (!$profile->user_id) {
            $keyboard = [
                [
                    ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                ]
            ];
            
            return [
                'action' => 'send_message',
                'message' => 'Для записи необходимо завершить регистрацию.',
                'keyboard' => $keyboard
            ];
        }

        $date = Carbon::parse($startsAt)->toDateString();

        // Ограничение: не более 4 интервалов в день
        $existingCount = Visit::where('client_id', $profile->user_id)
            ->whereDate('starts_at', $date)
            ->count();
        if ($existingCount >= 4) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'back_to_veterinarians'],
                    ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                ]
            ];
            
            return [
                'action' => 'send_message',
                'message' => 'Вы уже забронировали максимум 4 интервала на этот день.',
                'keyboard' => $keyboard
            ];
        }

        try {
            DB::beginTransaction();
            
            // Проверяем, что слот еще свободен
            $schedule = Schedule::lockForUpdate()->find($scheduleId);
            if (!$schedule) {
                throw new \Exception('Расписание не найдено');
            }

            // Сохраняем ID расписания для использования в сообщении
            $this->currentScheduleId = $scheduleId;

            // Проверяем, что время еще доступно
            $existingVisit = Visit::where('schedule_id', $scheduleId)
                ->where('starts_at', $startsAt)
                ->first();

            if ($existingVisit) {
                throw new \Exception('Этот временной слот уже занят');
            }

            // Получаем pet_id из профиля пользователя (может быть null)
            $petId = $profile->data['selected_pet_id'] ?? null;
            
            // Сохраняем имя питомца до очистки профиля
            $petName = '';
            if ($petId) {
                $pet = Pet::where('id', $petId)
                    ->where('client_id', $profile->user_id)
                    ->first();
                
                if (!$pet) {
                    throw new \Exception('Питомец не найден или не принадлежит вам');
                }
                $petName = $pet->name;
            }

            // Создаем приём (pet_id может быть null)
            $visit = Visit::create([
                'client_id' => $profile->user_id,
                'pet_id' => $petId, // Может быть null
                'schedule_id' => $scheduleId,
                'starts_at' => $startsAt,
                'status_id' => 1, // Статус "Запланирован"
                'complaints' => null,
                'notes' => null,
            ]);

            // Отправляем уведомление администраторам о новой записи через бота
            try {
                $this->notificationService->notifyAboutBotBooking($visit);
            } catch (\Exception $e) {
                Log::error('Failed to send notification about bot booking', [
                    'visit_id' => $visit->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Сбрасываем выбранного питомца и другие временные данные после успешного создания записи
            $data = $profile->data ?? [];
            foreach ([
                'pet_name',
                'selected_gender',
                'selected_pet_id',
                'selected_breed_id',
                'selected_species_id'
            ] as $key) {
                if (array_key_exists($key, $data)) {
                    unset($data[$key]);
                }
            }
            $profile->data = $data;
            $profile->save();

            DB::commit();

            return $this->formatSuccessMessage($profile, $startsAt, $petName);
        } catch (\Throwable $e) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'back_to_veterinarians'],
                    ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                ]
            ];
            
            return [
                'action' => 'send_message',
                'message' => 'Не удалось записаться: '.($e->getMessage() ?: 'интервал занят'),
                'keyboard' => $keyboard
            ];
        }
    }

    protected function formatSuccessMessage(TelegramProfile $profile, string $startsAt, string $petName): array
    {
        $user = User::find($profile->user_id);
        $userName = $user ? $user->name : 'Клиент';
        
        // Получаем pet_id из созданного приёма
        $visit = Visit::where('client_id', $profile->user_id)
            ->where('starts_at', $startsAt)
            ->first();
        
        $petNameInMessage = $petName ?: 'Питомец';

        $date = Carbon::parse($startsAt)->locale('ru');
        $endTime = $date->copy()->addMinutes(30);
        
        $message = "✅ <b>Запись успешно создана!</b>\n\n";
        $message .= "👤 <b>Клиент:</b> {$userName}\n";
        
        // Питомец отображается только если есть реальное имя
        if ($petName && $petName !== 'Питомец') {
            $message .= "🐾 <b>Питомец:</b> {$petName}\n";
        }
        
        $message .= "👨‍⚕️ <b>Врач:</b> {$this->getVeterinarianName()}\n";
        $message .= "🏥 <b>Филиал:</b> {$this->getBranchName()}\n";
        $message .= "📅 <b>Дата:</b> " . $date->translatedFormat('d F Y, l') . "\n";
        $message .= "🕐 <b>Время:</b> " . $date->format('H:i') . " - " . $endTime->format('H:i') . "\n";
        $message .= "⏱ <b>Длительность:</b> 30 минут";

        return [
            'action' => 'send_multiple_messages',
            'messages' => [
                [
                    'message' => $message,
                    'keyboard' => [] // Талон без кнопок
                ],
                [
                    'message' => 'Выберите действие:',
                    'keyboard' => [
                        [
                            ['text' => '📅 Записаться еще', 'callback_data' => 'book_appointment'],
                            ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                        ]
                    ]
                ]
            ]
        ];
    }

    private function getVeterinarianName(): string
    {
        $schedule = Schedule::find($this->currentScheduleId);
        if ($schedule && $schedule->veterinarian) {
            return $schedule->veterinarian->name;
        }
        return 'Врач';
    }

    private function getBranchName(): string
    {
        $schedule = Schedule::find($this->currentScheduleId);
        if ($schedule && $schedule->branch) {
            return $schedule->branch->name;
        }
        return 'Филиал';
    }

    /**
     * Очищает и нормализует UTF-8 строку, удаляя поврежденные символы
     */
    private function cleanUtf8(string $text): string
    {
        // Удаляем поврежденные UTF-8 символы
        $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        
        // Если iconv не сработал, используем mb_convert_encoding
        if ($clean === false) {
            $clean = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        
        // Удаляем невидимые символы и лишние пробелы
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $clean);
        $clean = trim($clean);
        
        return $clean ?: 'Неизвестно';
    }
}
