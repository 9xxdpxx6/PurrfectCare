<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, \Illuminate\Auth\AuthenticationException $exception)
    {
        // Если это админский запрос, редиректим на админский логин
        if ($this->isAdminRequest($request)) {
            return redirect()->guest(route('admin.login'));
        }

        // Для остальных запросов используем стандартное поведение
        return $request->expectsJson()
            ? response()->json(['message' => $exception->getMessage()], 401)
            : redirect()->guest(route('client.login'));
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // ValidationException всегда обрабатываем стандартно - не перенаправляем на страницу ошибки
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            \Log::info('ValidationException caught, using standard Laravel handling');
            return parent::render($request, $e);
        }
        
        // AuthenticationException тоже обрабатываем стандартно - чтобы работал редирект на логин
        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            \Log::info('AuthenticationException caught, using standard Laravel handling for redirect');
            return parent::render($request, $e);
        }
        
        // Проверяем, является ли запрос админским
        if ($this->isAdminRequest($request)) {
            \Log::info('Admin request error, using custom error handling', [
                'exception_type' => get_class($e),
                'message' => $e->getMessage()
            ]);
            return $this->renderAdminError($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Проверяет, является ли запрос админским
     */
    protected function isAdminRequest(Request $request): bool
    {
        return $request->is('admin/*') || $request->routeIs('admin.*');
    }

    /**
     * Рендерит страницу ошибки для админки
     */
    protected function renderAdminError(Request $request, Throwable $e)
    {
        // Получаем HTTP статус код
        $statusCode = $this->getStatusCode($e);
        
        // Создаем HTTP исключение с нужным статусом
        $httpException = new HttpException($statusCode, $e->getMessage(), $e);
        
        // Рендерим кастомный шаблон
        return response()->view('admin.errors.error', [
            'exception' => $httpException
        ], $statusCode);
    }

    /**
     * Получает HTTP статус код из исключения
     */
    protected function getStatusCode(Throwable $e): int
    {
        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        }

        // Маппинг типов исключений на HTTP коды
        // ValidationException не обрабатываем - пусть Laravel сам показывает ошибки валидации

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return 401;
        }

        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return 403;
        }

        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return 404;
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return 404;
        }

        // По умолчанию 500
        return 500;
    }
}
