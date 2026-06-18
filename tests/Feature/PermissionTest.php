<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Mouvement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_cannot_create_article(): void
    {
        $op = User::factory()->create();

        $this->actingAs($op, 'sanctum')
            ->postJson('/api/v1/articles', ['reference' => 'X1', 'designation' => 'Test', 'suivi_stock' => false])
            ->assertStatus(403);
    }

    public function test_admin_can_create_article(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/articles', ['reference' => 'X1', 'designation' => 'Test', 'suivi_stock' => false])
            ->assertCreated();
    }

    public function test_operator_cannot_list_users(): void
    {
        $op = User::factory()->create();

        $this->actingAs($op, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertStatus(403);
    }

    public function test_operator_cannot_update_mouvement(): void
    {
        $op = User::factory()->create();
        $article = Article::factory()->withStock(10)->create();
        $m = Mouvement::factory()->create(['article_id' => $article->id, 'user_id' => $op->id]);

        $this->actingAs($op, 'sanctum')
            ->putJson("/api/v1/mouvements/{$m->id}", ['quantite' => 3])
            ->assertStatus(403);
    }

    public function test_admin_can_manage_users(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/users', [
                'name' => 'Nouveau',
                'email' => 'nouveau@a.com',
                'password' => 'motdepasse',
                'password_confirmation' => 'motdepasse',
                'role' => 'operateur',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('users', ['email' => 'nouveau@a.com', 'role' => 'operateur']);
    }
}
