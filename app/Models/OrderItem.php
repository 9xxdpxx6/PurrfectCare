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
        'quantity' => 'decimal:2',
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
        return $this->morphTo('item', 'item_type', 'item_id');
    }

    // Альтернативный метод с правильным именем
    public function itemable()
    {
        return $this->morphTo('itemable', 'item_type', 'item_id');
    }

    // Добавляем удобный метод для получения названия элемента
    public function getItemNameAttribute()
    {
        try {
            // Отладочная информация для LabTest
            if ($this->item_type === 'App\Models\LabTest') {
                \Log::info('OrderItem getItemNameAttribute debug', [
                    'order_item_id' => $this->id,
                    'item_type' => $this->item_type,
                    'item_id' => $this->item_id,
                    'itemable_loaded' => $this->relationLoaded('itemable'),
                    'item_loaded' => $this->relationLoaded('item'),
                    'has_itemable' => $this->itemable ? 'yes' : 'no',
                    'has_item' => $this->item ? 'yes' : 'no'
                ]);
            }
            
            // Пробуем получить через itemable
            if ($this->relationLoaded('itemable') && $this->itemable) {
                return $this->itemable->name ?? 'Без названия';
            }
            
            // Пробуем получить через item
            if ($this->relationLoaded('item') && $this->item) {
                return $this->item->name ?? 'Без названия';
            }
            
            // Загружаем отношение если оно не загружено
            $itemable = $this->itemable;
            if ($itemable) {
                // Для LabTest нужно загрузить labTestType если не загружен
                if ($this->item_type === 'App\Models\LabTest' && $itemable instanceof \App\Models\LabTest) {
                    if (!$itemable->relationLoaded('labTestType')) {
                        \Log::info('Loading labTestType for LabTest', ['lab_test_id' => $itemable->id]);
                        $itemable->load('labTestType:id,name');
                    }
                }
                $name = $itemable->name ?? 'Без названия';
                \Log::info('Final name for LabTest', ['name' => $name]);
                return $name;
            }
            
            return 'Элемент не найден';
        } catch (\Exception $e) {
            \Log::error('Ошибка получения названия элемента заказа', [
                'order_item_id' => $this->id,
                'item_type' => $this->item_type,
                'item_id' => $this->item_id,
                'error' => $e->getMessage()
            ]);
            
            return 'Ошибка загрузки названия';
        }
    }

    public function getTotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
}
