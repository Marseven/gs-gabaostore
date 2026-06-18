<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntreeRequest;
use App\Http\Requests\StoreSortieRequest;
use App\Http\Requests\UpdateMouvementRequest;
use App\Http\Resources\MouvementResource;
use App\Models\Mouvement;
use App\Services\MouvementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MouvementController extends Controller
{
    public function __construct(private readonly MouvementService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Mouvement::class);

        $query = Mouvement::query()->with(['article', 'user']);

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }
        if ($request->filled('article_id')) {
            $query->where('article_id', $request->integer('article_id'));
        }
        if ($request->filled('livreur')) {
            $query->where('livreur', 'like', '%'.$request->string('livreur').'%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date_mouvement', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date_mouvement', '<=', $request->date('date_to'));
        }
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function (Builder $q) use ($search) {
                $q->where('destination', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhere('livreur', 'like', "%{$search}%")
                    ->orWhereHas('article', function (Builder $a) use ($search) {
                        $a->where('reference', 'like', "%{$search}%")
                            ->orWhere('designation', 'like', "%{$search}%");
                    });
            });
        }

        return MouvementResource::collection(
            $query->latest('date_mouvement')->latest('id')->paginate($request->integer('per_page', 20))
        );
    }

    public function storeEntree(StoreEntreeRequest $request): JsonResponse
    {
        $this->authorize('create', Mouvement::class);

        $data = $request->validated();
        $data['type'] = 'entree';

        $mouvement = $this->service->creer($data, $request->user()->id);

        return (new MouvementResource($mouvement->load(['article', 'user'])))
            ->response()->setStatusCode(201);
    }

    public function storeSortie(StoreSortieRequest $request): JsonResponse
    {
        $this->authorize('create', Mouvement::class);

        $data = $request->validated();
        $data['type'] = 'sortie';

        $mouvement = $this->service->creer($data, $request->user()->id);

        return (new MouvementResource($mouvement->load(['article', 'user'])))
            ->response()->setStatusCode(201);
    }

    public function update(UpdateMouvementRequest $request, Mouvement $mouvement): MouvementResource
    {
        $this->authorize('update', $mouvement);

        $mouvement = $this->service->mettreAJour($mouvement, $request->validated());

        return new MouvementResource($mouvement->load(['article', 'user']));
    }

    public function destroy(Mouvement $mouvement): JsonResponse
    {
        $this->authorize('delete', $mouvement);

        $this->service->supprimer($mouvement);

        return response()->json(['message' => 'Mouvement supprimé, stock recalculé.']);
    }
}
