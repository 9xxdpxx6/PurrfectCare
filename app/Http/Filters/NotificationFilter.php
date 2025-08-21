<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class NotificationFilter extends AbstractFilter
{
    const STATUS = 'status';
    const TYPE = 'type';
    const DATE_FROM = 'dateFrom';
    const DATE_TO = 'dateTo';
    const SORT = 'sort';

    protected function getCallbacks(): array
    {
        return [
            self::STATUS => [$this, 'status'],
            self::TYPE => [$this, 'type'],
            self::DATE_FROM => [$this, 'dateFrom'],
            self::DATE_TO => [$this, 'dateTo'],
            self::SORT => [$this, 'sort'],
        ];
    }

    protected function status(Builder $builder, $value): void
    {
        if ($value === 'unread') {
            $builder->whereNull('read_at');
        } elseif ($value === 'read') {
            $builder->whereNotNull('read_at');
        }
    }

    protected function type(Builder $builder, $value): void
    {
        if ($value) {
            $builder->whereJsonContains('data->type', $value);
        }
    }

    protected function dateFrom(Builder $builder, $value): void
    {
        if ($value) {
            $builder->whereDate('created_at', '>=', $value);
        }
    }

    protected function dateTo(Builder $builder, $value): void
    {
        if ($value) {
            $builder->whereDate('created_at', '<=', $value);
        }
    }

    protected function sort(Builder $builder, $value): void
    {
        switch ($value) {
            case 'created_asc':
                $builder->orderBy('created_at');
                break;
            case 'created_desc':
                $builder->orderBy('created_at', 'desc');
                break;
            case 'read_asc':
                $builder->orderBy('read_at');
                break;
            case 'read_desc':
                $builder->orderBy('read_at', 'desc');
                break;
            default:
                $builder->orderBy('created_at', 'desc');
                break;
        }
    }
}
