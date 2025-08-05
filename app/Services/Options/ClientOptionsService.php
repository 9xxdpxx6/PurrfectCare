<?php

namespace App\Services\Options;

use App\Models\User;
use Illuminate\Http\Request;

class ClientOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = User::query();
        return $this->buildOptions($request, $query, [
            'model' => User::class
        ]);
    }
} 