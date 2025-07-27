<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class VaccinationFilter extends AbstractFilter
{
    public const SEARCH = 'search';
    public const PET = 'pet';
    public const VETERINARIAN = 'veterinarian';
    public const DATE_FROM = 'date_from';
    public const DATE_TO = 'date_to';
    public const NEXT_DUE_FROM = 'next_due_from';
    public const NEXT_DUE_TO = 'next_due_to';
    public const SORT = 'sort';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::PET => [$this, 'pet'],
            self::VETERINARIAN => [$this, 'veterinarian'],
            self::DATE_FROM => [$this, 'dateFrom'],
            self::DATE_TO => [$this, 'dateTo'],
            self::NEXT_DUE_FROM => [$this, 'nextDueFrom'],
            self::NEXT_DUE_TO => [$this, 'nextDueTo'],
            self::SORT => [$this, 'sort'],
        ];
    }

    public function search(Builder $builder, $value)
    {
        $builder->where(function ($query) use ($value) {
            // Разбиваем поисковый запрос на слова
            $words = array_filter(explode(' ', trim($value)));
            
            if (empty($words)) {
                return $query;
            }
            
            // Ищем вакцинации, где каждое слово найдено в любом из полей
            $query->where(function ($subQuery) use ($words) {
                foreach ($words as $word) {
                    $subQuery->where(function ($wordQuery) use ($word) {
                        $wordQuery->whereHas('pet', function ($q) use ($word) {
                            $q->where('name', 'like', "%{$word}%");
                        })
                        ->orWhereHas('veterinarian', function ($q) use ($word) {
                            $q->where('name', 'like', "%{$word}%");
                        })
                        ->orWhereHas('drugs', function ($q) use ($word) {
                            $q->where('name', 'like', "%{$word}%");
                        });
                    });
                }
            });
        });
    }

    public function pet(Builder $builder, $value)
    {
        $builder->where('pet_id', $value);
    }

    public function veterinarian(Builder $builder, $value)
    {
        $builder->where('veterinarian_id', $value);
    }

    public function dateFrom(Builder $builder, $value)
    {
        $builder->whereDate('administered_at', '>=', $value);
    }

    public function dateTo(Builder $builder, $value)
    {
        $builder->whereDate('administered_at', '<=', $value);
    }

    public function nextDueFrom(Builder $builder, $value)
    {
        $builder->whereDate('next_due', '>=', $value);
    }

    public function nextDueTo(Builder $builder, $value)
    {
        $builder->whereDate('next_due', '<=', $value);
    }

    public function sort(Builder $builder, $value)
    {
        switch ($value) {
            case 'date_asc':
                $builder->orderBy('administered_at', 'asc');
                break;
            case 'date_desc':
                $builder->orderBy('administered_at', 'desc');
                break;
            case 'next_due_asc':
                $builder->orderBy('next_due', 'asc');
                break;
            case 'next_due_desc':
                $builder->orderBy('next_due', 'desc');
                break;
            case 'pet_asc':
                $builder->join('pets', 'vaccinations.pet_id', '=', 'pets.id')
                    ->orderBy('pets.name', 'asc')
                    ->select('vaccinations.*');
                break;
            case 'pet_desc':
                $builder->join('pets', 'vaccinations.pet_id', '=', 'pets.id')
                    ->orderBy('pets.name', 'desc')
                    ->select('vaccinations.*');
                break;
            case 'veterinarian_asc':
                $builder->join('employees', 'vaccinations.veterinarian_id', '=', 'employees.id')
                    ->orderBy('employees.name', 'asc')
                    ->select('vaccinations.*');
                break;
            case 'veterinarian_desc':
                $builder->join('employees', 'vaccinations.veterinarian_id', '=', 'employees.id')
                    ->orderBy('employees.name', 'desc')
                    ->select('vaccinations.*');
                break;
            default:
                $builder->orderBy('id', 'desc');
                break;
        }
    }

    public function apply(Builder $builder)
    {
        parent::apply($builder);
        // Если сортировка не указана, сортируем по ID DESC
        if (!isset($this->queryParams['sort']) || !$this->queryParams['sort']) {
            $builder->orderByDesc('id');
        }
    }
} 