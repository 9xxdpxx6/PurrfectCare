<?php

namespace App\Services\Options;

use App\Models\DictionaryDiagnosis;
use Illuminate\Http\Request;

class DiagnosisOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = DictionaryDiagnosis::query();
        $selectedIds = $request->input('selected');
        $search = $request->input('q');
        $options = [];
        
        // Обрабатываем выбранные значения
        if ($selectedIds) {
            $selectedArray = is_array($selectedIds) ? $selectedIds : explode(',', $selectedIds);
            foreach ($selectedArray as $selectedId) {
                if (is_numeric($selectedId)) {
                    $selected = DictionaryDiagnosis::find($selectedId);
                    if ($selected) {
                        $options[] = [
                            'value' => $selected->id,
                            'text' => $selected->name
                        ];
                        $query->where('id', '!=', $selectedId);
                    }
                } else {
                    // Кастомный диагноз
                    $options[] = [
                        'value' => $selectedId,
                        'text' => $selectedId
                    ];
                }
            }
        }
        
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }
        
        $diagnoses = $query->orderBy('name')->limit(15)->get();
        foreach ($diagnoses as $diagnosis) {
            $options[] = [
                'value' => $diagnosis->id,
                'text' => $diagnosis->name
            ];
        }
        
        // Если есть поиск и не найдено точного совпадения, добавляем возможность создать кастомный
        if ($search && !$diagnoses->where('name', $search)->count() && !empty(trim($search))) {
            $options[] = [
                'value' => $search,
                'text' => "Добавить: {$search}"
            ];
        }

        return response()->json($options);
    }
} 