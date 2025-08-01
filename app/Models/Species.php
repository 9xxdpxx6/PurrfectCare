<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Species extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'name'
    ];

    protected $deleteDependencies = [
        'breeds' => 'Невозможно удалить вид животного, так как с ним связаны породы',
    ];

    public function breeds()
    {
        return $this->hasMany(Breed::class);
    }
}
