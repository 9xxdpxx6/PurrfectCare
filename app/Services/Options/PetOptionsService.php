<?php

namespace App\Services\Options;

use App\Models\Pet;
use Illuminate\Http\Request;

class PetOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Pet::with('client');
        $clientId = $request->input('client_id');
        $search = $request->input('q');
        
        // Фильтр по клиенту (если указан)
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        
        // Поиск по кличке питомца и ФИО владельца
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Сортируем по имени питомца
        $query->orderBy('name');
        
        return $this->buildOptions($request, $query, [
            'model' => Pet::class
        ]);
    }

    protected function formatText($item, $config): string
    {
        return $item->name . ' (' . ($item->client->name ?? 'Без владельца') . ')';
    }
} 