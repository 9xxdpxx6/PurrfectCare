<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Diagnosis extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    /**
     * Документация индексов таблицы diagnoses
     *
     * - visit_id (FK)
     * - dictionary_diagnosis_id (FK)
     * - custom_diagnosis (string)
     */
    protected array $indexes = [
        'visit_id',
        'dictionary_diagnosis_id',
        'custom_diagnosis',
    ];

    protected $fillable = [
        'visit_id',
        'dictionary_diagnosis_id',
        'custom_diagnosis',
        'treatment_plan'
    ];

    protected $deleteDependencies = [
        // Убираем все проверки - диагнозы удаляются каскадно при удалении приёма
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
