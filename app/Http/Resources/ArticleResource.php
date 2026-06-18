<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'designation' => $this->designation,
            'categorie_id' => $this->categorie_id,
            'categorie' => new CategoryResource($this->whenLoaded('categorie')),
            'unite' => $this->unite,
            'suivi_stock' => $this->suivi_stock,
            'seuil_alerte' => $this->seuil_alerte,
            'stock_initial' => $this->stock_initial,
            'stock_actuel' => $this->stock_actuel,
            'en_alerte' => $this->en_alerte,
            'actif' => $this->actif,
        ];
    }
}
