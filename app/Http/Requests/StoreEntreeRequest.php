<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEntreeRequest extends FormRequest
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
            'prix' => ['nullable', 'numeric', 'min:0'],
            'numero' => ['nullable', 'string', 'max:100'],
            'date_mouvement' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:255'],
            'vendeur' => ['nullable', 'string', 'max:255'],
            'recu_par' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantite.min' => 'La quantité doit être strictement positive.',
            'article_id.exists' => "L'article sélectionné est introuvable ou inactif.",
        ];
    }
}
