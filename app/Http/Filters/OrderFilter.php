<?php

namespace App\Http\Filters;

use App\Http\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\Service;
use App\Models\Drug;
use App\Models\LabTestType;
use App\Models\VaccinationType;

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
        
        if (empty($normalized)) {
            return $builder;
        }

        return $builder->where(function ($query) use ($normalized) {
            // Если значение является числом, ищем точное совпадение по ID (самый быстрый поиск)
            if (is_numeric($normalized)) {
                $query->where('id', $normalized);
                return;
            }

            // Для текстового поиска используем EXISTS подзапросы для лучшей производительности
            $query->where(function ($subQuery) use ($normalized) {
                // Поиск по основным полям заказа
                $subQuery->where('notes', 'like', "%{$normalized}%");
                
                // Поиск по связанным таблицам через EXISTS (быстрее чем whereHas)
                $subQuery->orWhereExists(function ($existsQuery) use ($normalized) {
                    $existsQuery->select(\DB::raw(1))
                        ->from('users')
                        ->whereColumn('users.id', 'orders.client_id')
                        ->where('users.name', 'like', "%{$normalized}%");
                });
                
                $subQuery->orWhereExists(function ($existsQuery) use ($normalized) {
                    $existsQuery->select(DB::raw(1))
                        ->from('pets')
                        ->whereColumn('pets.id', 'orders.pet_id')
                        ->where('pets.name', 'like', "%{$normalized}%");
                });
                
                $subQuery->orWhereExists(function ($existsQuery) use ($normalized) {
                    $existsQuery->select(DB::raw(1))
                        ->from('employees')
                        ->whereColumn('employees.id', 'orders.manager_id')
                        ->where('employees.name', 'like', "%{$normalized}%");
                });
                
                // Поиск по элементам заказа через JOIN (значительно быстрее whereHasMorph)
                $subQuery->orWhereExists(function ($existsQuery) use ($normalized) {
                    $existsQuery->select(DB::raw(1))
                        ->from('order_items')
                        ->join('services', function ($join) {
                            $join->on('services.id', '=', 'order_items.item_id')
                                 ->where('order_items.item_type', 'App\Models\Service');
                        })
                        ->whereColumn('order_items.order_id', 'orders.id')
                        ->where('services.name', 'like', "%{$normalized}%");
                });
                
                $subQuery->orWhereExists(function ($existsQuery) use ($normalized) {
                    $existsQuery->select(DB::raw(1))
                        ->from('order_items')
                        ->join('drugs', function ($join) {
                            $join->on('drugs.id', '=', 'order_items.item_id')
                                 ->where('order_items.item_type', 'App\Models\Drug');
                        })
                        ->whereColumn('order_items.order_id', 'orders.id')
                        ->where('drugs.name', 'like', "%{$normalized}%");
                });
                
                $subQuery->orWhereExists(function ($existsQuery) use ($normalized) {
                    $existsQuery->select(DB::raw(1))
                        ->from('order_items')
                        ->join('lab_test_types', function ($join) {
                            $join->on('lab_test_types.id', '=', 'order_items.item_id')
                                 ->where('order_items.item_type', 'App\Models\LabTestType');
                        })
                        ->whereColumn('order_items.order_id', 'orders.id')
                        ->where('lab_test_types.name', 'like', "%{$normalized}%");
                });
                
                $subQuery->orWhereExists(function ($existsQuery) use ($normalized) {
                    $existsQuery->select(DB::raw(1))
                        ->from('order_items')
                        ->join('vaccination_types', function ($join) {
                            $join->on('vaccination_types.id', '=', 'order_items.item_id')
                                 ->where('order_items.item_type', 'App\Models\VaccinationType');
                        })
                        ->whereColumn('order_items.order_id', 'orders.id')
                        ->where('vaccination_types.name', 'like', "%{$normalized}%");
                });
            });
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