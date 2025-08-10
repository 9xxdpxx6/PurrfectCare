<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class VaccinationTypeDrug extends Pivot
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $table = 'vaccination_type_drugs';

    protected $fillable = [
        'vaccination_type_id',
        'drug_id',
        'dosage',
        'batch_template'
    ];

    protected $casts = [
        'dosage' => 'decimal:2'
    ];

    protected $deleteDependencies = [
        'vaccinationType' => 'Невозможно удалить связь типа вакцинации с препаратом, так как с ней связан тип вакцинации',
        'drug' => 'Невозможно удалить связь типа вакцинации с препаратом, так как с ней связан препарат',
    ];

    public function vaccinationType()
    {
        return $this->belongsTo(VaccinationType::class);
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function getDosageInfoAttribute()
    {
        $drugName = $this->drug ? $this->drug->name : 'Неизвестно';
        return "Препарат: {$drugName}, Доза: {$this->dosage}";
    }
}