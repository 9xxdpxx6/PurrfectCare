<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class EmployeeFilter extends AbstractFilter
{
    const SEARCH = 'search';
    const BRANCH = 'branch';
    const SPECIALTY = 'specialty';
    const SORT = 'sort';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::BRANCH => [$this, 'branch'],
            self::SPECIALTY => [$this, 'specialty'],
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
                      ->orWhereHas('specialties', function ($q2) use ($word) {
                          $q2->where('name', 'like', "%{$word}%");
                      })
                      ->orWhereHas('branches', function ($q3) use ($word) {
                          $q3->where('name', 'like', "%{$word}%");
                      });
                });
            }
        });
    }

    protected function branch(Builder $builder, $value)
    {
        $builder->whereHas('branches', function ($q) use ($value) {
            $q->where('branches.id', $value);
        });
    }

    protected function specialty(Builder $builder, $value)
    {
        $builder->whereHas('specialties', function ($q) use ($value) {
            $q->where('specialties.id', $value);
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
            case 'email_asc':
                $builder->orderBy('email');
                break;
            case 'email_desc':
                $builder->orderBy('email', 'desc');
                break;
            default:
                $builder->orderBy('id', 'desc');
                break;
        }
    }

    public function apply(Builder $builder)
    {
        parent::apply($builder);
        if (!isset($this->queryParams['sort']) || !$this->queryParams['sort']) {
            $builder->orderByDesc('id');
        }
    }
} 