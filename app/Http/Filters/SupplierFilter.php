<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class SupplierFilter extends AbstractFilter
{
    const SEARCH = 'search';
    const SORT = 'sort';

    /**
     * Определяет коллбэки для фильтров.
     */
    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::SORT => [$this, 'sort'],
        ];
    }

    /**
     * Фильтр поиска по имени.
     */
    protected function search(Builder $builder, $value)
    {
        // Разделяем строку поиска на слова и ищем совпадения в поле name
        $words = explode(' ', $value);
        $builder->where(function ($query) use ($words) {
            foreach ($words as $word) {
                $query->orWhere('name', 'like', "%{$word}%");
            }
        });
    }

    /**
     * Сортировка результатов.
     */
    protected function sort(Builder $builder, $value)
    {
        switch ($value) {
            case 'name_asc':
                $builder->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $builder->orderBy('name', 'desc');
                break;
            default:
                $builder->orderByDesc('id');
                break;
        }
    }

    /**
     * Применяет фильтры к запросу.
     */
    public function apply(Builder $builder)
    {
        parent::apply($builder);

        // Если сортировка не указана, сортируем по ID DESC
        if (!isset($this->queryParams['sort']) || !$this->queryParams['sort']) {
            $builder->orderByDesc('id');
        }
    }
}
