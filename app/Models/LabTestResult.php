<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class LabTestResult extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'lab_test_id',
        'lab_test_param_id',
        'value',
        'notes'
    ];

    protected $casts = [
        'value' => 'decimal:2'
    ];

    protected $deleteDependencies = [
        'labTest' => 'Невозможно удалить результат лабораторного исследования, так как с ним связано исследование',
        'labTestParam' => 'Невозможно удалить результат лабораторного исследования, так как с ним связан параметр',
    ];

    public function labTest()
    {
        return $this->belongsTo(LabTest::class);
    }

    public function labTestParam()
    {
        return $this->belongsTo(LabTestParam::class);
    }
}
