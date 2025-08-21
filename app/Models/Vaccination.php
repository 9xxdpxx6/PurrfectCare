<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Vaccination extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'vaccination_type_id',
        'pet_id',
        'veterinarian_id',
        'administered_at',
        'next_due'
    ];

    protected $casts = [
        'administered_at' => 'date',
        'next_due' => 'date'
    ];

    /**
     * Индексы для оптимизации производительности
     */
    protected $indexes = [
        'pet_id',
        'veterinarian_id',
        'administered_at',
        'next_due'
    ];

    protected $deleteDependencies = [
        'orders' => 'Невозможно удалить вакцинацию, так как она используется в заказах',
    ];

    public function vaccinationType()
    {
        return $this->belongsTo(VaccinationType::class);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function veterinarian()
    {
        return $this->belongsTo(Employee::class, 'veterinarian_id');
    }

    // Получить препараты через тип вакцинации (helper метод)
    public function getDrugsAttribute()
    {
        return $this->vaccinationType ? $this->vaccinationType->drugs : collect();
    }

    public function orders()
    {
        return $this->morphMany(OrderItem::class, 'item');
    }
}
