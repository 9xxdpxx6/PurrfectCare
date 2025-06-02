<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class LabTestResult extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'lab_test_id',
        'lab_test_param_id',
        'value',
        'notes'
    ];

    protected $casts = [
        'value' => 'decimal:2'
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
