<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class Visit extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    protected $fillable = [
        'client_id',
        'pet_id',
        'schedule_id',
        'starts_at',
        'status_id',
        'complaints',
        'notes'
    ];

    protected $casts = [
        'starts_at' => 'datetime'
    ];

    protected $deleteDependencies = [
        'diagnoses' => 'Невозможно удалить приём, так как с ним связаны диагнозы',
        'symptoms' => 'Невозможно удалить приём, так как с ним связаны симптомы',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function symptoms()
    {
        return $this->hasMany(Symptom::class);
    }
}
