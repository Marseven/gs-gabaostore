<?php

namespace App\Exports;

use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExport implements FromQuery, WithHeadings, WithMapping
{
    public function query(): Builder
    {
        return Article::query()
            ->with('categorie')
            ->where('actif', true)
            ->suivis()
            ->orderBy('designation');
    }

    public function headings(): array
    {
        return [
            'Référence', 'Désignation', 'Catégorie', 'Unité', 'Prix',
            'Stock actuel', 'Seuil alerte', 'En alerte',
        ];
    }

    /**
     * @param  Article  $article
     */
    public function map($article): array
    {
        return [
            $article->reference,
            $article->designation,
            $article->categorie?->nom,
            $article->unite,
            $article->prix,
            $article->stock_actuel,
            $article->seuil_alerte,
            $article->en_alerte ? 'OUI' : 'non',
        ];
    }
}
