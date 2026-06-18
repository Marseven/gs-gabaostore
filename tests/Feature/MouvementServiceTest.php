<?php

namespace Tests\Feature;

use App\Exceptions\StockInsuffisantException;
use App\Models\Article;
use App\Models\User;
use App\Services\MouvementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MouvementServiceTest extends TestCase
{
    use RefreshDatabase;

    private MouvementService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MouvementService::class);
        $this->user = User::factory()->create();
    }

    public function test_entree_increments_stock(): void
    {
        $article = Article::factory()->withStock(10)->create();

        $this->service->creer(['article_id' => $article->id, 'type' => 'entree', 'quantite' => 5], $this->user->id);

        $this->assertSame(15, $article->fresh()->stock_actuel);
    }

    public function test_sortie_decrements_stock(): void
    {
        $article = Article::factory()->withStock(10)->create();

        $this->service->creer(['article_id' => $article->id, 'type' => 'sortie', 'quantite' => 4, 'livreur' => 'Marc'], $this->user->id);

        $this->assertSame(6, $article->fresh()->stock_actuel);
    }

    public function test_sortie_over_stock_throws_and_does_not_change_stock(): void
    {
        $article = Article::factory()->withStock(3)->create();

        try {
            $this->service->creer(['article_id' => $article->id, 'type' => 'sortie', 'quantite' => 10, 'livreur' => 'Marc'], $this->user->id);
            $this->fail('StockInsuffisantException attendue');
        } catch (StockInsuffisantException $e) {
            $this->assertSame(3, $e->disponible);
            $this->assertSame(10, $e->demande);
        }

        // Stock inchangé et aucun mouvement créé (transaction annulée).
        $this->assertSame(3, $article->fresh()->stock_actuel);
        $this->assertDatabaseCount('mouvements', 0);
    }

    public function test_non_suivi_article_records_movement_without_stock_change(): void
    {
        $article = Article::factory()->nonSuivi()->create();

        $this->service->creer(['article_id' => $article->id, 'type' => 'sortie', 'quantite' => 999, 'livreur' => 'Marc'], $this->user->id);

        $this->assertSame(0, $article->fresh()->stock_actuel);
        $this->assertDatabaseCount('mouvements', 1);
    }

    public function test_recompute_rebuilds_stock_from_movements(): void
    {
        $article = Article::factory()->withStock(8, 10)->create();
        $this->service->creer(['article_id' => $article->id, 'type' => 'entree', 'quantite' => 5], $this->user->id);
        $this->service->creer(['article_id' => $article->id, 'type' => 'sortie', 'quantite' => 3, 'livreur' => 'M'], $this->user->id);

        // Corruption volontaire du cache
        $article->update(['stock_actuel' => 999]);

        $this->service->recalculer($article);

        $this->assertSame(10, $article->fresh()->stock_actuel); // 8 + 5 - 3
    }

    public function test_admin_delete_recomputes_stock(): void
    {
        $article = Article::factory()->withStock(10)->create();
        $m = $this->service->creer(['article_id' => $article->id, 'type' => 'entree', 'quantite' => 5], $this->user->id);
        $this->assertSame(15, $article->fresh()->stock_actuel);

        $this->service->supprimer($m);

        $this->assertSame(10, $article->fresh()->stock_actuel);
    }
}
