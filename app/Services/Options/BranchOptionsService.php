<?php

namespace App\Services\Options;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Branch::query();
        
        // Применяем поиск
        $search = $request->input('q');
        if ($search) {
            $searchTerms = array_filter(explode(' ', trim($search)));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $term = trim($term);
                    if (empty($term)) continue;
                    
                    $q->where(function($subQ) use ($term) {
                        // Поиск по названию филиала
                        $subQ->where('name', 'like', '%' . $term . '%')
                        // Поиск по адресу
                        ->orWhere('address', 'like', '%' . $term . '%');
                    });
                }
            });
        }
        
        return $this->buildOptions($request, $query, [
            'model' => Branch::class
        ]);
    }
} 