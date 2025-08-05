<?php

namespace App\Traits;

trait JsonResponse
{
    /**
     * Возвращает успешный JSON ответ
     */
    protected function successResponse($data = null, $message = null): \Illuminate\Http\JsonResponse
    {
        $response = ['success' => true];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        return response()->json($response);
    }

    /**
     * Возвращает ошибку валидации
     */
    protected function validationErrorResponse($errors, $message = 'Ошибка валидации'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], 422);
    }

    /**
     * Возвращает ошибку с сообщением
     */
    protected function errorResponse($message, $code = 500): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }

    /**
     * Возвращает ошибку зависимостей
     */
    protected function dependencyErrorResponse($message): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], 422);
    }
} 