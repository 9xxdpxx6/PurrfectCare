<?php

namespace App\Services\Options;

use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Specialty::query();
        
        // Применяем поиск
        $search = $request->input('q');
        if ($search) {
            $searchTerms = array_filter(explode(' ', trim($search)));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $term = trim($term);
                    if (empty($term)) continue;
                    
                    $q->where(function($subQ) use ($term) {
                        // Поиск только по названию специальности (столбец description не существует)
                        $subQ->where('name', 'like', '%' . $term . '%');
                    });
                }
            });
        }
        
        return $this->buildOptions($request, $query, [
            'model' => Specialty::class
        ]);
    }
}
