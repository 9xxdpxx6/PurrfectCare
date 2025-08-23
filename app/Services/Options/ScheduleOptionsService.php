<?php

namespace App\Services\Options;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Schedule::with('veterinarian');
        
        // Применяем поиск по имени ветеринара
        $search = $request->input('q');
        if ($search) {
            $query->whereHas('veterinarian', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
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
} 