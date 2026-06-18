<?php

namespace App\Services;

use App\Exceptions\StockInsuffisantException;
use App\Models\Article;
use App\Models\Mouvement;
use Illuminate\Support\Facades\DB;

class MouvementService
{
    /**
     * Crée un mouvement et met à jour le stock de l'article dans une transaction unique.
     *
     * @param  array{article_id:int,type:string,quantite:int,date_mouvement?:string,livreur?:string,destination?:string,source?:string,note?:string}  $data
     */
    public function creer(array $data, int $userId): Mouvement
    {
        return DB::transaction(function () use ($data, $userId) {
            // Verrou pessimiste pour éviter les courses sur stock_actuel.
            $article = Article::lockForUpdate()->findOrFail($data['article_id']);

            $this->appliquerStock($article, $data['type'], (int) $data['quantite']);

            return Mouvement::create([
                'article_id' => $article->id,
                'type' => $data['type'],
                'quantite' => $data['quantite'],
                'prix' => $data['prix'] ?? null,
                'date_mouvement' => $data['date_mouvement'] ?? now()->toDateString(),
                'livreur' => $data['livreur'] ?? null,
                'destination' => $data['destination'] ?? null,
                'telephone' => $data['telephone'] ?? null,
                'vendeur' => $data['vendeur'] ?? null,
                'mode_remise' => $data['mode_remise'] ?? 'livraison',
                'recu_par' => $data['recu_par'] ?? null,
                'statut_livraison' => $data['statut_livraison'] ?? null,
                'commentaire_statut' => $data['commentaire_statut'] ?? null,
                'source' => $data['source'] ?? null,
                'note' => $data['note'] ?? null,
                'user_id' => $userId,
            ]);
        });
    }

    /**
     * Met à jour un mouvement existant (admin) et recalcule le stock de l'article concerné.
     *
     * @param  array<string,mixed>  $data
     */
    public function mettreAJour(Mouvement $mouvement, array $data): Mouvement
    {
        return DB::transaction(function () use ($mouvement, $data) {
            $article = Article::lockForUpdate()->findOrFail($mouvement->article_id);

            // Champs simplement écrasés s'ils sont fournis, sinon conservés.
            $champs = [
                'livreur', 'destination', 'telephone', 'vendeur', 'mode_remise',
                'recu_par', 'statut_livraison', 'commentaire_statut', 'source', 'note', 'prix',
            ];
            $maj = [
                'type' => $data['type'] ?? $mouvement->type,
                'quantite' => $data['quantite'] ?? $mouvement->quantite,
                'date_mouvement' => $data['date_mouvement'] ?? $mouvement->date_mouvement,
            ];
            foreach ($champs as $champ) {
                $maj[$champ] = array_key_exists($champ, $data) ? $data[$champ] : $mouvement->{$champ};
            }
            $mouvement->update($maj);

            $this->recalculer($article);

            return $mouvement->fresh();
        });
    }

    /**
     * Supprime un mouvement (admin) et recalcule le stock de l'article concerné.
     */
    public function supprimer(Mouvement $mouvement): void
    {
        DB::transaction(function () use ($mouvement) {
            $article = Article::lockForUpdate()->findOrFail($mouvement->article_id);
            $mouvement->delete();
            $this->recalculer($article);
        });
    }

    /**
     * Recalcule stock_actuel d'un article à partir de stock_initial + somme des mouvements.
     */
    public function recalculer(Article $article): void
    {
        if (! $article->suivi_stock) {
            return;
        }

        $entrees = (int) $article->mouvements()->where('type', 'entree')->sum('quantite');
        $sorties = (int) $article->mouvements()->where('type', 'sortie')->sum('quantite');

        $article->stock_actuel = $article->stock_initial + $entrees - $sorties;
        $article->save();
    }

    /**
     * Applique l'effet d'un mouvement sur le stock d'un article suivi.
     * Bloque les sorties supérieures au stock disponible.
     */
    private function appliquerStock(Article $article, string $type, int $quantite): void
    {
        if (! $article->suivi_stock) {
            return; // Article non suivi : aucun impact, jamais bloqué.
        }

        if ($type === 'entree') {
            $article->stock_actuel += $quantite;
            $article->save();

            return;
        }

        // Sortie
        if ($quantite > $article->stock_actuel) {
            throw new StockInsuffisantException($article->stock_actuel, $quantite);
        }

        $article->stock_actuel -= $quantite;
        $article->save();
    }
}
