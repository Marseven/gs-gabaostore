<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MouvementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_create_entree(): void
    {
        $op = User::factory()->create(); // operateur par défaut
        $article = Article::factory()->withStock(10)->create();

        $this->actingAs($op, 'sanctum')
            ->postJson('/api/v1/mouvements/entree', ['article_id' => $article->id, 'quantite' => 7])
            ->assertCreated()
            ->assertJsonPath('data.type', 'entree');

        $this->assertSame(17, $article->fresh()->stock_actuel);
    }

    public function test_sortie_requires_livreur(): void
    {
        $op = User::factory()->create();
        $article = Article::factory()->withStock(10)->create();

        $this->actingAs($op, 'sanctum')
            ->postJson('/api/v1/mouvements/sortie', ['article_id' => $article->id, 'quantite' => 2])
            ->assertStatus(422)
            ->assertJsonValidationErrors('livreur');
    }

    public function test_sortie_over_stock_returns_422(): void
    {
        $op = User::factory()->create();
        $article = Article::factory()->withStock(5)->create();

        $this->actingAs($op, 'sanctum')
            ->postJson('/api/v1/mouvements/sortie', ['article_id' => $article->id, 'quantite' => 99, 'livreur' => 'Marc'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('quantite');

        $this->assertSame(5, $article->fresh()->stock_actuel);
    }

    public function test_quantite_must_be_positive(): void
    {
        $op = User::factory()->create();
        $article = Article::factory()->withStock(10)->create();

        $this->actingAs($op, 'sanctum')
            ->postJson('/api/v1/mouvements/entree', ['article_id' => $article->id, 'quantite' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors('quantite');
    }

    public function test_index_is_filterable_by_type(): void
    {
        $op = User::factory()->create();
        $article = Article::factory()->withStock(50)->create();
        $this->actingAs($op, 'sanctum');
        $this->postJson('/api/v1/mouvements/entree', ['article_id' => $article->id, 'quantite' => 5]);
        $this->postJson('/api/v1/mouvements/sortie', ['article_id' => $article->id, 'quantite' => 2, 'livreur' => 'M']);

        $this->getJson('/api/v1/mouvements?type=sortie')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'sortie');
    }
}
