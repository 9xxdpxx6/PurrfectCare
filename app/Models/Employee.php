<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Traits\Filterable;

class Employee extends Model
{
    use HasFactory, HasRoles, Filterable;

    protected $guard_name = 'admin';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token'
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
}
