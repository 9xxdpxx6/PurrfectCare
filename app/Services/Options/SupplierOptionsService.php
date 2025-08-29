<?php

namespace App\Services\Options;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Supplier::query();
        
        // Применяем поиск
        $search = $request->input('q');
        if ($search) {
            $searchTerms = array_filter(explode(' ', trim($search)));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $term = trim($term);
                    if (empty($term)) continue;
                    
                    $q->where(function($subQ) use ($term) {
                        // Поиск по названию поставщика
                        $subQ->where('name', 'like', '%' . $term . '%')
                        // Поиск по контактному лицу
                        ->orWhere('contact_person', 'like', '%' . $term . '%')
                        // Поиск по email
                        ->orWhere('email', 'like', '%' . $term . '%')
                        // Поиск по телефону
                        ->orWhere('phone', 'like', '%' . $term . '%');
                    });
                }
            });
        }
        
        return $this->buildOptions($request, $query, [
            'model' => Supplier::class
        ]);
    }
} 