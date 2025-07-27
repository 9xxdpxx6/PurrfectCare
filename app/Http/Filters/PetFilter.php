<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class PetFilter extends AbstractFilter
{
    const SEARCH = 'search';
    const OWNER = 'owner';
    const GENDER = 'gender';
    const SORT = 'sort';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::OWNER => [$this, 'owner'],
            self::GENDER => [$this, 'gender'],
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
                      ->orWhereHas('client', function ($q2) use ($word) {
                          $q2->where('name', 'like', "%{$word}%");
                      })
                      ->orWhereHas('breed', function ($q2) use ($word) {
                          $q2->where('name', 'like', "%{$word}%")
                             ->orWhereHas('species', function ($q3) use ($word) {
                                 $q3->where('name', 'like', "%{$word}%");
                             });
                      });
                });
            }
        });
    }

    protected function owner(Builder $builder, $value)
    {
        $builder->where('client_id', $value);
    }

    protected function gender(Builder $builder, $value)
    {
        $builder->where('gender', $value);
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
            case 'birth_asc':
                $builder->orderBy('birth_date');
                break;
            case 'birth_desc':
                $builder->orderBy('birth_date', 'desc');
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