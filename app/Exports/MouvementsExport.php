<?php

namespace App\Exports;

use App\Models\Mouvement;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MouvementsExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @param  array<string,mixed>  $filters
     */
    public function __construct(protected array $filters = [])
    {
    }

    public function query(): Builder
    {
        $query = Mouvement::query()->with(['article', 'user'])->latest('date_mouvement')->latest('id');

        if (! empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }
        if (! empty($this->filters['article_id'])) {
            $query->where('article_id', $this->filters['article_id']);
        }
        if (! empty($this->filters['livreur'])) {
            $query->where('livreur', 'like', '%'.$this->filters['livreur'].'%');
        }
        if (! empty($this->filters['date_from'])) {
            $query->whereDate('date_mouvement', '>=', $this->filters['date_from']);
        }
        if (! empty($this->filters['date_to'])) {
            $query->whereDate('date_mouvement', '<=', $this->filters['date_to']);
        }
        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('destination', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhere('livreur', 'like', "%{$search}%")
                    ->orWhereHas('article', function (Builder $a) use ($search) {
                        $a->where('reference', 'like', "%{$search}%")
                            ->orWhere('designation', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Date', 'Type', 'Référence', 'Désignation', 'Quantité',
            'Unité', 'Livreur', 'Destination', 'Source', 'Note', 'Auteur',
        ];
    }

    /**
     * @param  Mouvement  $mouvement
     */
    public function map($mouvement): array
    {
        return [
            $mouvement->date_mouvement?->format('d/m/Y'),
            $mouvement->type === 'entree' ? 'Entrée' : 'Sortie',
            $mouvement->article?->reference,
            $mouvement->article?->designation,
            $mouvement->quantite,
            $mouvement->article?->unite,
            $mouvement->livreur,
            $mouvement->destination,
            $mouvement->source,
            $mouvement->note,
            $mouvement->user?->name,
        ];
    }
}
