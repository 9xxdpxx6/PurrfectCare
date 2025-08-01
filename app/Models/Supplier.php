<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Supplier extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'name'
    ];

    protected $deleteDependencies = [
        'procurements' => 'Невозможно удалить поставщика, так как с ним связаны закупки',
    ];

    public function procurements()
    {
        return $this->hasMany(DrugProcurement::class);
    }
}
