<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Drug extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'name',
        'price',
        'prescription_required',
        'unit_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'prescription_required' => 'boolean',
    ];

    protected $deleteDependencies = [
        'orders' => 'Невозможно удалить препарат, так как он используется в заказах',
        'unit' => 'Невозможно удалить препарат, так как он связан с единицей измерения',
    ];

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_drug')->withPivot('quantity');
    }

    public function procurements()
    {
        return $this->hasMany(DrugProcurement::class);
    }

    public function orders()
    {
        return $this->morphMany(OrderItem::class, 'item');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Scope для получения препаратов, доступных в конкретном филиале
     */
    public function scopeAvailableInBranch($query, $branchId)
    {
        return $query->whereHas('branches', function($q) use ($branchId) {
            $q->where('branch_id', $branchId)->where('quantity', '>', 0);
        });
    }
}
