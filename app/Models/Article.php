<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'designation',
        'categorie_id',
        'unite',
        'suivi_stock',
        'seuil_alerte',
        'stock_initial',
        'stock_actuel',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'suivi_stock' => 'boolean',
            'actif' => 'boolean',
            'seuil_alerte' => 'integer',
            'stock_initial' => 'integer',
            'stock_actuel' => 'integer',
        ];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categorie_id');
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(Mouvement::class);
    }

    /**
     * Un article suivi est en alerte si un seuil est défini et que le stock l'atteint.
     */
    public function getEnAlerteAttribute(): bool
    {
        return $this->suivi_stock
            && ! is_null($this->seuil_alerte)
            && $this->stock_actuel <= $this->seuil_alerte;
    }

    public function scopeSuivis(Builder $query): Builder
    {
        return $query->where('suivi_stock', true);
    }

    public function scopeEnAlerte(Builder $query): Builder
    {
        return $query->where('suivi_stock', true)
            ->whereNotNull('seuil_alerte')
            ->whereColumn('stock_actuel', '<=', 'seuil_alerte');
    }
}
