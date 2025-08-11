<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class VaccinationType extends Model
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
        'drugs' => 'Невозможно удалить тип вакцинации, так как с ним связаны препараты',
        'vaccinations' => 'Невозможно удалить тип вакцинации, так как с ним связаны вакцинации',
    ];

    public function drugs()
    {
        return $this->belongsToMany(Drug::class, 'vaccination_type_drugs')
            ->using(VaccinationTypeDrug::class)
            ->withPivot('dosage')
            ->withTimestamps();
    }

    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class);
    }
}