<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class DictionaryDiagnosis extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'name',
        'description'
    ];

    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class);
    }
}
