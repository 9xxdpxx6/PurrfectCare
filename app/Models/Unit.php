<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Unit extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'name',
        'symbol'
    ];

    public function labTestParams()
    {
        return $this->hasMany(LabTestParam::class);
    }

    public function drugs()
    {
        return $this->belongsToMany(Drug::class, 'drug_unit');
    }
}
