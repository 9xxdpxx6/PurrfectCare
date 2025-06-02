<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Supplier extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'name'
    ];

    public function procurements()
    {
        return $this->hasMany(DrugProcurement::class);
    }
}
