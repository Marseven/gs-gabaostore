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

    public function rules(): array
    {
        return [
            'article_id' => ['required', 'integer', Rule::exists('articles', 'id')->where('actif', true)],
            'quantite' => ['required', 'integer', 'min:1'],
            'date_mouvement' => ['nullable', 'date'],
            'livreur' => ['required', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantite.min' => 'La quantité doit être strictement positive.',
            'livreur.required' => 'Le nom du livreur est obligatoire pour une sortie.',
            'article_id.exists' => "L'article sélectionné est introuvable ou inactif.",
        ];
    }
}
