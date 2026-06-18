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
        'date_mouvement',
        'livreur',
        'destination',
        'source',
        'note',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_mouvement' => 'date',
            'quantite' => 'integer',
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
