<?php

namespace App\Services\Options;

use App\Models\User;
use Illuminate\Http\Request;

class ClientOptionsService extends BaseOptionsService
{
    public function getOptions(Request $request)
    {
        $query = User::query();
        
        // Применяем поиск по имени
        $search = $request->input('q');
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        
        return $this->buildOptions($request, $query, [
            'model' => User::class
        ]);
    }
} 