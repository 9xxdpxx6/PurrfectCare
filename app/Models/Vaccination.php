<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Vaccination extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'pet_id',
        'veterinarian_id',
        'administered_at',
        'next_due'
    ];

    protected $casts = [
        'administered_at' => 'date',
        'next_due' => 'date'
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function veterinarian()
    {
        return $this->belongsTo(Employee::class, 'veterinarian_id');
    }

    public function drugs()
    {
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
