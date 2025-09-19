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

        // Очищаем UTF-8 символы в тексте сообщения
        $cleanText = $this->cleanUtf8($text);
        
        // Очищаем UTF-8 символы в клавиатуре
        $cleanKeyboard = $this->cleanKeyboardUtf8($inlineKeyboard);

        Log::info('TelegramApiService: sending message', [
            'chat_id' => $chatId,
            'text' => $cleanText,
            'keyboard' => $cleanKeyboard,
            'token_exists' => !empty($token),
            'token_preview' => substr($token, 0, 10) . '...' . substr($token, -4)
        ]);
        
        $payload = [
            'chat_id' => $chatId,
            'text' => $cleanText,
            'parse_mode' => 'HTML',
        ];
        
        if (!empty($cleanKeyboard)) {
            $payload['reply_markup'] = [
                'inline_keyboard' => $cleanKeyboard,
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
            $response = Http::telegram()->post($url, $data);
            
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

    /**
     * Очищает и нормализует UTF-8 строку, удаляя поврежденные символы
     */
    private function cleanUtf8(string $text): string
    {
        // Удаляем поврежденные UTF-8 символы
        $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        
        // Если iconv не сработал, используем mb_convert_encoding
        if ($clean === false) {
            $clean = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        
        // Удаляем невидимые символы и лишние пробелы
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $clean);
        $clean = trim($clean);
        
        return $clean ?: 'Сообщение';
    }

    /**
     * Очищает UTF-8 символы в клавиатуре
     */
    private function cleanKeyboardUtf8(array $keyboard): array
    {
        $cleanKeyboard = [];
        
        foreach ($keyboard as $row) {
            $cleanRow = [];
            foreach ($row as $button) {
                if (isset($button['text'])) {
                    $button['text'] = $this->cleanUtf8($button['text']);
                }
                $cleanRow[] = $button;
            }
            $cleanKeyboard[] = $cleanRow;
        }
        
        return $cleanKeyboard;
    }
}
