<?php

namespace App\Services\Bot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramApiService
{
    public function sendMessage(string $chatId, string $text, array $inlineKeyboard = []): void
    {
        $token = config('services.telegram.bot_token');
        if (!$token) {
            Log::error('TelegramApiService: bot token not configured');
            return;
        }

        Log::info('TelegramApiService: sending message', [
            'chat_id' => $chatId,
            'text' => $text,
            'keyboard' => $inlineKeyboard,
            'token_exists' => !empty($token),
            'token_preview' => substr($token, 0, 10) . '...' . substr($token, -4)
        ]);
        
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];
        
        if (!empty($inlineKeyboard)) {
            $payload['reply_markup'] = [
                'inline_keyboard' => $inlineKeyboard,
            ];
        }

        $this->dispatchRequest('sendMessage', $payload);
    }

    public function answerCallback(string $callbackId): void
    {
        $this->dispatchRequest('answerCallbackQuery', [
            'callback_query_id' => $callbackId,
        ]);
    }

    public function deleteMessage(string $chatId, int $messageId): void
    {
        $this->dispatchRequest('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    protected function dispatchRequest(string $method, array $data): void
    {
        $token = config('services.telegram.bot_token');
        if (!$token) {
            Log::error('TelegramApiService: bot token not configured');
            return;
        }

        $url = "https://api.telegram.org/bot{$token}/{$method}";
        
        Log::info("TelegramApiService: dispatching {$method} request", [
            'url' => $url,
            'data' => $data,
            'token_length' => strlen($token)
        ]);
        
        // Делаем синхронный запрос для получения ответа
        try {
            $response = Http::timeout(5)->post($url, $data);
            
            if ($response->successful()) {
                $responseData = $response->json();
                Log::info("TelegramApiService: {$method} request successful", [
                    'status' => $response->status(),
                    'response' => $responseData,
                    'ok' => $responseData['ok'] ?? 'unknown'
                ]);
                
                // Проверяем, что Telegram подтвердил отправку
                if (isset($responseData['ok']) && $responseData['ok'] === true) {
                    Log::info("TelegramApiService: message sent successfully", [
                        'method' => $method,
                        'message_id' => $responseData['result']['message_id'] ?? 'unknown'
                    ]);
                } else {
                    Log::error("TelegramApiService: Telegram returned error", [
                        'method' => $method,
                        'response' => $responseData
                    ]);
                }
            } else {
                Log::error("TelegramApiService: {$method} request failed", [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'headers' => $response->headers()
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("TelegramApiService: {$method} request error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
