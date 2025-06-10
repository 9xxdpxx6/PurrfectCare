<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Drug extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'name',
        'price',
        'quantity',
        'prescription_required',
        'unit_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'prescription_required' => 'boolean',
    ];

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
}
