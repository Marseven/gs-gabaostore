<?php

namespace App\Http\Controllers;

use App\Exports\MouvementsExport;
use App\Exports\StockExport;
use App\Models\Article;
use App\Models\Mouvement;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function mouvements(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', Mouvement::class);

        $filters = $request->only(['type', 'article_id', 'livreur', 'date_from', 'date_to', 'search']);

        return Excel::download(
            new MouvementsExport($filters),
            'mouvements_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function stock(): BinaryFileResponse
    {
        $this->authorize('viewAny', Article::class);

        return Excel::download(
            new StockExport(),
            'stock_'.now()->format('Ymd_His').'.xlsx'
        );
    }
}
