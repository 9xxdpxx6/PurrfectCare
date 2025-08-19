<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Bot\TelegramBotService;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(private TelegramBotService $botService)
    {
    }

    public function handle(Request $request): JsonResponse
    {
        $update = $request->all();
        
        // Логируем входящее сообщение
        Log::info('Telegram webhook received', [
            'update' => $update,
            'headers' => $request->headers->all()
        ]);
        
        try {
            $this->botService->handleUpdate($update);
            Log::info('Telegram update processed successfully');
        } catch (\Throwable $e) {
            Log::error('Telegram update processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return response()->json(['ok' => true]);
    }
}


