<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMouvementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'in:entree,sortie'],
            'quantite' => ['sometimes', 'integer', 'min:1'],
            'prix' => ['nullable', 'numeric', 'min:0'],
            'date_mouvement' => ['sometimes', 'date'],
            'livreur' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'vendeur' => ['nullable', 'string', 'max:255'],
            'mode_remise' => ['sometimes', 'in:livraison,sur_place'],
            'recu_par' => ['nullable', 'string', 'max:255'],
            'statut_livraison' => ['nullable', 'in:valide,rate,a_reprogrammer'],
            'commentaire_statut' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantite.min' => 'La quantité doit être strictement positive.',
        ];
    }
}
