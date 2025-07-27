<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class DrugFilter extends AbstractFilter
{
    const SEARCH = 'search';
    const PRESCRIPTION_REQUIRED = 'prescription_required';
    const UNIT = 'unit';
    const SUPPLIER = 'supplier';
    const SORT = 'sort';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::PRESCRIPTION_REQUIRED => [$this, 'prescriptionRequired'],
            self::UNIT => [$this, 'unit'],
            self::SUPPLIER => [$this, 'supplier'],
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
        
        $builder->where(function ($query) use ($words) {
            foreach ($words as $word) {
                $query->where(function ($q) use ($word) {
                    $q->where('name', 'like', "%{$word}%")
                      ->orWhereHas('procurements', function ($procQuery) use ($word) {
                          $procQuery->whereHas('supplier', function ($suppQuery) use ($word) {
                              $suppQuery->where('name', 'like', "%{$word}%");
                          });
                      });
                });
            }
        });
    }

    protected function prescriptionRequired(Builder $builder, $value)
    {
        $builder->where('prescription_required', $value === '1');
    }

    protected function unit(Builder $builder, $value)
    {
        $builder->where('unit_id', $value);
    }

    protected function supplier(Builder $builder, $value)
    {
        $builder->whereHas('procurements', function ($query) use ($value) {
            $query->where('supplier_id', $value);
        });
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
            case 'quantity_asc':
                $builder->orderBy('quantity');
                break;
            case 'quantity_desc':
                $builder->orderBy('quantity', 'desc');
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