<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Pet extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'name',
        'breed_id',
        'birthdate',
        'client_id',
        'temperature',
        'weight',
        'gender'
    ];

    protected $casts = [
        'birthdate' => 'date',
        'temperature' => 'decimal:2',
        'weight' => 'decimal:2'
    ];

    /**
     * Индексы для оптимизации производительности
     */
    protected $indexes = [
        'name',
        'client_id',
        'breed_id',
        'gender',
        'birthdate'
    ];

    protected $deleteDependencies = [
        'visits' => 'Невозможно удалить питомца, так как с ним связаны приёмы',
        'vaccinations' => 'Невозможно удалить питомца, так как с ним связаны вакцинации',
        'labTests' => 'Невозможно удалить питомца, так как с ним связаны лабораторные исследования',
        'orders' => 'Невозможно удалить питомца, так как с ним связаны заказы',
    ];

    public function breed()
    {
        return $this->belongsTo(Breed::class);
    }

    public function species()
    {
        return $this->hasOneThrough(Species::class, Breed::class, 'id', 'id', 'breed_id', 'species_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class);
    }

    public function labTests()
    {
        return $this->hasMany(LabTest::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
