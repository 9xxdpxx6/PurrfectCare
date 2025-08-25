<?php

namespace App\Services\Options;

use App\Models\Breed;
use Illuminate\Http\Request;

class BreedOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Breed::with('species');
        
        // Применяем поиск по названию породы
        $search = $request->input('q');
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        
        return $this->buildOptions($request, $query, [
            'model' => Breed::class
        ]);
    }

    protected function formatText($item, $config): string
    {
        return $item->name . ($item->species ? ' (' . $item->species->name . ')' : '');
    }
}
