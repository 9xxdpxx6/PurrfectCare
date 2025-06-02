<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class OrderItem extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'order_id',
        'item_type',
        'item_id',
        'quantity',
        'unit_price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function item()
    {
        return $this->morphTo();
    }

    public function getTotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
}
