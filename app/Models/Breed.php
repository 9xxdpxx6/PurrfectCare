<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Breed extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'name',
        'species_id'
    ];

    public function species()
    {
        return $this->belongsTo(Species::class);
    }

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }
}
