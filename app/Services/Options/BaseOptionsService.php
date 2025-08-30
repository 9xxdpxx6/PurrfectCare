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
        // Параметр filter может приходить как строка "true" / "false" —
        // приводим его к булевому виду, чтобы 'false' не считался истинным
        $isFilter = filter_var($request->input('filter', false), FILTER_VALIDATE_BOOLEAN);

        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все'];
        }

        // Добавляем выбранный элемент
        if ($selectedId && is_numeric($selectedId)) {
            $selected = $config['model']::find($selectedId);
            if ($selected) {
                $option = $this->formatOption($selected, $config);
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
            $option = $this->formatOption($item, $config);
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

    /**
     * Форматирование опции для JSON ответа
     */
    protected function formatOption($item, $config): array
    {
        $option = [
            'value' => $item->id,
            'text' => $this->formatText($item, $config)
        ];
        
        if (isset($config['include_price']) && $config['include_price']) {
            $option['price'] = $item->price ?? 0;
        }
        
        return $option;
    }
}
