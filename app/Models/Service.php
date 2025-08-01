<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Service extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer'
    ];

    protected $deleteDependencies = [
        'orders' => 'Невозможно удалить услугу, так как она используется в заказах',
        'branches' => 'Невозможно удалить услугу, так как она привязана к филиалам',
    ];

    public function orders()
    {
        return $this->morphMany(OrderItem::class, 'item');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_service');
    }
}
