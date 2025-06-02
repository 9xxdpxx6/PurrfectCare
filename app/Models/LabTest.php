<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class LabTest extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'pet_id',
        'lab_test_type_id',
        'veterinarian_id',
        'received_at',
        'completed_at'
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function labTestType()
    {
        return $this->belongsTo(LabTestType::class);
    }

    public function veterinarian()
    {
        return $this->belongsTo(Employee::class, 'veterinarian_id');
    }

    public function results()
    {
        return $this->hasMany(LabTestResult::class);
    }

    public function orders()
    {
        return $this->morphMany(OrderItem::class, 'item');
    }
}
