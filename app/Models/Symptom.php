<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Symptom extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'visit_id',
        'dictionary_symptom_id',
        'custom_symptom',
        'notes'
    ];

    protected $deleteDependencies = [
        // Убираем все проверки - симптомы удаляются каскадно при удалении приёма
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function dictionarySymptom()
    {
        return $this->belongsTo(DictionarySymptom::class);
    }

    /**
     * Получить название симптома (из справочника или кастомный)
     */
    public function getName()
    {
        if ($this->dictionary_symptom_id && $this->dictionarySymptom) {
            return $this->dictionarySymptom->name;
        }
        
        return $this->custom_symptom;
    }
}
