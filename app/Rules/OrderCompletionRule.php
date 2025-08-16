<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OrderCompletionRule implements ValidationRule
{
    /**
     * Проверяет, что заказ не может быть выполнен, если он не оплачен
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $request = request();
        
        // Если заказ не выполнен, правило проходит
        if (!$request->has('is_closed') || !$request->input('is_closed')) {
            return;
        }
        
        // Если заказ выполнен, проверяем, что он оплачен
        if (!$request->has('is_paid') || !$request->input('is_paid')) {
            $fail('Заказ не может быть выполнен, если он не оплачен.');
        }
    }
}
