<?php

namespace App\Services\Options;

use App\Models\User;
use Illuminate\Http\Request;

class ClientOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = User::query();
        
        // Применяем поиск
        $search = $request->input('q');
        if ($search) {
            $searchTerms = array_filter(explode(' ', trim($search)));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $term = trim($term);
                    if (empty($term)) continue;
                    
                    $q->where(function($subQ) use ($term) {
                        // Поиск по имени
                        $subQ->where('name', 'like', '%' . $term . '%')
                        // Поиск по email
                        ->orWhere('email', 'like', '%' . $term . '%')
                        // Поиск по телефону
                        ->orWhere('phone', 'like', '%' . $term . '%');
                    });
                }
            });
        }
        
        return $this->buildOptions($request, $query, [
            'model' => User::class
        ]);
    }
} 