<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'reference' => ['required', 'string', 'max:255', Rule::unique('articles', 'reference')],
            'designation' => ['required', 'string', 'max:255'],
            'categorie_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'unite' => ['nullable', 'string', 'max:50'],
            'prix' => ['nullable', 'numeric', 'min:0'],
            'suivi_stock' => ['required', 'boolean'],
            'seuil_alerte' => ['nullable', 'integer', 'min:0', 'required_if:suivi_stock,true'],
            'stock_initial' => ['nullable', 'integer', 'min:0'],
            'actif' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'reference.unique' => 'Cette référence existe déjà.',
            'reference.required' => 'La référence est obligatoire.',
            'designation.required' => 'La désignation est obligatoire.',
        ];
    }
}
