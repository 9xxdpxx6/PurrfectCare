<?php

namespace App\Services\Options;

use App\Models\VaccinationType;
use Illuminate\Http\Request;

class VaccinationTypeOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = VaccinationType::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        
        // Если запрошены последние записи, загружаем их
        if ($request->has('recent')) {
            $limit = $request->input('recent', 20);
            $query->orderBy('id', 'desc')->limit($limit);
            
            $items = $query->get();
            $options = [];
            
            foreach ($items as $item) {
                $options[] = [
                    'value' => $item->id,
                    'text' => $item->name . ($item->price > 0 ? " (₽{$item->price})" : ''),
                    'description' => $item->description,
                    'price' => $item->price,
                ];
            }
            
            return response()->json($options);
        }
        
        // Добавляем выбранный элемент
        if ($selectedId && is_numeric($selectedId)) {
            $selected = VaccinationType::find($selectedId);
            if ($selected) {
                $option = [
                    'value' => $selected->id,
                    'text' => $selected->name . ($selected->price > 0 ? " (₽{$selected->price})" : ''),
                    'description' => $selected->description,
                    'price' => $selected->price,
                ];
                
                $query->where('id', '!=', $selectedId);
            }
        }

        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $vaccinationTypes = $query->limit(20)->get();
        $options = [];

        // Добавляем выбранный элемент первым
        if (isset($option)) {
            $options[] = $option;
        }

        // Добавляем остальные элементы
        foreach ($vaccinationTypes as $vaccinationType) {
            $options[] = [
                'value' => $vaccinationType->id,
                'text' => $vaccinationType->name . ($vaccinationType->price > 0 ? " (₽{$vaccinationType->price})" : ''),
                'description' => $vaccinationType->description,
                'price' => $vaccinationType->price,
            ];
        }

        return response()->json($options);
    }
}
