<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class DictionaryDiagnosis extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'name',
        'description'
    ];

    protected $deleteDependencies = [
        'diagnoses' => 'Невозможно удалить диагноз из словаря, так как с ним связаны диагнозы',
    ];

    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class);
    }
}
