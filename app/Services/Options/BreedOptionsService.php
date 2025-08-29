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
            $searchTerms = array_filter(explode(' ', trim($search)));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $term = trim($term);
                    if (empty($term)) continue;
                    
                    $q->where(function($subQ) use ($term) {
                        // Поиск по названию породы
                        $subQ->where('name', 'like', '%' . $term . '%')
                        // Поиск по названию вида
                        ->orWhereHas('species', function($sq) use ($term) {
                            $sq->where('name', 'like', '%' . $term . '%');
                        });
                    });
                }
            });
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
