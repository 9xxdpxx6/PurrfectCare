<?php

namespace App\Services\Options;

use App\Models\DictionarySymptom;
use Illuminate\Http\Request;

class SymptomOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = DictionarySymptom::query();
        $selectedIds = $request->input('selected');
        $search = $request->input('q');
        $options = [];
        
        // Обрабатываем выбранные значения
        if ($selectedIds) {
            $selectedArray = is_array($selectedIds) ? $selectedIds : explode(',', $selectedIds);
            foreach ($selectedArray as $selectedId) {
                if (is_numeric($selectedId)) {
                    $selected = DictionarySymptom::find($selectedId);
                    if ($selected) {
                        $options[] = [
                            'value' => $selected->id,
                            'text' => $selected->name
                        ];
                        $query->where('id', '!=', $selectedId);
                    }
                } else {
                    // Кастомный симптом
                    $options[] = [
                        'value' => $selectedId,
                        'text' => $selectedId
                    ];
                }
            }
        }
        
        if ($search) {
            $searchTerms = array_filter(explode(' ', trim($search)));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $term = trim($term);
                    if (empty($term)) continue;
                    
                    $q->where('name', 'like', '%' . $term . '%');
                }
            });
        }
        
        $symptoms = $query->orderBy('name')->limit(15)->get();
        foreach ($symptoms as $symptom) {
            $options[] = [
                'value' => $symptom->id,
                'text' => $symptom->name
            ];
        }
        
        // Если есть поиск и не найдено точного совпадения, добавляем возможность создать кастомный
        if ($search && !$symptoms->where('name', $search)->count() && !empty(trim($search))) {
            $options[] = [
                'value' => $search,
                'text' => $search . ' (создать)'
            ];
        }
        
        return response()->json($options);
    }
} 