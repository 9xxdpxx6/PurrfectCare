<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\Traits\Filterable;

class VaccinationDrug extends Pivot
{
    use HasFactory, Filterable;

    protected $table = 'vaccination_drugs';

    protected $fillable = [
        'vaccination_id',
        'drug_id',
        'batch_number',
        'dosage'
    ];

    protected $casts = [
        'dosage' => 'decimal:2'
    ];

    public function vaccination()
    {
        return $this->belongsTo(Vaccination::class);
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function getBatchInfoAttribute()
    {
        return "Серия: {$this->batch_number}, Доза: {$this->dosage}";
    }
}
