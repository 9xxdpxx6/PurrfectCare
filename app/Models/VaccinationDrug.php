<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class VaccinationDrug extends Pivot {
    use HasFactory;

    protected $table = 'vaccination_drug';

    public function getBatchInfoAttribute() {
        return "Серия: {$this->batch_number}, Доза: {$this->dose}";
    }
}
