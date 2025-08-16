<?php

namespace App\Services\Settings;

use App\Models\Species;
use App\Http\Filters\Settings\SpeciesFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpeciesService
{
    /**
     * Получить все виды животных с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return Species::filter(new SpeciesFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новый вид животного
     */
    public function create(array $data)
    {
        try {
            DB::beginTransaction();
            
            $species = Species::create($data);
            
            DB::commit();
            
            Log::info('Вид животного успешно создан', [
                'species_id' => $species->id,
                'species_name' => $species->name
            ]);
            
            return $species;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании вида животного', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Обновить вид животного
     */
    public function update(Species $species, array $data)
    {
        try {
            DB::beginTransaction();
            
            $oldName = $species->name;
            $result = $species->update($data);
            
            DB::commit();
            
            Log::info('Вид животного успешно обновлен', [
                'species_id' => $species->id,
                'old_name' => $oldName,
                'new_name' => $species->name
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении вида животного', [
                'species_id' => $species->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Удалить вид животного
     */
    public function delete(Species $species)
    {
        try {
            DB::beginTransaction();
            
            if ($errorMessage = $species->hasDependencies()) {
                throw new \Exception($errorMessage);
            }
            
            $speciesName = $species->name;
            $result = $species->delete();
            
            DB::commit();
            
            Log::info('Вид животного успешно удален', [
                'species_id' => $species->id,
                'species_name' => $speciesName
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении вида животного', [
                'species_id' => $species->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Получить все виды животных для селекта
     */
    public function getForSelect()
    {
        return Species::orderBy('name')->get();
    }
} 