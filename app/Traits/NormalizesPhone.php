<?php

namespace App\Traits;

trait NormalizesPhone
{
    protected function normalizePhone(string $phone): string
    {
        // Убираем все символы кроме цифр (включая пробелы, дефисы, скобки, точки, плюсы)
        $digits = preg_replace('/[^0-9]/', '', $phone);
        
        // Если номер начинается с 8, заменяем на 7
        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7' . substr($digits, 1);
        }
        
        // Если номер начинается с 7 и имеет 11 цифр - оставляем как есть
        if (strlen($digits) === 11 && $digits[0] === '7') {
            return $digits;
        }
        
        // Если номер имеет 10 цифр и начинается с 9 - добавляем 7 в начало
        if (strlen($digits) === 10 && $digits[0] === '9') {
            return '7' . $digits;
        }
        
        // Если номер имеет 10 цифр и начинается с 8 - добавляем 7 в начало
        if (strlen($digits) === 10 && $digits[0] === '8') {
            return '7' . $digits;
        }
        
        // Если номер имеет 10 цифр и начинается с 6, 4, 3, 5 - добавляем 7 в начало
        if (strlen($digits) === 10 && in_array($digits[0], ['6', '4', '3', '5'])) {
            return '7' . $digits;
        }
        
        // Если номер имеет 10 цифр и начинается с 7 - добавляем 7 в начало
        if (strlen($digits) === 10 && $digits[0] === '7') {
            return '7' . $digits;
        }
        
        return $digits;
    }
    
    protected function validatePhone(string $phone): bool
    {
        $normalized = $this->normalizePhone($phone);
        
        // Проверяем, что номер имеет правильную длину и формат
        if (strlen($normalized) !== 11) {
            return false;
        }
        
        // Проверяем, что номер начинается с 7
        if ($normalized[0] !== '7') {
            return false;
        }
        
        // Проверяем, что вторая цифра (код оператора) валидна
        $operatorCode = $normalized[1];
        $validOperatorCodes = ['9', '4', '8', '3', '5', '6'];
        
        return in_array($operatorCode, $validOperatorCodes);
    }
}
