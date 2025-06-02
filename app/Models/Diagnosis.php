<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Diagnosis extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'visit_id',
        'dictionary_diagnosis_id',
        'custom_diagnosis',
        'treatment_plan'
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function dictionaryDiagnosis()
    {
        return $this->belongsTo(DictionaryDiagnosis::class);
    }
}
