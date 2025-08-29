<?php

namespace App\Services\Options;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Service::query();
        
        // Применяем поиск
        $search = $request->input('q');
        if ($search) {
            $searchTerms = array_filter(explode(' ', trim($search)));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $term = trim($term);
                    if (empty($term)) continue;
                    
                    $q->where(function($subQ) use ($term) {
                        // Поиск по названию услуги
                        $subQ->where('name', 'like', '%' . $term . '%')
                        // Поиск по описанию
                        ->orWhere('description', 'like', '%' . $term . '%');
                    });
                }
            });
        }
        
        return $this->buildOptions($request, $query, [
            'model' => Service::class,
            'include_price' => $request->input('include_price', false)
        ]);
    }
} 