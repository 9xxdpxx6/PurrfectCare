<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasDeleteDependenciesCheck;

class LabTestResult extends Model
{
    use HasFactory, Filterable, HasDeleteDependenciesCheck;

    /**
     * Документация индексов таблицы lab_test_results
     *
     * - lab_test_id (FK)
     * - lab_test_param_id (FK)
     * - value (decimal)
     */
    protected array $indexes = [
        'lab_test_id',
        'lab_test_param_id',
        'value',
    ];

    protected $fillable = [
        'lab_test_id',
        'lab_test_param_id',
        'value',
        'notes'
    ];

    protected $casts = [
        'value' => 'decimal:2'
    ];

    protected $deleteDependencies = [
        // Убираем проверки - результаты будут удаляться каскадно при удалении исследования или параметра
    ];

    public function labTest()
    {
        return $this->belongsTo(LabTest::class);
    }

    public function labTestParam()
    {
        return $this->belongsTo(LabTestParam::class);
    }
}
