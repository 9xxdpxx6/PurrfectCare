<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Specialty extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'name',
        'is_veterinarian'
    ];

    protected $casts = [
        'is_veterinarian' => 'boolean'
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_specialty');
    }
}
