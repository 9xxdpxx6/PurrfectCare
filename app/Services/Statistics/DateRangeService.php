<?php

namespace App\Services\Statistics;

use App\Models\Visit;
use App\Models\Order;
use App\Models\User;
use App\Models\Pet;
use Carbon\Carbon;

class DateRangeService
{
    /**
     * Получить начальную дату для выбранного периода
     */
    public function getStartDate($period)
    {
        return match($period) {
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'quarter' => Carbon::now()->subQuarter(),
            'year' => Carbon::now()->subYear(),
            'all' => $this->getEarliestDataDate(),
            default => Carbon::now()->subMonth(),
        };
    }

    /**
     * Получить самую раннюю дату из всех основных таблиц
     */
    public function getEarliestDataDate()
    {
        // Получаем самую раннюю дату из всех основных таблиц
        $dates = [];
        
        // Заказы
        // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
        $earliestOrder = Order::select(['created_at'])->orderBy('created_at')->first();
        if ($earliestOrder) {
            $dates[] = $earliestOrder->created_at;
        }
        
        // Приемы
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        $earliestVisit = Visit::select(['starts_at'])->orderBy('starts_at')->first();
        if ($earliestVisit) {
            $dates[] = $earliestVisit->starts_at;
        }
        
        // Клиенты
        // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
        $earliestUser = User::select(['created_at'])->orderBy('created_at')->first();
        if ($earliestUser) {
            $dates[] = $earliestUser->created_at;
        }
        
        // Питомцы
        // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
        $earliestPet = Pet::select(['created_at'])->orderBy('created_at')->first();
        if ($earliestPet) {
            $dates[] = $earliestPet->created_at;
        }
        
        // Если есть данные, возвращаем самую раннюю дату
        if (!empty($dates)) {
            return min($dates);
        }
        
        // Если данных нет, возвращаем дату 3 года назад
        return Carbon::now()->subYears(3);
    }

    /**
     * Обработать входящие даты и вернуть корректный диапазон
     */
    public function processDateRange($period, $startDateInput = null, $endDateInput = null)
    {
        if ($period === 'custom' && $startDateInput && $endDateInput) {
            try {
                $startDate = Carbon::createFromFormat('d.m.Y', $startDateInput)->startOfDay();
                $endDate = Carbon::createFromFormat('d.m.Y', $endDateInput)->endOfDay();
            } catch (\Throwable $e) {
                $startDate = $this->getStartDate('month');
                $endDate = Carbon::now();
            }
        } else {
            $startDate = $this->getStartDate($period);
            $endDate = Carbon::now();
        }

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dateRange' => $startDate->format('d.m.Y') . ' — ' . $endDate->format('d.m.Y')
        ];
    }
}
