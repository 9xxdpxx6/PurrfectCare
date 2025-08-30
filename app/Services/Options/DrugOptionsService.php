<?php

namespace App\Services\Options;

use App\Models\Drug;
use Illuminate\Http\Request;

class DrugOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Drug::with('unit');
        
        // Фильтруем по филиалу, если указан
        $branchId = $request->input('branch_id');
        if ($branchId) {
            $query->availableInBranch($branchId);
        }
        
        // Применяем поиск, если есть запрос
        $search = $request->input('q');
        if ($search) {
            $searchTerms = array_filter(explode(' ', trim($search)));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $term = trim($term);
                    if (empty($term)) continue;
                    
                    $q->where(function($subQ) use ($term) {
                        // Поиск только по названию препарата (столбец description не существует)
                        $subQ->where('name', 'like', '%' . $term . '%');
                    });
                }
            });
        }
        
        return $this->buildOptions($request, $query, [
            'model' => Drug::class,
            'include_price' => $request->input('include_price', false),
            'branch_id' => $branchId
        ]);
    }

    protected function formatText($item, $config): string
    {
        $unit = $item->unit ? $item->unit->symbol : null;
        $text = $item->name . ($unit ? ' (' . $unit . ')' : '');
        
        // Добавляем информацию о количестве в филиале, если указан branch_id
        if (isset($config['branch_id']) && $config['branch_id']) {
            $branchDrug = $item->branches()
                ->where('branch_id', $config['branch_id'])
                ->first();
            
            if ($branchDrug) {
                $text .= ' - В наличии: ' . $branchDrug->pivot->quantity;
            } else {
                $text .= ' - Нет в наличии';
            }
        }
        
        return $text;
    }

    protected function formatOption($item, $config): array
    {
        $option = parent::formatOption($item, $config);
        
        // Добавляем информацию о stock для препаратов
        if (isset($config['branch_id']) && $config['branch_id']) {
            $branchDrug = $item->branches()
                ->where('branch_id', $config['branch_id'])
                ->first();
            
            $option['stock'] = $branchDrug ? $branchDrug->pivot->quantity : 0;
        }
        
        return $option;
    }
} 