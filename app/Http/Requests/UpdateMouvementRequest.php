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
            'date_mouvement' => ['sometimes', 'date'],
            'livreur' => ['nullable', 'string', 'max:255', 'required_if:type,sortie'],
            'destination' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantite.min' => 'La quantité doit être strictement positive.',
            'livreur.required_if' => 'Le nom du livreur est obligatoire pour une sortie.',
        ];
    }
}
