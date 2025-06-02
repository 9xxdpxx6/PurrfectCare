<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Status extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'name',
        'color'
    ];

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function statusable()
    {
        return $this->morphTo();
    }
}
