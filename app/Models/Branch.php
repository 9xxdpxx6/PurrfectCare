<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Branch extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

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

    protected $deleteDependencies = [
        'veterinarians' => 'Невозможно удалить филиал, так как с ним связаны сотрудники',
        'services' => 'Невозможно удалить филиал, так как с ним связаны услуги',
    ];

    public function veterinarians() {
        return $this->hasMany(Employee::class, 'veterinarian_id');
    }

    public function services() {
        return $this->belongsToMany(Service::class, 'branch_service');
    }
}
