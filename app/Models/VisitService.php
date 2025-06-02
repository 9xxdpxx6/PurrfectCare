<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class VisitService extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'visit_id',
        'service_id',
        'price',
        'notes'
    ];

    protected $casts = [
        'price' => 'decimal:2'
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
} 