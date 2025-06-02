<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Branch extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'opens_at',
        'closes_at'
    ];

    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime'
    ];

    public function veterinarians() {
        return $this->hasMany(Employee::class, 'veterinarian_id');
    }

    public function services() {
        return $this->belongsToMany(Service::class, 'branch_service');
    }
}
