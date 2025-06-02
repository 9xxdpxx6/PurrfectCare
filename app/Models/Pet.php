<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Pet extends Model
{
    use HasFactory, Filterable;

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

    public function breed()
    {
        return $this->belongsTo(Breed::class);
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
