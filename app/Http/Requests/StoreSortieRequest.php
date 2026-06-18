<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSortieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        // Mode de remise par défaut : livraison.
        $this->merge([
            'mode_remise' => $this->input('mode_remise', 'livraison'),
        ]);
    }

    public function rules(): array
    {
        return [
            'article_id' => ['required', 'integer', Rule::exists('articles', 'id')->where('actif', true)],
            'quantite' => ['required', 'integer', 'min:1'],
            'prix' => ['nullable', 'numeric', 'min:0'],
            'numero' => ['nullable', 'string', 'max:100'],
            'date_mouvement' => ['nullable', 'date'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'vendeur' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'mode_remise' => ['required', 'in:livraison,sur_place'],
            // Livraison → livreur obligatoire ; Sur place → reçu par obligatoire.
            'livreur' => ['nullable', 'string', 'max:255', 'required_if:mode_remise,livraison'],
            'recu_par' => ['nullable', 'string', 'max:255', 'required_if:mode_remise,sur_place'],
            'statut_livraison' => ['nullable', 'in:valide,rate,a_reprogrammer'],
            // Commentaire obligatoire si la livraison est ratée.
            'commentaire_statut' => ['nullable', 'string', 'required_if:statut_livraison,rate'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantite.min' => 'La quantité doit être strictement positive.',
            'livreur.required_if' => 'Le nom du livreur est obligatoire pour une livraison.',
            'recu_par.required_if' => 'Le nom de la personne ayant reçu la marchandise est obligatoire (sur place).',
            'commentaire_statut.required_if' => 'Indiquez la raison d\'une livraison ratée.',
            'article_id.exists' => "L'article sélectionné est introuvable ou inactif.",
        ];
    }
}
