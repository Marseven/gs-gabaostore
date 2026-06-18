<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mouvement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'article_id',
        'type',
        'quantite',
        'prix',
        'numero',
        'date_mouvement',
        'livreur',
        'destination',
        'telephone',
        'vendeur',
        'mode_remise',
        'recu_par',
        'statut_livraison',
        'commentaire_statut',
        'source',
        'note',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_mouvement' => 'date',
            'quantite' => 'integer',
            'prix' => 'decimal:2',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
