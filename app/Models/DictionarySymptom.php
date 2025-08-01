<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class DictionarySymptom extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'name',
        'description'
    ];

    protected $deleteDependencies = [
        'symptoms' => 'Невозможно удалить симптом из словаря, так как с ним связаны симптомы',
    ];

    public function symptoms()
    {
        return $this->hasMany(Symptom::class);
    }
}
