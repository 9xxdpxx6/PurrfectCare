<?php

namespace App\Http\Filters;

use App\Http\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class OrderFilter extends AbstractFilter
{
    public const SEARCH = 'search';
    public const CLIENT = 'client';
    public const PET = 'pet';
    public const STATUS = 'status';
    public const BRANCH = 'branch';

    public const CREATED_AT_FROM = 'created_at_from';
    public const CREATED_AT_TO = 'created_at_to';
    public const TOTAL_FROM = 'total_from';
    public const TOTAL_TO = 'total_to';
    public const SORT = 'sort';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::CLIENT => [$this, 'client'],
            self::PET => [$this, 'pet'],
            self::STATUS => [$this, 'status'],
            self::BRANCH => [$this, 'branch'],

            self::CREATED_AT_FROM => [$this, 'createdAtFrom'],
            self::CREATED_AT_TO => [$this, 'createdAtTo'],
            self::TOTAL_FROM => [$this, 'totalFrom'],
            self::TOTAL_TO => [$this, 'totalTo'],
            self::SORT => [$this, 'sort'],
        ];
    }

    public function search(Builder $builder, $value)
    {
        // Нормализация пробелов и обрезка
        $normalized = trim(preg_replace('/\s+/', ' ', (string) $value));

        return $builder->where(function ($query) use ($normalized) {
            // Если значение является числом, ищем точное совпадение по ID
            if ($normalized !== '' && is_numeric($normalized)) {
                $query->where('id', $normalized)
                    ->orWhere(function ($subQuery) use ($normalized) {
                        $subQuery->whereHas('client', function ($q) use ($normalized) {
                            $q->where('name', 'like', "%{$normalized}%");
                        })
                        ->orWhereHas('pet', function ($q) use ($normalized) {
                            $q->where('name', 'like', "%{$normalized}%");
                        });

                        // Поиск по notes только если длина запроса >= 3
                        if (mb_strlen($normalized) >= 3) {
                            $subQuery->orWhere('notes', 'like', "%{$normalized}%");
                        }
                    });
            } else {
                if ($normalized === '') {
                    return $query;
                }

                // Разбиваем на слова, отбрасываем слишком короткие (<3)
                $words = array_values(array_filter(explode(' ', $normalized), function ($w) {
                    return mb_strlen($w) >= 3;
                }));

                if (empty($words)) {
                    return $query;
                }

                // Ищем заказы, где каждое слово найдено в любом из полей
                $query->where(function ($subQuery) use ($words) {
                    foreach ($words as $word) {
                        $subQuery->where(function ($wordQuery) use ($word) {
                            $wordQuery->whereHas('client', function ($q) use ($word) {
                                $q->where('name', 'like', "%{$word}%");
                            })
                            ->orWhereHas('pet', function ($q) use ($word) {
                                $q->where('name', 'like', "%{$word}%");
                            })
                            // Поиск по notes только для слов длиной >=3 (фильтр уже применён)
                            ->orWhere('notes', 'like', "%{$word}%");
                        });
                    }
                });
            }
        });
    }

    public function client(Builder $builder, $value)
    {
        return $builder->where('client_id', $value);
    }

    public function pet(Builder $builder, $value)
    {
        return $builder->where('pet_id', $value);
    }

    public function status(Builder $builder, $value)
    {
        return $builder->where('status_id', $value);
    }

    public function branch(Builder $builder, $value)
    {
        return $builder->where('branch_id', $value);
    }

    public function createdAtFrom(Builder $builder, $value)
    {
        return $builder->whereDate('created_at', '>=', $value);
    }

    public function createdAtTo(Builder $builder, $value)
    {
        return $builder->whereDate('created_at', '<=', $value);
    }

    public function totalFrom(Builder $builder, $value)
    {
        return $builder->where('total', '>=', $value);
    }

    public function totalTo(Builder $builder, $value)
    {
        return $builder->where('total', '<=', $value);
    }

    public function sort(Builder $builder, $value)
    {
        switch ($value) {
            case 'created_at_desc':
                return $builder->orderBy('created_at', 'desc');
            case 'created_at_asc':
                return $builder->orderBy('created_at', 'asc');
            case 'total_desc':
                return $builder->orderBy('total', 'desc');
            case 'total_asc':
                return $builder->orderBy('total', 'asc');
            case 'client_name_asc':
                return $builder->join('users', 'orders.client_id', '=', 'users.id')
                    ->orderBy('users.name', 'asc')
                    ->select('orders.*');
            case 'client_name_desc':
                return $builder->join('users', 'orders.client_id', '=', 'users.id')
                    ->orderBy('users.name', 'desc')
                    ->select('orders.*');
            case 'pet_name_asc':
                return $builder->join('pets', 'orders.pet_id', '=', 'pets.id')
                    ->orderBy('pets.name', 'asc')
                    ->select('orders.*');
            case 'pet_name_desc':
                return $builder->join('pets', 'orders.pet_id', '=', 'pets.id')
                    ->orderBy('pets.name', 'desc')
                    ->select('orders.*');
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