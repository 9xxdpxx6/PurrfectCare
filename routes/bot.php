<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Bot\TelegramWebhookController;

// Telegram webhook endpoint
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->name('bot.telegram.webhook');

// Тестовый маршрут для проверки нормализации телефонов
Route::get('/test-phone-normalization', function () {
    $testPhones = [
        '7650 1 -478 985',
        '+76501478985',
        '76501478985',
        '8 650 147 89 85',
        '+7 (650) 147-89-85'
    ];
    
    $results = [];
    foreach ($testPhones as $phone) {
        $normalized = app(\App\Traits\NormalizesPhone::class)->normalizePhone($phone);
        $isValid = app(\App\Traits\NormalizesPhone::class)->validatePhone($phone);
        
        $results[] = [
            'original' => $phone,
            'normalized' => $normalized,
            'is_valid' => $isValid,
            'length' => strlen($normalized)
        ];
    }
    
    return response()->json($results);
});


