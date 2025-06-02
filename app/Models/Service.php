<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Service extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer'
    ];

    public function orders()
    {
        return $this->morphMany(OrderItem::class, 'item');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_service');
    }

    public function visits()
    {
        return $this->hasMany(VisitService::class);
    }
}
