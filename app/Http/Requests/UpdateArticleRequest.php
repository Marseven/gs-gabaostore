<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('article')?->id ?? $this->route('article');

        return [
            'reference' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('articles', 'reference')->ignore($id)],
            'designation' => ['sometimes', 'required', 'string', 'max:255'],
            'categorie_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'unite' => ['nullable', 'string', 'max:50'],
            'suivi_stock' => ['sometimes', 'boolean'],
            'seuil_alerte' => ['nullable', 'integer', 'min:0'],
            'stock_initial' => ['sometimes', 'integer', 'min:0'],
            'actif' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'reference.unique' => 'Cette référence existe déjà.',
        ];
    }
}
