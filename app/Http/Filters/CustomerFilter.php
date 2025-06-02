<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class CustomerFilter extends AbstractFilter
{
    const KEYWORD = 'keyword';
    const SORT = 'sort';

    protected function getCallbacks(): array
    {
        return [
            self::KEYWORD => [$this, 'keyword'],
            self::SORT => [$this, 'sort'],
        ];
    }

    protected function keyword(Builder $builder, $value)
    {
        $words = explode(' ', $value);

        $builder->where(function ($query) use ($words) {
            foreach ($words as $word) {
                $query->where(function ($query) use ($word) {
                    // Поиск по имени, телефону и информации клиента
                    $query->where('name', 'like', '%' . $word . '%')
                        ->orWhere('phone', 'like', '%' . $word . '%')
                        ->orWhere('info', 'like', '%' . $word . '%')
                        // Поиск по заказам клиента
                        ->orWhereHas('orders', function ($query) use ($word) {
                            $query->where('name', 'like', '%' . $word . '%');
                        });
                });
            }
        });
    }

    protected function sort(Builder $builder, $value)
    {
        switch ($value) {
            case 'date_asc':
                $builder->orderBy('created_at');
                break;
            case 'date_desc':
                $builder->orderBy('created_at', 'desc');
                break;
            case 'orders_asc':
                $builder->withCount('orders')
                    ->orderBy('orders_count');
                break;
            case 'orders_desc':
                $builder->withCount('orders')
                    ->orderBy('orders_count', 'desc');
                break;
            case 'total_price_asc':
                $builder->withSum('orders', 'total')
                    ->orderBy('orders_sum_total');
                break;
            case 'total_price_desc':
                $builder->withSum('orders', 'total')
                    ->orderBy('orders_sum_total', 'desc');
                break;
            default:
                $builder->orderBy('id', 'desc');
                break;
        }
    }
}
