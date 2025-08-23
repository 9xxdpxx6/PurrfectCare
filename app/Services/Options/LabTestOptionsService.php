<?php

namespace App\Services\Options;

use App\Models\LabTest;
use App\Models\LabTestType;
use App\Models\LabTestParam;
use Illuminate\Http\Request;

class LabTestOptionsService extends BaseOptionsService
{
    public function getLabTestTypeOptions(Request $request)
    {
        $query = LabTestType::query();
        
        // Если запрошены последние записи, загружаем их
        if ($request->has('recent')) {
            $limit = $request->input('recent', 20);
            $query->orderBy('id', 'desc')->limit($limit);
            
            $items = $query->get();
            $options = [];
            
            foreach ($items as $item) {
                $option = [
                    'value' => $item->id,
                    'text' => $item->name
                ];
                
                if ($request->input('include_price', false)) {
                    $option['price'] = $item->price ?? 0;
                }
                
                $options[] = $option;
            }
            
            return response()->json($options);
        }
        
        return $this->buildOptions($request, $query, [
            'model' => LabTestType::class,
            'include_price' => $request->input('include_price', false)
        ]);
    }

    public function getLabTestParamOptions(Request $request)
    {
        $query = LabTestParam::with('unit');
        return $this->buildOptions($request, $query, [
            'model' => LabTestParam::class
        ]);
    }

    public function getLabTestOptions(Request $request)
    {
        $query = LabTest::with(['pet', 'veterinarian', 'labTestType']);
        $petId = $request->input('pet_id');
        
        // Фильтруем по питомцу если указан
        if ($petId) {
            $query->where('pet_id', $petId);
        }
        
        return $this->buildOptions($request, $query, [
            'model' => LabTest::class,
            'include_price' => $request->input('include_price', false)
        ]);
    }

    protected function formatText($item, $config): string
    {
        if ($item instanceof LabTestParam) {
            $unit = $item->unit ? $item->unit->symbol : null;
            return $item->name . ($unit ? ' (' . $unit . ')' : '');
        }
        
        if ($item instanceof LabTest) {
            $date = $item->received_at ? \Carbon\Carbon::parse($item->received_at)->format('d.m.Y') : 'без даты';
            return "Анализ от {$date} - {$item->pet->name}";
        }
        
        return $item->name;
    }
} 