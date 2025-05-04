<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function veterinarians() {
        return $this->hasMany(Veterinarian::class);
    }
    public function services() {
        return $this->hasMany(Service::class);
    }
}
