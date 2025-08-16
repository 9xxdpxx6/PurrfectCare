<?php

namespace App\Services\Settings;

use App\Models\Breed;
use App\Models\Species;
use App\Http\Filters\Settings\BreedFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BreedService
{
    /**
     * Получить все породы с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return Breed::with('species')
            ->filter(new BreedFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новую породу
     */
    public function create(array $data)
    {
        try {
            DB::beginTransaction();
            
            $breed = Breed::create($data);
            
            DB::commit();
            
            Log::info('Порода успешно создана', [
                'breed_id' => $breed->id,
                'breed_name' => $breed->name,
                'species_id' => $breed->species_id
            ]);
            
            return $breed;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании породы', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Обновить породу
     */
    public function update(Breed $breed, array $data)
    {
        try {
            DB::beginTransaction();
            
            $oldName = $breed->name;
            $oldSpeciesId = $breed->species_id;
            $result = $breed->update($data);
            
            DB::commit();
            
            Log::info('Порода успешно обновлена', [
                'breed_id' => $breed->id,
                'old_name' => $oldName,
                'new_name' => $breed->name,
                'old_species_id' => $oldSpeciesId,
                'new_species_id' => $breed->species_id
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении породы', [
                'breed_id' => $breed->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Удалить породу
     */
    public function delete(Breed $breed)
    {
        try {
            DB::beginTransaction();
            
            if ($errorMessage = $breed->hasDependencies()) {
                throw new \Exception($errorMessage);
            }
            
            $breedName = $breed->name;
            $result = $breed->delete();
            
            DB::commit();
            
            Log::info('Порода успешно удалена', [
                'breed_id' => $breed->id,
                'breed_name' => $breedName
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении породы', [
                'breed_id' => $breed->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Получить все виды животных для селекта
     */
    public function getSpeciesForSelect()
    {
        return Species::orderBy('name')->get();
    }
} 