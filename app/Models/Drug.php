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
        'expiry_date',
        'manufacture_date',
        'packaging_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'expiry_date' => 'date',
        'manufacture_date' => 'date',
        'packaging_date' => 'date'
    ];

    public function procurements()
    {
        return $this->hasMany(DrugProcurement::class);
    }

    public function orders()
    {
        return $this->morphMany(OrderItem::class, 'item');
    }

    public function units()
    {
        return $this->belongsToMany(Unit::class, 'drug_unit');
    }
}
