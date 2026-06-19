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

    public function test_entree_stores_prix_vendeur_recu_par(): void
    {
        $op = User::factory()->create();
        $article = Article::factory()->withStock(10)->create();

        $this->actingAs($op, 'sanctum')
            ->postJson('/api/v1/mouvements/entree', [
                'article_id' => $article->id, 'quantite' => 4,
                'prix' => 9000, 'vendeur' => 'Fournisseur X', 'recu_par' => 'Abou',
                'numero' => 'CMD-001', 'source' => 'Dépôt central',
            ])
            ->assertCreated()
            ->assertJsonPath('data.vendeur', 'Fournisseur X')
            ->assertJsonPath('data.recu_par', 'Abou')
            ->assertJsonPath('data.numero', 'CMD-001');

        $this->assertSame(14, $article->fresh()->stock_actuel);
        $this->assertDatabaseHas('mouvements', ['type' => 'entree', 'recu_par' => 'Abou', 'prix' => 9000]);
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

    public function test_sortie_sur_place_requires_recu_par(): void
    {
        $op = User::factory()->create();
        $article = Article::factory()->withStock(10)->create();

        // Sur place sans "reçu par" → erreur
        $this->actingAs($op, 'sanctum')
            ->postJson('/api/v1/mouvements/sortie', [
                'article_id' => $article->id, 'quantite' => 2, 'mode_remise' => 'sur_place',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('recu_par');

        // Sur place avec "reçu par" → OK, sans livreur
        $this->actingAs($op, 'sanctum')
            ->postJson('/api/v1/mouvements/sortie', [
                'article_id' => $article->id, 'quantite' => 2,
                'mode_remise' => 'sur_place', 'recu_par' => 'Abou',
            ])
            ->assertCreated()
            ->assertJsonPath('data.recu_par', 'Abou')
            ->assertJsonPath('data.mode_remise', 'sur_place');
    }

    public function test_sortie_stores_sale_fields(): void
    {
        $op = User::factory()->create();
        $article = Article::factory()->withStock(10)->create();

        $this->actingAs($op, 'sanctum')
            ->postJson('/api/v1/mouvements/sortie', [
                'article_id' => $article->id, 'quantite' => 1, 'livreur' => 'Jean',
                'prix' => 15000.50, 'telephone' => '077000000', 'vendeur' => 'Abou',
                'destination' => 'Libreville', 'statut_livraison' => 'valide',
            ])
            ->assertCreated()
            ->assertJsonPath('data.vendeur', 'Abou')
            ->assertJsonPath('data.telephone', '077000000')
            ->assertJsonPath('data.statut_livraison', 'valide');

        $this->assertDatabaseHas('mouvements', ['vendeur' => 'Abou', 'prix' => 15000.50]);
    }

    public function test_failed_delivery_requires_comment(): void
    {
        $op = User::factory()->create();
        $article = Article::factory()->withStock(10)->create();

        $this->actingAs($op, 'sanctum')
            ->postJson('/api/v1/mouvements/sortie', [
                'article_id' => $article->id, 'quantite' => 1, 'livreur' => 'Jean',
                'statut_livraison' => 'rate',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('commentaire_statut');
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
