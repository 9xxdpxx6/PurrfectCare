<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Pet;

class BelongsToClient implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Если питомец не выбран (nullable), валидация проходит
        if (empty($value)) {
            return;
        }
        
        $clientId = request()->input('client_id');
        
        if (!$clientId) {
            $fail('Клиент должен быть выбран.');
            return;
        }
        
        $pet = Pet::where('id', $value)
                  ->where('client_id', $clientId)
                  ->first();
        
        if (!$pet) {
            $fail('Выбранный питомец не принадлежит указанному клиенту.');
        }
    }
}
