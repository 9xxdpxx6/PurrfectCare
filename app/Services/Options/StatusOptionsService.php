<?php

namespace App\Services\Options;

use App\Models\Status;
use Illuminate\Http\Request;

class StatusOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Status::query();
        
        // Применяем поиск по названию статуса
        $search = $request->input('q');
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        
        // Сортируем по названию для удобства
        $query->orderBy('name');
        
        return $this->buildOptions($request, $query, [
            'model' => Status::class
        ]);
    }
} 