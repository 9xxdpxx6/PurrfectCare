<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class LabTestType extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'name',
        'description'
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
