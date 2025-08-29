<?php

namespace App\Services\Options;

use App\Models\Drug;
use Illuminate\Http\Request;

class DrugOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Drug::with('unit');
        
        // Применяем поиск, если есть запрос
        $search = $request->input('q');
        if ($search) {
            $searchTerms = array_filter(explode(' ', trim($search)));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $term = trim($term);
                    if (empty($term)) continue;
                    
                    $q->where(function($subQ) use ($term) {
                        // Поиск по названию препарата
                        $subQ->where('name', 'like', '%' . $term . '%')
                        // Поиск по описанию
                        ->orWhere('description', 'like', '%' . $term . '%');
                    });
                }
            });
        }
        
        return $this->buildOptions($request, $query, [
            'model' => Drug::class,
            'include_price' => $request->input('include_price', false)
        ]);
    }

    protected function formatText($item, $config): string
    {
        $unit = $item->unit ? $item->unit->symbol : null;
        return $item->name . ($unit ? ' (' . $unit . ')' : '');
    }
} 