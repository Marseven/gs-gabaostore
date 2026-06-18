<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_and_user(): void
    {
        $user = User::factory()->admin()->create(['email' => 'a@a.com']);

        $this->postJson('/api/v1/login', ['email' => 'a@a.com', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role', 'actif']])
            ->assertJsonPath('user.role', 'admin');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'a@a.com']);

        $this->postJson('/api/v1/login', ['email' => 'a@a.com', 'password' => 'mauvais'])
            ->assertStatus(422);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->inactif()->create(['email' => 'off@a.com']);

        $this->postJson('/api/v1/login', ['email' => 'off@a.com', 'password' => 'password'])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'Ce compte est désactivé.');
    }

    public function test_protected_route_requires_authentication(): void
    {
        $this->getJson('/api/v1/dashboard')->assertStatus(401);
    }

    public function test_me_returns_current_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }
}
