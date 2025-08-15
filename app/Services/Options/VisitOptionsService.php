<?php

namespace App\Services\Options;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VisitOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request): JsonResponse
    {
        $query = Visit::with(['client', 'pet', 'status']);
        
        // Фильтрация по клиенту (обязательно)
        if (!$request->has('client_id') || !$request->client_id) {
            return response()->json([]);
        }
        $query->where('client_id', $request->client_id);
        
        // Фильтрация по питомцу (если указан)
        if ($request->has('pet_id') && $request->pet_id) {
            $query->where('pet_id', $request->pet_id);
        }
        
        // Фильтрация по дате заказа (если указана)
        if ($request->has('order_date')) {
            $orderDate = $request->order_date;
            $query->where('starts_at', '<=', $orderDate);
        }
        
        // Сортировка по дате приема (новые сначала)
        $query->orderBy('starts_at', 'desc');
        
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        
        $options = [];
        
        // Добавляем выбранные элементы
        if ($selectedId) {
            // Обрабатываем как одиночное значение или список через запятую
            $selectedIds = is_numeric($selectedId) ? [$selectedId] : explode(',', $selectedId);
            $selectedIds = array_filter($selectedIds, 'is_numeric');
            
            if (!empty($selectedIds)) {
                $selectedVisits = Visit::with(['client', 'pet', 'status'])->whereIn('id', $selectedIds)->get();
                foreach ($selectedVisits as $selected) {
                    $options[] = [
                        'value' => $selected->id,
                        'text' => $this->formatText($selected)
                    ];
                }
                $query->whereNotIn('id', $selectedIds);
            }
        }
        
        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('client', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%$search%");
                })
                ->orWhereHas('pet', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%$search%");
                })
                ->orWhere('complaints', 'like', "%$search%");
            });
        }
        
        $visits = $query->limit(20)->get();
        
        // Добавляем остальные элементы
        foreach ($visits as $visit) {
            $options[] = [
                'value' => $visit->id,
                'text' => $this->formatText($visit)
            ];
        }
        
        return response()->json($options);
    }
    
    protected function formatText($visit, $config = []): string
    {
        $text = "Прием от " . $visit->starts_at->format('d.m.Y H:i');
        
        if ($visit->client) {
            $text .= " - " . $visit->client->name;
        }
        
        if ($visit->pet) {
            $text .= " (" . $visit->pet->name . ")";
        }
        
        if ($visit->status) {
            $text .= " [" . $visit->status->name . "]";
        }
        
        return $text;
    }
}

