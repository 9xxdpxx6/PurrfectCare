<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class DrugProcurement extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'supplier_id',
        'drug_id',
        'delivery_date',
        'expiry_date',
        'manufacture_date',
        'packaging_date',
        'price',
        'quantity',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'expiry_date' => 'date',
        'manufacture_date' => 'date',
        'packaging_date' => 'date',
    ];

    protected $deleteDependencies = [
        // Убираем проверки - закупки будут удаляться каскадно при удалении препарата или поставщика
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }
}
