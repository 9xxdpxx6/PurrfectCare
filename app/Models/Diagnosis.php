<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Diagnosis extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'visit_id',
        'dictionary_diagnosis_id',
        'custom_diagnosis',
        'treatment_plan'
    ];

    protected $deleteDependencies = [
        'visit' => 'Невозможно удалить диагноз, так как с ним связан приём',
        'dictionaryDiagnosis' => 'Невозможно удалить диагноз, так как с ним связан диагноз из словаря',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function dictionaryDiagnosis()
    {
        return $this->belongsTo(DictionaryDiagnosis::class);
    }

    /**
     * Получить название диагноза (из справочника или кастомный)
     */
    public function getName()
    {
        if ($this->dictionary_diagnosis_id && $this->dictionaryDiagnosis) {
            return $this->dictionaryDiagnosis->name;
        }
        
        return $this->custom_diagnosis;
    }
}
