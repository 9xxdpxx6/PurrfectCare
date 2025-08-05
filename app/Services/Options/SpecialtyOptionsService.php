<?php

namespace App\Services\Options;

use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Specialty::query();
        return $this->buildOptions($request, $query, [
            'model' => Specialty::class
        ]);
    }
}
