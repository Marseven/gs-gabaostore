<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Attache un identifiant de corrélation (X-Request-Id) à chaque requête,
 * l'injecte dans le contexte de log et le renvoie dans la réponse.
 * Facilite le traçage de bout en bout d'une requête dans les logs.
 */
class CorrelationId
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-Id') ?: (string) Str::uuid();
        $request->headers->set('X-Request-Id', $requestId);

        // Contexte partagé par tous les logs de cette requête.
        Log::shareContext([
            'request_id' => $requestId,
            'user_id' => optional($request->user())->id,
        ]);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
