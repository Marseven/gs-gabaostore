<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MouvementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'article_id' => $this->article_id,
            'article' => new ArticleResource($this->whenLoaded('article')),
            'type' => $this->type,
            'quantite' => $this->quantite,
            'prix' => $this->prix,
            'numero' => $this->numero,
            'date_mouvement' => $this->date_mouvement?->format('Y-m-d'),
            'livreur' => $this->livreur,
            'destination' => $this->destination,
            'telephone' => $this->telephone,
            'vendeur' => $this->vendeur,
            'mode_remise' => $this->mode_remise,
            'recu_par' => $this->recu_par,
            'statut_livraison' => $this->statut_livraison,
            'commentaire_statut' => $this->commentaire_statut,
            'source' => $this->source,
            'note' => $this->note,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
