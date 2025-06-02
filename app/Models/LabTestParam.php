<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class LabTestParam extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'lab_test_type_id',
        'name',
        'description',
        'unit_id'
    ];

    public function labTestType()
    {
        return $this->belongsTo(LabTestType::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function results()
    {
        return $this->hasMany(LabTestResult::class);
    }
}
