<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class DictionarySymptom extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'name',
        'description'
    ];

    public function symptoms()
    {
        return $this->hasMany(Symptom::class);
    }
}
