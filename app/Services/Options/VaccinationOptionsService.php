<?php

namespace App\Services\Options;

use App\Models\Vaccination;
use Illuminate\Http\Request;

class VaccinationOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Vaccination::with(['pet', 'veterinarian']);
        $petId = $request->input('pet_id');
        
        // Фильтруем по питомцу если указан
        if ($petId) {
            $query->where('pet_id', $petId);
        }
        
        return $this->buildOptions($request, $query, [
            'model' => Vaccination::class,
            'include_price' => $request->input('include_price', false)
        ]);
    }

    protected function formatText($item, $config): string
    {
        $date = $item->administered_at ? \Carbon\Carbon::parse($item->administered_at)->format('d.m.Y') : 'без даты';
        return "Вакцинация от {$date} - {$item->pet->name}";
    }
} 