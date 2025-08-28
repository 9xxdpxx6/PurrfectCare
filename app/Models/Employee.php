<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;

class Employee extends Authenticatable
{
    use HasFactory, HasRoles, Filterable, HasDeleteDependenciesCheck, Notifiable, AuthenticatableTrait;

    protected $guard_name = 'admin';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $indexes = [
        'name',
        'email',
        'phone',
    ];

    protected $deleteDependencies = [
        'visits' => 'Невозможно удалить сотрудника, так как с ним связаны приёмы',
        'labTests' => 'Невозможно удалить сотрудника, так как с ним связаны лабораторные исследования',
        'vaccinations' => 'Невозможно удалить сотрудника, так как с ним связаны вакцинации',
        'orders' => 'Невозможно удалить сотрудника, так как с ним связаны заказы',
        // Убираем проверку на specialties - они будут удаляться каскадно через pivot таблицу
    ];

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'employee_specialty');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_employee');
    }

    public function visits()
    {
        return $this->hasMany(Visit::class, 'veterinarian_id');
    }

    public function labTests()
    {
        return $this->hasMany(LabTest::class, 'veterinarian_id');
    }

    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class, 'veterinarian_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'manager_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'veterinarian_id');
    }
}
