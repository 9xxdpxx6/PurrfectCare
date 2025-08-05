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
        
        // Фильтр по клиенту (если указан)
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        
        return $this->buildOptions($request, $query, [
            'model' => Pet::class
        ]);
    }

    protected function formatText($item, $config): string
    {
        return $item->name . ' (' . ($item->client->name ?? 'Без владельца') . ')';
    }
} 