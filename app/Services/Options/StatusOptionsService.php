<?php

namespace App\Services\Options;

use App\Models\Status;
use Illuminate\Http\Request;

class StatusOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = Status::query();
        return $this->buildOptions($request, $query, [
            'model' => Status::class
        ]);
    }
} 