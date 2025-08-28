<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class ScheduleFilter extends AbstractFilter
{
    const SEARCH = 'search';
    const VETERINARIAN = 'veterinarian';
    const BRANCH = 'branch';
    const DATE_FROM = 'date_from';
    const DATE_TO = 'date_to';
    const SORT = 'sort';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::VETERINARIAN => [$this, 'veterinarian'],
            self::BRANCH => [$this, 'branch'],
            self::DATE_FROM => [$this, 'dateFrom'],
            self::DATE_TO => [$this, 'dateTo'],
            self::SORT => [$this, 'sort'],
        ];
    }

    protected function search(Builder $builder, $value)
    {
        if (empty(trim($value))) {
            return $builder;
        }
        
        $term = trim($value);
        
        // Debug logging
        \Log::info('ScheduleFilter search called with term: ' . $term);
        
        // Маппинг русских названий дней недели для поиска
        // DAYOFWEEK() возвращает 1=воскресенье, 2=понедельник, 3=вторник, 4=среда, 5=четверг, 6=пятница, 7=суббота
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
            'воскресенье' => 1
        ];
        
        $builder->where(function ($query) use ($term, $dayMapping) {
            // Поиск по имени ветеринара
            $query->whereHas('veterinarian', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%");
            })
            // Поиск по названию филиала
            ->orWhereHas('branch', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%");
            })
            // Поиск по дню недели
            ->orWhere(function ($dayQuery) use ($term, $dayMapping) {
                $lowerTerm = mb_strtolower($term);
                
                // Если это известный день недели, ищем по номеру дня
                if (isset($dayMapping[$lowerTerm])) {
                    $dayNumber = $dayMapping[$lowerTerm];
                    $dayQuery->whereRaw("DAYOFWEEK(shift_starts_at) = ?", [$dayNumber]);
                    \Log::info('Searching by day number: ' . $dayNumber . ' for term: ' . $lowerTerm);
                } else {
                    // Иначе пробуем искать по названию дня (fallback)
                    $dayQuery->whereRaw("LOWER(DAYNAME(shift_starts_at)) LIKE ?", ["%" . $lowerTerm . "%"]);
                    \Log::info('Searching by day name fallback for term: ' . $lowerTerm);
                }
            });
        });
        
        \Log::info('ScheduleFilter search query built');
        return $builder;
    }

    protected function veterinarian(Builder $builder, $value)
    {
        $builder->where('veterinarian_id', $value);
    }

    protected function branch(Builder $builder, $value)
    {
        $builder->where('branch_id', $value);
    }

    protected function dateFrom(Builder $builder, $value)
    {
        try {
            $date = \Carbon\Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            $date = $value; // fallback, если вдруг формат уже верный
        }
        $builder->whereDate('shift_starts_at', '>=', $date);
    }

    protected function dateTo(Builder $builder, $value)
    {
        try {
            $date = \Carbon\Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            $date = $value;
        }
        $builder->whereDate('shift_starts_at', '<=', $date);
    }

    protected function sort(Builder $builder, $value)
    {
        switch ($value) {
            case 'date_asc':
                $builder->orderBy('schedules.shift_starts_at');
                break;
            case 'date_desc':
                $builder->orderBy('schedules.shift_starts_at', 'desc');
                break;
            case 'veterinarian_asc':
                $builder->join('employees', 'schedules.veterinarian_id', '=', 'employees.id')
                        ->orderBy('employees.name');
                break;
            case 'veterinarian_desc':
                $builder->join('employees', 'schedules.veterinarian_id', '=', 'employees.id')
                        ->orderBy('employees.name', 'desc');
                break;
            case 'branch_asc':
                $builder->join('branches', 'schedules.branch_id', '=', 'branches.id')
                        ->orderBy('branches.name');
                break;
            case 'branch_desc':
                $builder->join('branches', 'schedules.branch_id', '=', 'branches.id')
                        ->orderBy('branches.name', 'desc');
                break;
            default:
                $builder->orderBy('schedules.id', 'desc');
                break;
        }
    }

    public function apply(Builder $builder)
    {
        parent::apply($builder);
        // Если сортировка не указана, сортируем по дате DESC
        if (!isset($this->queryParams['sort']) || !$this->queryParams['sort']) {
            $builder->orderByDesc('schedules.id');
        }
    }
} 