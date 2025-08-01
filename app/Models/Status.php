<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Status extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'name',
        'color'
    ];

    protected $deleteDependencies = [
        'visits' => 'Невозможно удалить статус, так как с ним связаны приёмы',
        'orders' => 'Невозможно удалить статус, так как с ним связаны заказы',
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
