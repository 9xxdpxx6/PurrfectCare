<?php

namespace App\Services\Options;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Supplier::query();
        return $this->buildOptions($request, $query, [
            'model' => Supplier::class
        ]);
    }
} 