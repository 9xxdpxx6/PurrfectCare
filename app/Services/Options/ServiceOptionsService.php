<?php

namespace App\Services\Options;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Service::query();
        return $this->buildOptions($request, $query, [
            'model' => Service::class,
            'include_price' => $request->input('include_price', false)
        ]);
    }
} 