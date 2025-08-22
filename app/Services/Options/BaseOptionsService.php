<?php

namespace App\Services\Options;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

abstract class BaseOptionsService
{
    /**
     * Основной метод для построения опций
     */
    protected function buildOptions(Request $request, $query, $config = []): JsonResponse
    {
        $options = [];
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);

        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все'];
        }

        // Добавляем выбранный элемент
        if ($selectedId && is_numeric($selectedId)) {
            $selected = $config['model']::find($selectedId);
            if ($selected) {
                $option = [
                    'value' => $selected->id,
                    'text' => $this->formatText($selected, $config)
                ];
                
                if (isset($config['include_price']) && $config['include_price']) {
                    $option['price'] = $selected->price ?? 0;
                }
                
                $options[] = $option;
                $query->where('id', '!=', $selectedId);
            }
        }

        // Основной запрос для поиска/загрузки
        // Поиск уже должен быть применен в дочернем классе
        if (!$search) {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $items = $query->limit(20)->get();

        // Добавляем остальные элементы
        foreach ($items as $item) {
            $option = [
                'value' => $item->id,
                'text' => $this->formatText($item, $config)
            ];
            
            if (isset($config['include_price']) && $config['include_price']) {
                $option['price'] = $item->price ?? 0;
            }
            
            $options[] = $option;
        }

        return response()->json($options);
    }

    /**
     * Форматирование текста для опции
     */
    protected function formatText($item, $config): string
    {
        return $item->name;
    }
}
