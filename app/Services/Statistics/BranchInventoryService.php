<?php

namespace App\Services\Statistics;

use App\Models\Branch;
use App\Models\Drug;
use App\Models\DrugProcurement;

class BranchInventoryService
{
    /**
     * Получить отчет по запасам препаратов по филиалам
     */
    public function getBranchInventoryReport()
    {
        return Branch::with(['drugs' => function($query) {
            $query->with(['unit:id,name,symbol']);
        }])
        ->get()
        ->map(function($branch) {
            return [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'drugs' => $branch->drugs->map(function($drug) {
                    return [
                        'drug_id' => $drug->id,
                        'drug_name' => $drug->name,
                        'drug_price' => $drug->price,
                                                 'stock_quantity' => $drug->pivot->quantity,
                        'unit_name' => $drug->unit->name ?? null,
                        'unit_symbol' => $drug->unit->symbol ?? null
                    ];
                })
            ];
        });
    }

    /**
     * Получить отчет по запасам конкретного филиала
     */
    public function getBranchInventoryReportById(int $branchId)
    {
        return Branch::with(['drugs' => function($query) {
            $query->with(['unit:id,name,symbol']);
        }])
        ->find($branchId)
        ->drugs
        ->map(function($drug) {
            return [
                'drug_id' => $drug->id,
                'drug_name' => $drug->name,
                'drug_price' => $drug->price,
                                         'stock_quantity' => $drug->pivot->quantity,
                'unit_name' => $drug->unit->name ?? null,
                'unit_symbol' => $drug->unit->symbol ?? null
            ];
        });
    }

    /**
     * Получить препараты с низким запасом по филиалам
     */
    public function getLowStockDrugs()
    {
        return Branch::with(['drugs' => function($query) {
            $query->with(['unit:id,symbol']);
        }])
        ->get()
        ->flatMap(function($branch) {
                         return $branch->drugs
                 ->filter(function($drug) {
                     return $drug->pivot->quantity <= 5; // Фиксированный минимальный порог
                 })
                ->map(function($drug) use ($branch) {
                    return [
                        'branch_id' => $branch->id,
                        'branch_name' => $branch->name,
                        'drug_id' => $drug->id,
                        'drug_name' => $drug->name,
                                                 'stock_quantity' => $drug->pivot->quantity,
                        'unit_symbol' => $drug->unit->symbol ?? null
                    ];
                });
        })
        ->sortBy(['branch_name', 'stock_quantity'])
        ->values();
    }

    /**
     * Получить общую стоимость запасов по филиалам
     */
    public function getBranchInventoryValue()
    {
        return Branch::with('drugs')
            ->get()
            ->map(function($branch) {
                $totalValue = $branch->drugs->sum(function($drug) {
                    return $drug->pivot->quantity * $drug->price;
                });
                
                return [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'total_value' => $totalValue,
                    'drugs_count' => $branch->drugs->count()
                ];
            })
            ->sortByDesc('total_value')
            ->values();
    }

    /**
     * Получить статистику по движению препаратов в филиале
     */
    public function getBranchDrugMovement(int $branchId, $startDate = null, $endDate = null)
    {
        $query = DrugProcurement::with('drug:id,name')
            ->where('branch_id', $branchId);

        if ($startDate) {
            $query->where('delivery_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('delivery_date', '<=', $endDate);
        }

        return $query->get()
            ->groupBy('drug_id')
            ->map(function($procurements, $drugId) {
                $drug = $procurements->first()->drug;
                return [
                    'drug_id' => $drugId,
                    'drug_name' => $drug->name,
                    'total_procured' => $procurements->sum('quantity'),
                    'procurements_count' => $procurements->count()
                ];
            })
            ->sortByDesc('total_procured')
            ->values();
    }
}
