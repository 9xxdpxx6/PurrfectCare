<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class OrderItem extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

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

    /**
     * Индексы для оптимизации производительности
     */
    protected $indexes = [
        'order_id',
        'item_type',
        'item_id'
    ];

    protected $deleteDependencies = [
        // Убираем проверку на order - элементы заказа будут удаляться каскадно при удалении заказа
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
