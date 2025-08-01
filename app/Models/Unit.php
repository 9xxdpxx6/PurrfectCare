<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Unit extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'name',
        'symbol'
    ];

    protected $deleteDependencies = [
        'labTestParams' => 'Невозможно удалить единицу измерения, так как с ней связаны параметры лабораторных исследований',
        'drugs' => 'Невозможно удалить единицу измерения, так как с ней связаны препараты',
    ];

    public function labTestParams()
    {
        return $this->hasMany(LabTestParam::class);
    }

    public function drugs()
    {
        return $this->hasMany(Drug::class);
    }
}
