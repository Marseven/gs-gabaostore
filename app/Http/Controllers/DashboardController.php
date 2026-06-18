<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Http\Resources\MouvementResource;
use App\Models\Article;
use App\Models\Mouvement;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Article::class);

        $articlesSuivis = Article::where('actif', true)->suivis()->count();
        $enAlerte = Article::where('actif', true)->enAlerte()->count();
        $mouvementsDuJour = Mouvement::whereDate('date_mouvement', now()->toDateString())->count();

        $alertes = Article::with('categorie')
            ->where('actif', true)
            ->enAlerte()
            ->orderBy('stock_actuel')
            ->limit(20)
            ->get();

        $derniers = Mouvement::with(['article', 'user'])
            ->latest('id')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => [
                'articles_suivis' => $articlesSuivis,
                'en_alerte' => $enAlerte,
                'mouvements_du_jour' => $mouvementsDuJour,
            ],
            'alertes' => ArticleResource::collection($alertes),
            'derniers_mouvements' => MouvementResource::collection($derniers),
        ]);
    }
}
