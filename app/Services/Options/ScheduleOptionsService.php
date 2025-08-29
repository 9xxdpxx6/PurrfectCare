<?php

namespace App\Services\Options;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Schedule::with('veterinarian');
        
        // Применяем поиск
        $search = $request->input('q');
        if ($search) {
            $searchTerms = array_filter(explode(' ', trim($search)));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $term = trim($term);
                    if (empty($term)) continue;
                    
                    $q->where(function($subQ) use ($term) {
                        // Поиск по имени ветеринара
                        $subQ->whereHas('veterinarian', function($vq) use ($term) {
                            $vq->where('name', 'like', '%' . $term . '%');
                        })
                        // Поиск по дате в формате d.m.Y
                        ->orWhereRaw("DATE_FORMAT(shift_starts_at, '%d.%m.%Y') LIKE ?", ['%' . $term . '%'])
                        // Поиск по дате в формате d.m
                        ->orWhereRaw("DATE_FORMAT(shift_starts_at, '%d.%m') LIKE ?", ['%' . $term . '%'])
                        // Поиск по дню недели
                        ->orWhere(function($dayQ) use ($term) {
                            $dayNumber = $this->getDayOfWeek($term);
                            if ($dayNumber !== null) {
                                $dayQ->whereRaw("DAYOFWEEK(shift_starts_at) = ?", [$dayNumber]);
                            }
                        })
                        // Поиск по времени
                        ->orWhereRaw("TIME_FORMAT(shift_starts_at, '%H:%i') LIKE ?", ['%' . $term . '%']);
                    });
                }
            });
        }
        
        // Сортируем по дате расписания (новые первыми)
        $query->orderBy('shift_starts_at', 'desc');
        
        return $this->buildOptions($request, $query, [
            'model' => Schedule::class
        ]);
    }

    protected function formatText($item, $config): string
    {
        $veterinarian = $item->veterinarian ? $item->veterinarian->name . ' - ' : '';
        return $veterinarian . 
               $item->shift_starts_at->format('d.m.Y H:i') . ' - ' . 
               $item->shift_ends_at->format('H:i');
    }
    
    /**
     * Преобразует название дня недели в числовое значение для MySQL DAYOFWEEK
     * DAYOFWEEK возвращает: 1=воскресенье, 2=понедельник, 3=вторник, 4=среда, 5=четверг, 6=пятница, 7=суббота
     */
    private function getDayOfWeek(string $term): ?int
    {
        $term = mb_strtolower(trim($term));
        
        $dayMapping = [
            'пн' => 2,
            'понедельник' => 2,
            'вт' => 3,
            'вторник' => 3,
            'ср' => 4,
            'среда' => 4,
            'чт' => 5,
            'четверг' => 5,
            'пт' => 6,
            'пятница' => 6,
            'сб' => 7,
            'суббота' => 7,
            'вс' => 1,
            'воскресенье' => 1,
            // Английские названия для совместимости
            'mon' => 2,
            'monday' => 2,
            'tue' => 3,
            'tuesday' => 3,
            'wed' => 4,
            'wednesday' => 4,
            'thu' => 5,
            'thursday' => 5,
            'fri' => 6,
            'friday' => 6,
            'sat' => 7,
            'saturday' => 7,
            'sun' => 1,
            'sunday' => 1
        ];
        
        return $dayMapping[$term] ?? null;
    }
} 