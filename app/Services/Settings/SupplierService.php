<?php

namespace App\Services\Settings;

use App\Models\Supplier;
use App\Http\Filters\Settings\SupplierFilter;

class SupplierService
{
    /**
     * Получить всех поставщиков с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return Supplier::filter(new SupplierFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать нового поставщика
     */
    public function create(array $data)
    {
        return Supplier::create($data);
    }

    /**
     * Обновить поставщика
     */
    public function update(Supplier $supplier, array $data)
    {
        return $supplier->update($data);
    }

    /**
     * Удалить поставщика
     */
    public function delete(Supplier $supplier)
    {
        if ($errorMessage = $supplier->hasDependencies()) {
            throw new \Exception($errorMessage);
        }
        
        return $supplier->delete();
    }

    /**
     * Получить всех поставщиков для селекта
     */
    public function getForSelect()
    {
        return Supplier::orderBy('name')->get();
    }
} 