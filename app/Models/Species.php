<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Species extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'name'
    ];

    public function breeds()
    {
        return $this->hasMany(Breed::class);
    }
}
