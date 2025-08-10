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

    // Убираем прямую связь с препаратами, теперь через тип вакцинации
    public function drugs()
    {
        if ($this->vaccinationType) {
            return $this->vaccinationType->drugs();
        }
        
        // Обратная совместимость для старых записей
        return $this->belongsToMany(Drug::class, 'vaccination_drugs')
            ->using(VaccinationDrug::class)
            ->withPivot('batch_number', 'dosage')
            ->withTimestamps();
    }

    public function orders()
    {
        return $this->morphMany(OrderItem::class, 'item');
    }
}
