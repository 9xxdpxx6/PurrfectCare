<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Specialty extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'name',
        'is_veterinarian'
    ];

    protected $casts = [
        'is_veterinarian' => 'boolean'
    ];

    protected $deleteDependencies = [
        'employees' => 'Невозможно удалить специальность, так как с ней связаны сотрудники',
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_specialty');
    }
}
