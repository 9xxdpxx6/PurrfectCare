<?php

namespace App\Http\Filters;

use App\Http\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class LabTestFilter extends AbstractFilter
{
    public const SEARCH = 'search';
    public const PET = 'pet';
    public const VETERINARIAN = 'veterinarian';
    public const LAB_TEST_TYPE = 'lab_test_type';
    public const RECEIVED_AT_FROM = 'received_at_from';
    public const RECEIVED_AT_TO = 'received_at_to';
    public const COMPLETED_AT_FROM = 'completed_at_from';
    public const COMPLETED_AT_TO = 'completed_at_to';
    public const SORT = 'sort';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::PET => [$this, 'pet'],
            self::VETERINARIAN => [$this, 'veterinarian'],
            self::LAB_TEST_TYPE => [$this, 'labTestType'],
            self::RECEIVED_AT_FROM => [$this, 'receivedAtFrom'],
            self::RECEIVED_AT_TO => [$this, 'receivedAtTo'],
            self::COMPLETED_AT_FROM => [$this, 'completedAtFrom'],
            self::COMPLETED_AT_TO => [$this, 'completedAtTo'],
            self::SORT => [$this, 'sort'],
        ];
    }

    public function search(Builder $builder, $value)
    {
        // Разбиваем поисковый запрос на слова
        $words = array_filter(explode(' ', trim($value)));
        
        if (empty($words)) {
            return $builder;
        }
        
        return $builder->where(function ($query) use ($words) {
            foreach ($words as $word) {
                $query->where(function ($q) use ($word) {
                    $q->whereHas('pet', function ($q2) use ($word) {
                        $q2->where('name', 'like', "%{$word}%");
                    })
                    ->orWhereHas('pet.client', function ($q2) use ($word) {
                        $q2->where('name', 'like', "%{$word}%");
                    })
                    ->orWhereHas('veterinarian', function ($q2) use ($word) {
                        $q2->where('name', 'like', "%{$word}%");
                    })
                    ->orWhereHas('labTestType', function ($q2) use ($word) {
                        $q2->where('name', 'like', "%{$word}%");
                    });
                });
            }
        });
    }

    public function pet(Builder $builder, $value)
    {
        return $builder->where('pet_id', $value);
    }

    public function veterinarian(Builder $builder, $value)
    {
        return $builder->where('veterinarian_id', $value);
    }

    public function labTestType(Builder $builder, $value)
    {
        return $builder->where('lab_test_type_id', $value);
    }

    public function receivedAtFrom(Builder $builder, $value)
    {
        return $builder->where('received_at', '>=', $value);
    }

    public function receivedAtTo(Builder $builder, $value)
    {
        return $builder->where('received_at', '<=', $value);
    }

    public function completedAtFrom(Builder $builder, $value)
    {
        return $builder->where('completed_at', '>=', $value);
    }

    public function completedAtTo(Builder $builder, $value)
    {
        return $builder->where('completed_at', '<=', $value);
    }

    public function sort(Builder $builder, $value)
    {
        switch ($value) {
            case 'received_at_desc':
                return $builder->orderBy('received_at', 'desc');
            case 'received_at_asc':
                return $builder->orderBy('received_at', 'asc');
            case 'completed_at_desc':
                return $builder->orderBy('completed_at', 'desc');
            case 'completed_at_asc':
                return $builder->orderBy('completed_at', 'asc');
            case 'pet_name_asc':
                return $builder->join('pets', 'lab_tests.pet_id', '=', 'pets.id')
                    ->orderBy('pets.name', 'asc')
                    ->select('lab_tests.*');
            case 'pet_name_desc':
                return $builder->join('pets', 'lab_tests.pet_id', '=', 'pets.id')
                    ->orderBy('pets.name', 'desc')
                    ->select('lab_tests.*');
            default:
                return $builder->orderBy('id', 'desc');
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