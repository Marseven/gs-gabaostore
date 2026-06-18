<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Levée lorsqu'une sortie dépasse le stock disponible d'un article suivi.
 * Rendue en HTTP 422 avec un format d'erreur cohérent avec la validation Laravel.
 */
class StockInsuffisantException extends RuntimeException
{
    public function __construct(
        public readonly int $disponible,
        public readonly int $demande,
    ) {
        parent::__construct("Stock insuffisant : disponible {$disponible}, demandé {$demande}.");
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'quantite' => [$this->getMessage()],
            ],
        ], 422);
    }
}
