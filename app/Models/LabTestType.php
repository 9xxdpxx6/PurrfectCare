<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class LabTestType extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'name',
        'price',
        'description'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    protected $deleteDependencies = [
        'params' => 'Невозможно удалить тип лабораторного исследования, так как с ним связаны параметры',
        'labTests' => 'Невозможно удалить тип лабораторного исследования, так как с ним связаны исследования',
    ];

    public function params()
    {
        return $this->hasMany(LabTestParam::class);
    }

    public function labTests()
    {
        return $this->hasMany(LabTest::class);
    }
}
