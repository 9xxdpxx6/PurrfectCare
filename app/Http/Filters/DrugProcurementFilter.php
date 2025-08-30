<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class DrugProcurementFilter extends AbstractFilter
{
    const SEARCH = 'search';
    const SUPPLIER = 'supplier';
    const DRUG = 'drug';
    const BRANCH = 'branch';
    const DELIVERY_DATE_FROM = 'delivery_date_from';
    const DELIVERY_DATE_TO = 'delivery_date_to';
    const EXPIRY_DATE_FROM = 'expiry_date_from';
    const EXPIRY_DATE_TO = 'expiry_date_to';
    const PRICE_FROM = 'price_from';
    const PRICE_TO = 'price_to';
    const SORT = 'sort';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::SUPPLIER => [$this, 'supplier'],
            self::DRUG => [$this, 'drug'],
            self::BRANCH => [$this, 'branch'],
            self::DELIVERY_DATE_FROM => [$this, 'deliveryDateFrom'],
            self::DELIVERY_DATE_TO => [$this, 'deliveryDateTo'],
            self::EXPIRY_DATE_FROM => [$this, 'expiryDateFrom'],
            self::EXPIRY_DATE_TO => [$this, 'expiryDateTo'],
            self::PRICE_FROM => [$this, 'priceFrom'],
            self::PRICE_TO => [$this, 'priceTo'],
            self::SORT => [$this, 'sort'],
        ];
    }

    protected function search(Builder $builder, $value)
    {
        $builder->where(function (Builder $query) use ($value) {
            $query->whereHas('drug', function (Builder $q) use ($value) {
                $q->where('name', 'like', "%{$value}%");
            })->orWhereHas('supplier', function (Builder $q) use ($value) {
                $q->where('name', 'like', "%{$value}%");
            })->orWhereHas('branch', function (Builder $q) use ($value) {
                $q->where('name', 'like', "%{$value}%");
            });
        });
    }

    protected function supplier(Builder $builder, $value)
    {
        $builder->where('supplier_id', $value);
    }

    protected function drug(Builder $builder, $value)
    {
        $builder->where('drug_id', $value);
    }

    protected function branch(Builder $builder, $value)
    {
        $builder->where('branch_id', $value);
    }

    protected function deliveryDateFrom(Builder $builder, $value)
    {
        try {
            // Parse date from dd.mm.yyyy format to Y-m-d
            $date = \Carbon\Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
            $builder->whereDate('delivery_date', '>=', $date);
        } catch (\Exception $e) {
            // If parsing fails, try original format
            $builder->whereDate('delivery_date', '>=', $value);
        }
    }

    protected function deliveryDateTo(Builder $builder, $value)
    {
        try {
            // Parse date from dd.mm.yyyy format to Y-m-d
            $date = \Carbon\Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
            $builder->whereDate('delivery_date', '<=', $date);
        } catch (\Exception $e) {
            // If parsing fails, try original format
            $builder->whereDate('delivery_date', '<=', $value);
        }
    }

    protected function expiryDateFrom(Builder $builder, $value)
    {
        try {
            // Parse date from dd.mm.yyyy format to Y-m-d
            $date = \Carbon\Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
            $builder->whereDate('expiry_date', '>=', $date);
        } catch (\Exception $e) {
            // If parsing fails, try original format
            $builder->whereDate('expiry_date', '>=', $value);
        }
    }

    protected function expiryDateTo(Builder $builder, $value)
    {
        try {
            // Parse date from dd.mm.yyyy format to Y-m-d
            $date = \Carbon\Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
            $builder->whereDate('expiry_date', '<=', $date);
        } catch (\Exception $e) {
            // If parsing fails, try original format
            $builder->whereDate('expiry_date', '<=', $value);
        }
    }

    protected function priceFrom(Builder $builder, $value)
    {
        $builder->where('price', '>=', $value);
    }

    protected function priceTo(Builder $builder, $value)
    {
        $builder->where('price', '<=', $value);
    }

    protected function sort(Builder $builder, $value)
    {
        switch ($value) {
            case 'delivery_date_asc':
                $builder->orderBy('delivery_date');
                break;
            case 'delivery_date_desc':
                $builder->orderBy('delivery_date', 'desc');
                break;
            case 'expiry_date_asc':
                $builder->orderBy('expiry_date');
                break;
            case 'expiry_date_desc':
                $builder->orderBy('expiry_date', 'desc');
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
        
        // Если сортировка не указана, сортируем по дате поставки DESC
        if (!isset($this->queryParams['sort']) || !$this->queryParams['sort']) {
            $builder->orderByDesc('id');
        }
    }
} 