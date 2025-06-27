<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class ServiceFilter extends AbstractFilter
{
    const SEARCH = 'search';
    const BRANCH = 'branch';
    const PRICE_FROM = 'price_from';
    const PRICE_TO = 'price_to';
    const DURATION_FROM = 'duration_from';
    const DURATION_TO = 'duration_to';
    const SORT = 'sort';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::BRANCH => [$this, 'branch'],
            self::PRICE_FROM => [$this, 'priceFrom'],
            self::PRICE_TO => [$this, 'priceTo'],
            self::DURATION_FROM => [$this, 'durationFrom'],
            self::DURATION_TO => [$this, 'durationTo'],
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
                      ->orWhere('description', 'like', "%{$word}%");
                });
            }
        });
    }

    protected function branch(Builder $builder, $value)
    {
        $builder->whereHas('branches', function ($query) use ($value) {
            $query->where('branches.id', $value);
        });
    }

    protected function priceFrom(Builder $builder, $value)
    {
        $builder->where('price', '>=', $value);
    }

    protected function priceTo(Builder $builder, $value)
    {
        $builder->where('price', '<=', $value);
    }

    protected function durationFrom(Builder $builder, $value)
    {
        $builder->where('duration', '>=', $value);
    }

    protected function durationTo(Builder $builder, $value)
    {
        $builder->where('duration', '<=', $value);
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
            case 'price_asc':
                $builder->orderBy('price');
                break;
            case 'price_desc':
                $builder->orderBy('price', 'desc');
                break;
            case 'duration_asc':
                $builder->orderBy('duration');
                break;
            case 'duration_desc':
                $builder->orderBy('duration', 'desc');
                break;
            case 'branch_asc':
                $builder->join('branch_service', 'services.id', '=', 'branch_service.service_id')
                        ->join('branches', 'branch_service.branch_id', '=', 'branches.id')
                        ->orderBy('branches.name');
                break;
            case 'branch_desc':
                $builder->join('branch_service', 'services.id', '=', 'branch_service.service_id')
                        ->join('branches', 'branch_service.branch_id', '=', 'branches.id')
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
        // Если сортировка не указана, сортируем по id DESC
        if (!isset($this->queryParams['sort']) || !$this->queryParams['sort']) {
            $builder->orderByDesc('id');
        }
    }
} 