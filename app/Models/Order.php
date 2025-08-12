<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Order extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'client_id',
        'pet_id',
        'status_id',
        'branch_id',
        'manager_id',
        'notes',
        'total',
        'is_paid',
        'closed_at'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'is_paid' => 'boolean',
        'closed_at' => 'datetime'
    ];

    protected $deleteDependencies = [
        // Убираем проверку на items - они будут удаляться каскадно
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
        return $this->items->where('item_type', 'App\Models\Service');
    }

    public function drugs()
    {
        return $this->items->where('item_type', 'App\Models\Drug');
    }

    public function labTests()
    {
        return $this->items->where('item_type', 'App\Models\LabTest');
    }

    public function vaccinations()
    {
        return $this->items->where('item_type', 'App\Models\Vaccination');
    }

    public function servicesTotal()
    {
        return $this->services()->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    public function drugsTotal()
    {
        return $this->drugs()->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    public function labTestsTotal()
    {
        return $this->labTests()->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    public function vaccinationsTotal()
    {
        return $this->vaccinations()->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
    }
}
