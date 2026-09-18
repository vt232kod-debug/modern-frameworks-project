<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(append: [\App\Http\Middleware\UnescapedUnicodeJson::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Always answer API requests with JSON (validation errors, 404, etc.)
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*'));
        // HTTP errors of the API (400, 404, ...) return only a message, without a debug trace
        $exceptions->render(fn (Symfony\Component\HttpKernel\Exception\HttpException $e, $request) => $request->is('api/*')
            ? response()->json(
                ['message' => $e instanceof Symfony\Component\HttpKernel\Exception\NotFoundHttpException ? 'Resource not found.' : $e->getMessage()],
                $e->getStatusCode(),
                $e->getHeaders(),
            )
            : null);
    })->create();
