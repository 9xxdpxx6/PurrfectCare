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
            $query->where('name', 'like', "%$search%");
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