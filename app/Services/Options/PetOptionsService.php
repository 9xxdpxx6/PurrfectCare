<?php

namespace App\Services\Options;

use App\Models\Pet;
use Illuminate\Http\Request;

class PetOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Pet::with(['client', 'breed']);
        $clientId = $request->input('client_id');
        $search = $request->input('q');
        
        // Фильтр по клиенту (если указан)
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        
        // Поиск по кличке питомца, ФИО владельца и породе
        if ($search) {
            $searchTerms = array_filter(explode(' ', trim($search)));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $term = trim($term);
                    if (empty($term)) continue;
                    
                    $q->where(function($subQ) use ($term) {
                        // Поиск по кличке питомца
                        $subQ->where('name', 'like', '%' . $term . '%')
                        // Поиск по ФИО владельца
                        ->orWhereHas('client', function($q2) use ($term) {
                            $q2->where('name', 'like', '%' . $term . '%');
                        })
                        // Поиск по породе
                        ->orWhereHas('breed', function($q2) use ($term) {
                            $q2->where('name', 'like', '%' . $term . '%');
                        });
                    });
                }
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