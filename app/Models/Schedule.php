<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;

class Schedule extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'veterinarian_id',
        'branch_id',
        'shift_starts_at',
        'shift_ends_at'
    ];

    protected $casts = [
        'shift_starts_at' => 'datetime',
        'shift_ends_at' => 'datetime'
    ];

    public function veterinarian()
    {
        return $this->belongsTo(Employee::class, 'veterinarian_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
}
