<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockRecomputeCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_reconciles_corrupted_cache(): void
    {
        $article = Article::factory()->withStock(20)->create();
        $article->mouvements()->create([
            'type' => 'sortie', 'quantite' => 5, 'date_mouvement' => now()->toDateString(),
            'livreur' => 'M', 'user_id' => \App\Models\User::factory()->create()->id,
        ]);

        // Cache corrompu
        $article->update(['stock_actuel' => 0]);

        $this->artisan('stock:recompute')->assertSuccessful();

        $this->assertSame(15, $article->fresh()->stock_actuel); // 20 - 5
    }
}
