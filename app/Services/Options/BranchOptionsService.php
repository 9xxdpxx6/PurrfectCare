<?php

namespace App\Services\Options;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Branch::query();
        return $this->buildOptions($request, $query, [
            'model' => Branch::class
        ]);
    }
} 