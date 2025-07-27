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
        // Разбиваем поисковый запрос на слова
        $words = array_filter(explode(' ', trim($value)));
        
        if (empty($words)) {
            return $builder;
        }
        
        // Маппинг русских названий дней недели на английские
        $dayMapping = [
            'пн' => 'monday',
            'понедельник' => 'monday',
            'вт' => 'tuesday', 
            'вторник' => 'tuesday',
            'ср' => 'wednesday',
            'среда' => 'wednesday',
            'чт' => 'thursday',
            'четверг' => 'thursday',
            'пт' => 'friday',
            'пятница' => 'friday',
            'сб' => 'saturday',
            'суббота' => 'saturday',
            'вс' => 'sunday',
            'воскресенье' => 'sunday'
        ];
        
        $builder->where(function ($query) use ($words, $dayMapping) {
            foreach ($words as $word) {
                $query->where(function ($q) use ($word, $dayMapping) {
                    $q->whereHas('veterinarian', function ($q2) use ($word) {
                        $q2->where('name', 'like', "%{$word}%")
                           ->orWhereHas('specialties', function ($q3) use ($word) {
                               $q3->where('name', 'like', "%{$word}%");
                           });
                    })
                    ->orWhereHas('branch', function ($q2) use ($word) {
                        $q2->where('name', 'like', "%{$word}%")
                           ->orWhere('address', 'like', "%{$word}%");
                    })
                    ->orWhere(function ($dayQuery) use ($word, $dayMapping) {
                        // Проверяем русские названия дней недели
                        if (isset($dayMapping[strtolower($word)])) {
                            $dayQuery->whereRaw("DAYNAME(shift_starts_at) = ?", [$dayMapping[strtolower($word)]]);
                        } else {
                            // Если не русское название, ищем как есть (для английских названий)
                            $dayQuery->whereRaw("DAYNAME(shift_starts_at) LIKE ?", ["%{$word}%"]);
                        }
                    });
                });
            }
        });
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
                $builder->orderBy('shift_starts_at');
                break;
            case 'date_desc':
                $builder->orderBy('shift_starts_at', 'desc');
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
                $builder->orderBy('id', 'desc');
                break;
        }
    }

    public function apply(Builder $builder)
    {
        parent::apply($builder);
        // Если сортировка не указана, сортируем по дате DESC
        if (!isset($this->queryParams['sort']) || !$this->queryParams['sort']) {
            $builder->orderByDesc('id');
        }
    }
} 