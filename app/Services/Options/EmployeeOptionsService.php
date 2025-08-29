<?php

namespace App\Services\Options;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeOptionsService extends BaseOptionsService
{
    public function getVeterinarianOptions(Request $request)
    {
        $query = Employee::query()
            ->whereHas('specialties', function($q) {
                $q->where('is_veterinarian', true);
            });
        
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
                        ->orWhere('email', 'like', '%' . $term . '%');
                    });
                }
            });
        }
            
        return $this->buildOptions($request, $query, [
            'model' => Employee::class
        ]);
    }

    public function getManagerOptions(Request $request)
    {
        $query = Employee::query();
        
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
                        ->orWhere('email', 'like', '%' . $term . '%');
                    });
                }
            });
        }
        
        return $this->buildOptions($request, $query, [
            'model' => Employee::class
        ]);
    }
} 