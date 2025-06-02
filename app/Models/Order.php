<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Order extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'client_id',
        'pet_id',
        'status_id',
        'branch_id',
        'manager_id',
        'notes',
        'total'
    ];

    protected $casts = [
        'total' => 'decimal:2'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function services()
    {
        return $this->morphMany(Service::class, 'item');
    }

    public function drugs()
    {
        return $this->morphMany(Drug::class, 'item');
    }

    public function labTests()
    {
        return $this->morphMany(LabTest::class, 'item');
    }
}
