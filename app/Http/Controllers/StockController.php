<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Article::class);

        $query = Article::query()->with('categorie')->where('actif', true)->suivis();

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->integer('categorie_id'));
        }
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        return ArticleResource::collection($query->orderBy('designation')->get());
    }

    public function alertes(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Article::class);

        return ArticleResource::collection(
            Article::with('categorie')->where('actif', true)->enAlerte()->orderBy('designation')->get()
        );
    }
}
