<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Breed extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'name',
        'species_id'
    ];

    protected $deleteDependencies = [
        'pets' => 'Невозможно удалить породу, так как с ней связаны питомцы',
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
