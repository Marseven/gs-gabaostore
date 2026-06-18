<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Article::class);

        $query = Article::query()->with('categorie');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function (Builder $q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }
        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->integer('categorie_id'));
        }
        if ($request->filled('actif')) {
            $query->where('actif', $request->boolean('actif'));
        }

        return ArticleResource::collection(
            $query->orderBy('designation')->paginate($request->integer('per_page', 20))
        );
    }

    public function store(StoreArticleRequest $request): ArticleResource
    {
        $data = $request->validated();
        // Le stock actuel démarre au stock initial.
        $data['stock_actuel'] = $data['stock_initial'] ?? 0;

        $article = Article::create($data);

        return new ArticleResource($article->load('categorie'));
    }

    public function show(Article $article): ArticleResource
    {
        $this->authorize('view', $article);

        return new ArticleResource($article->load('categorie'));
    }

    public function update(UpdateArticleRequest $request, Article $article): ArticleResource
    {
        $data = $request->validated();

        // Si le stock_initial change, on réajuste le stock_actuel par différence
        // (les mouvements restent inchangés).
        if (array_key_exists('stock_initial', $data)) {
            $delta = $data['stock_initial'] - $article->stock_initial;
            $data['stock_actuel'] = $article->stock_actuel + $delta;
        }

        $article->update($data);

        return new ArticleResource($article->load('categorie'));
    }

    public function destroy(Article $article): JsonResponse
    {
        $this->authorize('delete', $article);

        // Désactivation logique (soft) au lieu de suppression.
        $article->update(['actif' => false]);

        return response()->json(['message' => 'Article désactivé.']);
    }
}
