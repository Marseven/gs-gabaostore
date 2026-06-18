<?php

use App\Http\Middleware\CorrelationId;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Identifiant de corrélation sur toutes les requêtes API (traçabilité logs).
        $middleware->api(append: [
            CorrelationId::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Toujours répondre en JSON sur le préfixe /api (format d'erreur standardisé).
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
