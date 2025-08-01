<?php

namespace App\Http\Filters\Settings;

use App\Http\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class UnitFilter extends AbstractFilter
{
    const SEARCH = 'search';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
        ];
    }

    protected function search(Builder $builder, $value)
    {
        if (empty($value)) {
            return $builder;
        }

        $words = array_filter(explode(' ', trim($value)));
        if (empty($words)) {
            return $builder;
        }

        return $builder->where(function ($query) use ($words) {
            foreach ($words as $word) {
                $query->where(function ($wordQuery) use ($word) {
                    $wordQuery->where('name', 'like', "%$word%")
                        ->orWhere('symbol', 'like', "%$word%");
                });
            }
        });
    }

    public function apply(Builder $builder)
    {
        parent::apply($builder);
        // Если сортировка не указана, сортируем по id DESC
        if (!isset($this->queryParams['sort']) || !$this->queryParams['sort']) {
            $builder->orderByDesc('id');
        }
    }
} 