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
        // Убираем все символы кроме цифр
        $digits = preg_replace('/[^0-9]/', '', $phone);
        
        // Принимаем номера длиной 10-11 цифр
        if (strlen($digits) < 10 || strlen($digits) > 11) {
            return false;
        }
        
        // Если 10 цифр - добавляем 7 в начало
        if (strlen($digits) === 10) {
            $digits = '7' . $digits;
        }
        
        // Проверяем, что номер начинается с 7 или 8
        if ($digits[0] !== '7' && $digits[0] !== '8') {
            return false;
        }
        
        // Если начинается с 8, заменяем на 7
        if ($digits[0] === '8') {
            $digits = '7' . substr($digits, 1);
        }
        
        // Проверяем, что вторая цифра (код оператора) валидна
        $operatorCode = $digits[1];
        $validOperatorCodes = ['1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        return in_array($operatorCode, $validOperatorCodes);
    }
}
