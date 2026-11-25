<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        // AÑADIMOS LA EXCEPCIÓN DEL CSRF AQUÍ:
        $middleware->validateCsrfTokens(except: [
            '*'
        ]);

        // Alias para middleware de autenticación API por sesión
        $middleware->alias([
            'api.session' => \App\Http\Middleware\ApiSessionAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Database\QueryException|\PDOException $e, \Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Log::error('Error de base de datos: ' . $e->getMessage());

            $safeMessage = 'Ocurrió un error interno al procesar la solicitud. Por favor, intenta nuevamente más tarde.';
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $safeMessage,
                ], 500);
            }

            if (url()->previous() && url()->previous() !== $request->fullUrl()) {
                return redirect()->back()->with('error', $safeMessage);
            }

            return response($safeMessage, 500);
        });
    })->create();