<?php

namespace App\Services\Settings;

use App\Models\Supplier;
use App\Http\Filters\Settings\SupplierFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            DB::beginTransaction();
            
            $supplier = Supplier::create($data);
            
            DB::commit();
            
            Log::info('Поставщик успешно создан', [
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name
            ]);
            
            return $supplier;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании поставщика', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Обновить поставщика
     */
    public function update(Supplier $supplier, array $data)
    {
        try {
            DB::beginTransaction();
            
            $oldName = $supplier->name;
            $result = $supplier->update($data);
            
            DB::commit();
            
            Log::info('Поставщик успешно обновлен', [
                'supplier_id' => $supplier->id,
                'old_name' => $oldName,
                'new_name' => $supplier->name
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении поставщика', [
                'supplier_id' => $supplier->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Удалить поставщика
     */
    public function delete(Supplier $supplier)
    {
        try {
            DB::beginTransaction();
            
            if ($errorMessage = $supplier->hasDependencies()) {
                throw new \Exception($errorMessage);
            }
            
            $supplierName = $supplier->name;
            $result = $supplier->delete();
            
            DB::commit();
            
            Log::info('Поставщик успешно удален', [
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplierName
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении поставщика', [
                'supplier_id' => $supplier->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Получить всех поставщиков для селекта
     */
    public function getForSelect()
    {
        return Supplier::orderBy('name')->get();
    }
} 