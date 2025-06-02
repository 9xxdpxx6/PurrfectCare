<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class DrugProcurement extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'supplier_id',
        'drug_id',
        'delivery_date',
        'price',
        'quantity'
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'price' => 'decimal:2',
        'quantity' => 'integer'
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
