<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class VisitFilter extends AbstractFilter
{
    const SEARCH = 'search';
    const CLIENT = 'client';
    const PET = 'pet';
    const STATUS = 'status';
    const DATE_FROM = 'date_from';
    const DATE_TO = 'date_to';
    const SORT = 'sort';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::CLIENT => [$this, 'client'],
            self::PET => [$this, 'pet'],
            self::STATUS => [$this, 'status'],
            self::DATE_FROM => [$this, 'dateFrom'],
            self::DATE_TO => [$this, 'dateTo'],
            self::SORT => [$this, 'sort'],
        ];
    }

    protected function search(Builder $builder, $value)
    {
        // Нормализация пробелов и обрезка
        $normalized = trim(preg_replace('/\s+/', ' ', (string) $value));

        // Разбиваем поисковый запрос на слова и отбрасываем слишком короткие (<3)
        $words = array_values(array_filter(explode(' ', $normalized), function ($w) {
            return mb_strlen($w) >= 3;
        }));
        
        if (empty($words)) {
            return $builder;
        }
        
        $builder->where(function ($query) use ($words) {
            foreach ($words as $word) {
                $query->where(function ($q) use ($word) {
                    $q->where('complaints', 'like', "%{$word}%")
                      // Поиск по notes только для слов длиной >=3 (фильтр уже применён)
                      ->orWhere('notes', 'like', "%{$word}%")
                      ->orWhereHas('client', function ($q2) use ($word) {
                          $q2->where('name', 'like', "%{$word}%")
                             ->orWhere('email', 'like', "%{$word}%")
                             ->orWhere('phone', 'like', "%{$word}%");
                      })
                      ->orWhereHas('pet', function ($q2) use ($word) {
                          $q2->where('name', 'like', "%{$word}%");
                      })
                      ->orWhereHas('schedule', function ($q2) use ($word) {
                          $q2->whereHas('veterinarian', function ($q3) use ($word) {
                              $q3->where('name', 'like', "%{$word}%");
                          });
                      })
                      ->orWhereHas('diagnoses', function ($q2) use ($word) {
                          $q2->where('custom_diagnosis', 'like', "%{$word}%")
                              ->orWhereHas('dictionaryDiagnosis', function ($q3) use ($word) {
                                  $q3->where('name', 'like', "%{$word}%");
                              });
                      })
                      ->orWhereHas('symptoms', function ($q2) use ($word) {
                          $q2->where('custom_symptom', 'like', "%{$word}%")
                              ->orWhereHas('dictionarySymptom', function ($q3) use ($word) {
                                  $q3->where('name', 'like', "%{$word}%");
                              });
                      });
                });
            }
        });
    }

    protected function client(Builder $builder, $value)
    {
        $builder->where('client_id', $value);
    }

    protected function pet(Builder $builder, $value)
    {
        $builder->where('pet_id', $value);
    }

    protected function status(Builder $builder, $value)
    {
        $builder->where('status_id', $value);
    }

    protected function dateFrom(Builder $builder, $value)
    {
        $builder->whereDate('starts_at', '>=', $value);
    }

    protected function dateTo(Builder $builder, $value)
    {
        $builder->whereDate('starts_at', '<=', $value);
    }

    protected function sort(Builder $builder, $value)
    {
        switch ($value) {
            case 'date_asc':
                $builder->orderBy('starts_at');
                break;
            case 'date_desc':
                $builder->orderBy('starts_at', 'desc');
                break;
            case 'client_asc':
                $builder->join('users', 'visits.client_id', '=', 'users.id')
                        ->orderBy('users.name');
                break;
            case 'client_desc':
                $builder->join('users', 'visits.client_id', '=', 'users.id')
                        ->orderBy('users.name', 'desc');
                break;
            case 'pet_asc':
                $builder->join('pets', 'visits.pet_id', '=', 'pets.id')
                        ->orderBy('pets.name');
                break;
            case 'pet_desc':
                $builder->join('pets', 'visits.pet_id', '=', 'pets.id')
                        ->orderBy('pets.name', 'desc');
                break;
            default:
                $builder->orderBy('id', 'desc');
                break;
        }
    }

    public function apply(Builder $builder)
    {
        parent::apply($builder);
        // Если сортировка не указана, сортируем по дате DESC
        if (!isset($this->queryParams['sort']) || !$this->queryParams['sort']) {
            $builder->orderByDesc('id');
        }
    }
} 