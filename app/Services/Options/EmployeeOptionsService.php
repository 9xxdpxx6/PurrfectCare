<?php

namespace App\Services\Options;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeOptionsService extends BaseOptionsService
{
    public function getVeterinarianOptions(Request $request)
    {
        $query = Employee::query()
            ->whereHas('specialties', function($q) {
                $q->where('is_veterinarian', true);
            });
            
        return $this->buildOptions($request, $query, [
            'model' => Employee::class
        ]);
    }

    public function getManagerOptions(Request $request)
    {
        $query = Employee::query();
        return $this->buildOptions($request, $query, [
            'model' => Employee::class
        ]);
    }
} 