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
    public const MANAGER = 'manager';
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
            self::MANAGER => [$this, 'manager'],
            self::CREATED_AT_FROM => [$this, 'createdAtFrom'],
            self::CREATED_AT_TO => [$this, 'createdAtTo'],
            self::TOTAL_FROM => [$this, 'totalFrom'],
            self::TOTAL_TO => [$this, 'totalTo'],
            self::SORT => [$this, 'sort'],
        ];
    }

    public function search(Builder $builder, $value)
    {
        return $builder->where(function ($query) use ($value) {
            // Если значение является числом, ищем точное совпадение по ID
            if (is_numeric($value)) {
                $query->where('id', $value)
                    ->orWhere(function ($subQuery) use ($value) {
                        $subQuery->whereHas('client', function ($q) use ($value) {
                            $q->where('name', 'like', "%$value%");
                        })
                        ->orWhereHas('pet', function ($q) use ($value) {
                            $q->where('name', 'like', "%$value%");
                        })
                        ->orWhereHas('manager', function ($q) use ($value) {
                            $q->where('name', 'like', "%$value%");
                        })
                        ->orWhere('notes', 'like', "%$value%");
                    });
            } else {
                // Если не число, ищем только по текстовым полям
                $query->whereHas('client', function ($q) use ($value) {
                    $q->where('name', 'like', "%$value%");
                })
                ->orWhereHas('pet', function ($q) use ($value) {
                    $q->where('name', 'like', "%$value%");
                })
                ->orWhereHas('manager', function ($q) use ($value) {
                    $q->where('name', 'like', "%$value%");
                })
                ->orWhere('notes', 'like', "%$value%");
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

    public function manager(Builder $builder, $value)
    {
        return $builder->where('manager_id', $value);
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