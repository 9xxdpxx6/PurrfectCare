<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class UserFilter extends AbstractFilter
{
    const SEARCH = 'search';
    const HAS_PETS = 'has_pets';
    const HAS_ORDERS = 'has_orders';
    const HAS_VISITS = 'has_visits';
    const SORT = 'sort';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::HAS_PETS => [$this, 'hasPets'],
            self::HAS_ORDERS => [$this, 'hasOrders'],
            self::HAS_VISITS => [$this, 'hasVisits'],
            self::SORT => [$this, 'sort'],
        ];
    }

    protected function search(Builder $builder, $value)
    {
        $words = explode(' ', $value);
        $builder->where(function ($query) use ($words) {
            foreach ($words as $word) {
                $query->where(function ($q) use ($word) {
                    $q->where('name', 'like', "%{$word}%")
                      ->orWhere('email', 'like', "%{$word}%")
                      ->orWhere('phone', 'like', "%{$word}%")
                      ->orWhere('address', 'like', "%{$word}%")
                      ->orWhere('telegram', 'like', "%{$word}%");
                });
            }
        });
    }

    protected function hasPets(Builder $builder, $value)
    {
        if ($value === '1') {
            $builder->has('pets');
        } elseif ($value === '0') {
            $builder->doesntHave('pets');
        }
    }

    protected function hasOrders(Builder $builder, $value)
    {
        if ($value === '1') {
            $builder->has('orders');
        } elseif ($value === '0') {
            $builder->doesntHave('orders');
        }
    }

    protected function hasVisits(Builder $builder, $value)
    {
        if ($value === '1') {
            $builder->has('visits');
        } elseif ($value === '0') {
            $builder->doesntHave('visits');
        }
    }

    protected function sort(Builder $builder, $value)
    {
        switch ($value) {
            case 'name_asc':
                $builder->orderBy('name');
                break;
            case 'name_desc':
                $builder->orderBy('name', 'desc');
                break;
            case 'email_asc':
                $builder->orderBy('email');
                break;
            case 'email_desc':
                $builder->orderBy('email', 'desc');
                break;
            case 'orders_desc':
                $builder->withCount('orders')->orderBy('orders_count', 'desc');
                break;
            case 'orders_asc':
                $builder->withCount('orders')->orderBy('orders_count', 'asc');
                break;
            case 'created_asc':
                $builder->orderBy('created_at');
                break;
            case 'created_desc':
                $builder->orderBy('created_at', 'desc');
                break;
            default:
                $builder->orderBy('id', 'desc');
                break;
        }
    }

    public function apply(Builder $builder)
    {
        parent::apply($builder);
        // Если сортировка не указана, сортируем по дате создания DESC
        if (!isset($this->queryParams['sort']) || !$this->queryParams['sort']) {
            $builder->orderByDesc('id');
        }
    }
} 