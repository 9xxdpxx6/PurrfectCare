<?php

namespace App\Services\Options;

use App\Models\Drug;
use Illuminate\Http\Request;

class DrugOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Drug::with('unit');
        return $this->buildOptions($request, $query, [
            'model' => Drug::class,
            'include_price' => $request->input('include_price', false)
        ]);
    }

    protected function formatText($item, $config): string
    {
        $unit = $item->unit ? $item->unit->symbol : null;
        return $item->name . ($unit ? ' (' . $unit . ')' : '');
    }
} 